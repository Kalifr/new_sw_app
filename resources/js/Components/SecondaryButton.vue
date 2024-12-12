<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-futura text-sm text-gray-700 tracking-brand hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-selina disabled:opacity-25 transition ease-in-out duration-150"
    >
        <slot />
    </button>
</template>
