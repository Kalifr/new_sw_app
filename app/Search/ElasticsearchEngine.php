<?php

namespace App\Search;

use Elastic\Elasticsearch\Client;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;

class ElasticsearchEngine extends Engine
{
    protected $elasticsearch;
    protected $index;

    public function __construct(Client $elasticsearch, string $index)
    {
        $this->elasticsearch = $elasticsearch;
        $this->index = $index;
    }

    public function update($models)
    {
        if ($models->isEmpty()) {
            return;
        }

        $params = ['body' => []];

        $models->each(function ($model) use (&$params) {
            $params['body'][] = [
                'index' => [
                    '_index' => $this->index,
                    '_id' => $model->getKey(),
                ],
            ];

            $params['body'][] = array_merge(
                $model->toSearchableArray(),
                $model->scoutMetadata()
            );
        });

        $this->elasticsearch->bulk($params);
    }

    public function delete($models)
    {
        if ($models->isEmpty()) {
            return;
        }

        $params = ['body' => []];

        $models->each(function ($model) use (&$params) {
            $params['body'][] = [
                'delete' => [
                    '_index' => $this->index,
                    '_id' => $model->getKey(),
                ],
            ];
        });

        $this->elasticsearch->bulk($params);
    }

    public function search(Builder $builder)
    {
        return $this->performSearch($builder, array_filter([
            'numericFilters' => $this->filters($builder),
            'size' => $builder->limit,
        ]));
    }

    public function paginate(Builder $builder, $perPage, $page)
    {
        return $this->performSearch($builder, array_filter([
            'numericFilters' => $this->filters($builder),
            'size' => $perPage,
            'from' => ($page - 1) * $perPage,
        ]));
    }

    protected function performSearch(Builder $builder, array $options = [])
    {
        $query = $builder->query;

        if (empty($query)) {
            $params = [
                'index' => $this->index,
                'body' => [
                    'query' => ['match_all' => new \stdClass],
                ],
            ];
        } else {
            $params = [
                'index' => $this->index,
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                'multi_match' => [
                                    'query' => $query,
                                    'fields' => ['name^3', 'description', 'search_vector'],
                                    'fuzziness' => 'AUTO',
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        }

        if ($options['numericFilters']) {
            $params['body']['query']['bool']['filter'] = $options['numericFilters'];
        }

        if (isset($options['size'])) {
            $params['body']['size'] = $options['size'];
        }

        if (isset($options['from'])) {
            $params['body']['from'] = $options['from'];
        }

        $response = $this->elasticsearch->search($params);

        return [
            'hits' => collect($response['hits']['hits'])->map(function ($hit) {
                return [
                    'id' => $hit['_id'],
                    '_score' => $hit['_score'],
                    ...$hit['_source'],
                ];
            })->all(),
            'total' => $response['hits']['total']['value'],
        ];
    }

    public function mapIds($results)
    {
        return collect($results['hits'])->pluck('id')->values()->all();
    }

    public function map(Builder $builder, $results, $model)
    {
        if (count($results['hits']) === 0) {
            return $model->newCollection();
        }

        $objectIds = collect($results['hits'])->pluck('id')->values()->all();
        $objectIdPositions = array_flip($objectIds);

        return $model->getScoutModelsByIds($builder, $objectIds)
            ->filter(function ($model) use ($objectIds) {
                return in_array($model->getScoutKey(), $objectIds);
            })
            ->sortBy(function ($model) use ($objectIdPositions) {
                return $objectIdPositions[$model->getScoutKey()];
            })
            ->values();
    }

    public function getTotalCount($results)
    {
        return $results['total'];
    }

    protected function filters(Builder $builder)
    {
        return collect($builder->wheres)->map(function ($value, $key) {
            if (is_array($value)) {
                return ['terms' => [$key => $value]];
            }

            return ['term' => [$key => $value]];
        })->values()->all();
    }

    public function flush($model)
    {
        $params = [
            'index' => $this->index,
            'body' => [
                'query' => [
                    'match_all' => new \stdClass,
                ],
            ],
        ];

        $this->elasticsearch->deleteByQuery($params);
    }
} 