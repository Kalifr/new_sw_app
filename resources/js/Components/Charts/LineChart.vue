<template>
    <div class="relative h-64">
        <canvas ref="canvas"></canvas>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    data: {
        type: Array,
        required: true,
    },
    format: {
        type: Function,
        default: value => value,
    },
});

const canvas = ref(null);
let chart = null;

onMounted(() => {
    createChart();
});

watch(() => props.data, () => {
    if (chart) {
        chart.destroy();
    }
    createChart();
}, { deep: true });

function createChart() {
    const ctx = canvas.value.getContext('2d');
    
    chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: props.data.map(item => formatDate(item.date)),
            datasets: [{
                label: 'Value',
                data: props.data.map(item => item.value),
                borderColor: '#339966',
                backgroundColor: 'rgba(51, 153, 102, 0.1)',
                fill: true,
                tension: 0.4,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    callbacks: {
                        label: (context) => {
                            return props.format(context.raw);
                        },
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => props.format(value),
                    },
                },
                x: {
                    grid: {
                        display: false,
                    },
                },
            },
        },
    });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
    }).format(date);
}
</script> 