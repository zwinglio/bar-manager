<template>
    <WaiterLayout>
        <v-container class="px-4 pt-4">
            <!-- Header -->
            <div class="d-flex align-center justify-space-between mb-6">
                <div>
                    <h1 class="text-h6 font-weight-bold text-on-surface">
                        Boa noite, {{ waiter.name }}
                    </h1>
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
                        v-if="scope === tab.value && tables.length > 0"
                        color="white"
                        size="small"
                        class="ml-2 font-weight-bold"
                        label
                    >
                        {{ tables.length }}
                    </v-chip>
                </v-btn>
            </div>

            <v-empty-state
                v-if="tables.length === 0"
                :headline="scope === 'closed' ? 'Nenhuma mesa fechada.' : 'Nenhuma mesa aberta.'"
            />

            <div v-else>
                <TableCard
                    v-for="table in tables"
                    :key="table.id"
                    :table="table"
                    :restaurant-slug="restaurant.slug"
                    :scope="scope"
                />
            </div>

            <!-- Sticky bottom CTA -->
            <div
                v-if="scope !== 'closed'"
                class="sticky-bottom pa-4"
                style="position: sticky; bottom: 0; z-index: 10;"
            >
                <Link
                    :href="route('waiter.tables.create', { restaurant: restaurant.slug })"
                    class="text-decoration-none"
                >
                    <v-btn
                        color="primary"
                        block
                        size="large"
                        prepend-icon="mdi-plus"
                    >
                        Abrir mesa
                    </v-btn>
                </Link>
            </div>
        </v-container>
    </WaiterLayout>
</template>

<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import WaiterLayout from '../../Layouts/WaiterLayout.vue';
import TableCard from '../../components/TableCard.vue';

const props = defineProps({
    restaurant: Object,
    waiter: Object,
    tables: Array,
    scope: String,
});

const scope = ref(props.scope);

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
