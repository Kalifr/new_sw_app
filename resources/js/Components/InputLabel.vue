<script setup>
defineProps({
    value: {
        type: String,
    },
});
</script>

<template>
    <label class="block font-cocomat text-sm text-wamucii">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
