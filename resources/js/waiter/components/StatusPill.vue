<template>
    <v-chip
        :color="color"
        :text-color="textColor"
        size="small"
        class="font-weight-medium"
        label
    >
        {{ label }}
    </v-chip>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
});

const config = computed(() => {
    switch (props.status) {
    case 'new':
        return { label: 'NEW', color: 'primary', textColor: 'white' };
    case 'preparing':
        return { label: 'PREPARING', color: 'warning', textColor: 'white' };
    case 'ready':
        return { label: 'READY', color: 'success', textColor: 'white' };
    case 'served':
    default:
        return { label: 'SERVED', color: 'secondary', textColor: 'white' };
    }
});

const label = computed(() => config.value.label);
const color = computed(() => config.value.color);
const textColor = computed(() => config.value.textColor);
</script>
