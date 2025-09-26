<template>
    <Link :href="props.url" class="transition-all" 
    :class="outline ? 
    'text-indigo-600 bg-white px-4 py-2 rounded hover:text-indigo-800 border border-indigo-100 hover:border-indigo-600' : 
    'bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700'">
        <i class="bx me-1" :class="icon" v-if="icon"></i>
        {{ props.text }}
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
const props = defineProps({
    icon: { type: String, default: null},
    text: { type: String, default: null },
    url: { type: String, default: null },
    outline: { type: Boolean, default: false}
})
</script>