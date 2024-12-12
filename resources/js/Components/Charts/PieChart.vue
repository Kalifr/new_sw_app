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
        type: Object,
        required: true,
    },
});

const canvas = ref(null);
let chart = null;

const colors = [
    '#339966',
    '#0E5641',
    '#F4D03F',
    '#45B7AF',
    '#2E86C1',
    '#8E44AD',
    '#E74C3C',
    '#F39C12',
    '#16A085',
    '#2C3E50',
];

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
    const labels = Object.keys(props.data);
    const values = Object.values(props.data);
    
    chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: colors.slice(0, labels.length),
                borderColor: '#ffffff',
                borderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        font: {
                            family: 'Futura LT',
                            size: 12,
                        },
                        padding: 20,
                    },
                },
                tooltip: {
                    callbacks: {
                        label: (context) => {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.raw / total) * 100).toFixed(1);
                            return `${context.label}: ${context.raw} (${percentage}%)`;
                        },
                    },
                },
            },
            cutout: '60%',
            radius: '90%',
        },
    });
}
</script> 