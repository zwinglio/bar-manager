<template>
    <WaiterLayout>
        <v-container>
            <div class="d-flex align-center justify-space-between mb-4">
                <h1 class="text-h6 font-weight-bold">
                    Boa noite, {{ waiter.name }}
                </h1>
                <v-btn
                    variant="text"
                    size="small"
                    :loading="logoutForm.processing"
                    @click="logout"
                >
                    Sair
                </v-btn>
            </div>

            <v-btn-toggle
                v-model="scope"
                mandatory
                divided
                class="mb-4 w-100"
                @update:model-value="changeScope"
            >
                <v-btn value="mine" class="flex-grow-1">Minhas</v-btn>
                <v-btn value="all" class="flex-grow-1">Todas</v-btn>
                <v-btn value="closed" class="flex-grow-1">Fechadas</v-btn>
            </v-btn-toggle>

            <v-empty-state
                v-if="tables.length === 0"
                :headline="scope === 'closed' ? 'Nenhuma mesa fechada.' : 'Nenhuma mesa aberta.'"
            />

            <v-list v-else lines="two" class="pa-0">
                <Link
                    v-for="table in tables"
                    :key="table.id"
                    :href="route('waiter.tables.show', { restaurant: restaurant.slug, table: table.id })"
                    class="text-decoration-none"
                >
                    <v-list-item link class="px-0">
                        <template #title>
                            <span class="font-weight-bold">
                                Mesa {{ table.number }}
                                <span v-if="table.name" class="font-weight-regular text-medium-emphasis"> — {{ table.name }}</span>
                            </span>
                        </template>

                        <template #subtitle>
                            <div>
                                {{ table.person_count }} pessoas &middot; {{ table.item_count }} itens
                                <span v-if="scope === 'closed' && table.closed_at">
                                    &middot; {{ closedAtLabel(table.closed_at) }}
                                </span>
                                <span v-else>
                                    &middot; {{ elapsed(table.opened_at) }}
                                </span>
                            </div>
                            <div v-if="(scope === 'all' || scope === 'closed') && table.waiter_name" class="text-caption text-disabled">
                                {{ table.waiter_name }}
                            </div>
                        </template>

                        <template #append>
                            <div class="text-right">
                                <v-chip
                                    size="small"
                                    :color="scope === 'closed' ? 'default' : 'success'"
                                    class="mb-1"
                                >
                                    {{ scope === 'closed' ? 'Fechada' : 'Aberta' }}
                                </v-chip>
                                <div class="text-body-2 font-weight-bold">
                                    R$ {{ table.total }}
                                </div>
                            </div>
                        </template>
                    </v-list-item>
                </Link>
            </v-list>

            <Link
                v-if="scope !== 'closed'"
                :href="route('waiter.tables.create', { restaurant: restaurant.slug })"
                class="text-decoration-none"
            >
                <v-btn color="primary" block class="mt-6">
                    + Abrir mesa
                </v-btn>
            </Link>
        </v-container>
    </WaiterLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import WaiterLayout from '../../Layouts/WaiterLayout.vue';

const props = defineProps({
    restaurant: Object,
    waiter: Object,
    tables: Array,
    scope: String,
});

const scope = ref(props.scope);

const logoutForm = useForm({});

function logout() {
    logoutForm.post(route('waiter.logout', { restaurant: props.restaurant.slug }));
}

function changeScope(newScope) {
    scope.value = newScope;
    const url = route('waiter.tables.index', { restaurant: props.restaurant.slug, scope: newScope });
    window.location.href = url;
}

function elapsed(iso) {
    if (!iso) return '';
    const diff = Math.floor((Date.now() - new Date(iso).getTime()) / 60000);
    if (diff < 1) return 'aberta agora';
    if (diff < 60) return `${diff}min`;
    const hours = Math.floor(diff / 60);
    const mins = diff % 60;
    return `${hours}h ${mins}min`;
}

function closedAtLabel(iso) {
    if (!iso) return '';
    const date = new Date(iso);
    return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' });
}
</script>
