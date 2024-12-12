<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @if(isset($page['props']['meta']))
            <title>{{ $page['props']['meta']['title'] ?? config('app.name') }}</title>
            <meta name="description" content="{{ $page['props']['meta']['description'] ?? '' }}">
            
            @if(isset($page['props']['meta']['canonical']))
                <link rel="canonical" href="{{ $page['props']['meta']['canonical'] }}" />
            @endif

            @if(isset($page['props']['product']))
                <!-- Open Graph tags -->
                @foreach($page['props']['product']['openGraph'] as $property => $content)
                    <meta property="og:{{ $property }}" content="{{ $content }}" />
                @endforeach

                <!-- Twitter Card tags -->
                @foreach($page['props']['product']['twitterCard'] as $name => $content)
                    <meta name="twitter:{{ $name }}" content="{{ $content }}" />
                @endforeach

                <!-- JSON-LD structured data -->
                <script type="application/ld+json">
                    {!! json_encode($page['props']['product']['jsonLd']) !!}
                </script>
            @endif

            @if(isset($page['props']['meta']['jsonLd']))
                <script type="application/ld+json">
                    {!! json_encode($page['props']['meta']['jsonLd']) !!}
                </script>
            @endif
        @else
            <title>{{ config('app.name') }}</title>
        @endif

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="font-futura antialiased bg-white">
        @inertia
    </body>
</html>
