<template>
    <WaiterLayout>
        <v-container class="px-4 pt-4">
            <!-- Header -->
            <div class="d-flex align-center justify-space-between mb-6">
                <div>
                    <h3 class="text-subtitle-1 font-weight-bold text-on-surface">
                        Boa noite, {{ waiter.name }}
                    </h3>
                </div>
                <div class="d-flex align-center gap-2">
                    <v-avatar
                        color="primary"
                        size="36"
                        class="text-white text-caption font-weight-bold"
                    >
                        {{ initials(waiter.name) }}
                    </v-avatar>
                    <v-btn
                        icon="mdi-bell-outline"
                        variant="text"
                        density="comfortable"
                        color="on-surface"
                        class="ml-2"
                    />
                    <v-btn
                        variant="text"
                        size="small"
                        density="comfortable"
                        color="on-surface"
                        :loading="logoutForm.processing"
                        class="ml-1"
                        @click="logout"
                    >
                        Sair
                    </v-btn>
                </div>
            </div>

            <!-- Pill tabs -->
            <div class="d-flex ga-3 mb-4">
                <v-btn
                    v-for="tab in tabs"
                    :key="tab.value"
                    :variant="scope === tab.value ? 'flat' : 'tonal'"
                    :color="scope === tab.value ? 'primary' : 'on-surface'"
                    rounded="pill"
                    size="large"
                    class="text-none flex-grow-1 px-4"
                    @click="changeScope(tab.value)"
                >
                    <span class="font-weight-medium">{{ tab.label }}</span>
                    <v-chip
                        v-if="scope === tab.value && filteredTables.length > 0"
                        color="white"
                        size="small"
                        class="ml-2 font-weight-bold"
                        label
                    >
                        {{ filteredTables.length }}
                    </v-chip>
                </v-btn>
            </div>

            <!-- Search -->
            <v-text-field
                v-model="search"
                label="Buscar por nome ou número..."
                variant="outlined"
                density="compact"
                prepend-inner-icon="mdi-magnify"
                hide-details
                class="mb-4"
            />

            <v-empty-state
                v-if="filteredTables.length === 0"
                :headline="search ? 'Nenhuma mesa encontrada.' : (scope === 'closed' ? 'Nenhuma mesa fechada.' : 'Nenhuma mesa aberta.')"
            />

            <div v-else>
                <TableCard
                    v-for="table in filteredTables"
                    :key="table.id"
                    :table="table"
                    :restaurant-slug="restaurant.slug"
                    :scope="scope"
                />
            </div>

            <!-- Sticky bottom CTA -->
            <div
                v-if="scope !== 'closed'"
                class="sticky-bottom py-4"
                style="position: sticky; bottom: 0; z-index: 10;"
            >
                <v-btn
                    color="primary"
                    block
                    size="large"
                    prepend-icon="mdi-plus"
                    @click="$inertia.visit(route('waiter.tables.create', { restaurant: restaurant.slug }))"
                >
                    Abrir mesa
                </v-btn>
            </div>
        </v-container>
    </WaiterLayout>
</template>

<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import WaiterLayout from '../../Layouts/WaiterLayout.vue';
import TableCard from '../../components/TableCard.vue';

const props = defineProps({
    restaurant: Object,
    waiter: Object,
    tables: Array,
    scope: String,
});

const scope = ref(props.scope);
const search = ref('');

const filteredTables = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (!term) {
        return props.tables;
    }

    return props.tables.filter((table) => {
        return String(table.number).includes(term)
            || (table.name ?? '').toLowerCase().includes(term);
    });
});

const tabs = [
    { value: 'mine', label: 'Abertas' },
    { value: 'all', label: 'Todas' },
    { value: 'closed', label: 'Fechadas' },
];

const logoutForm = useForm({});

function logout() {
    logoutForm.post(route('waiter.logout', { restaurant: props.restaurant.slug }));
}

function changeScope(newScope) {
    scope.value = newScope;
    router.get(route('waiter.tables.index', { restaurant: props.restaurant.slug, scope: newScope }));
}

function initials(name) {
    if (!name) {
        return '';
    }

    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
}
</script>
