<template>
    <Link
        :href="route('waiter.tables.show', { restaurant: restaurantSlug, table: table.id })"
        class="text-decoration-none"
    >
        <v-card
            v-if="isOpen"
            class="mb-3"
            hover
        >
            <v-card-text class="pa-4">
                <div class="d-flex align-start">
                    <!-- Number badge -->
                    <div
                        class="d-flex align-center justify-center rounded-lg mr-3 flex-shrink-0"
                        style="width: 48px; height: 48px; background-color: rgb(var(--v-theme-primary-100));"
                    >
                        <span class="text-h6 font-weight-bold" style="color: rgb(var(--v-theme-primary-700));">
                            {{ table.number }}
                        </span>
                    </div>

                    <!-- Info -->
                    <div class="flex-grow-1 min-width-0">
                        <div class="d-flex align-center mb-1">
                            <v-icon
                                icon="mdi-circle-small"
                                :color="'success'"
                                size="small"
                                class="mr-1"
                            />
                            <span class="text-caption font-weight-medium text-success">Aberta</span>
                            <v-icon icon="mdi-silverware-fork-knife" size="x-small" class="ml-2 text-medium-emphasis" />
                        </div>
                        <div class="text-body-2 text-medium-emphasis">
                            {{ table.person_count }} pessoas &middot; {{ table.item_count }} itens &middot; {{ elapsed(table.opened_at) }}
                        </div>
                        <v-chip
                            v-if="toSendCount > 0"
                            color="warning"
                            size="x-small"
                            class="mt-1 font-weight-medium"
                            label
                        >
                            {{ toSendCount }} para enviar
                        </v-chip>
                    </div>

                    <!-- Total -->
                    <div class="text-right flex-shrink-0 ml-3">
                        <div class="text-h6 font-weight-bold" style="color: rgb(var(--v-theme-primary));">
                            R$ {{ table.total }}
                        </div>
                    </div>
                </div>
            </v-card-text>
        </v-card>

        <v-card
            v-else
            class="mb-3"
            variant="outlined"
            hover
        >
            <v-card-text class="pa-4">
                <div class="d-flex align-center">
                    <!-- Number badge -->
                    <div
                        class="d-flex align-center justify-center rounded-lg mr-3 flex-shrink-0 bg-grey-lighten-3"
                        style="width: 48px; height: 48px;"
                    >
                        <span class="text-h6 font-weight-bold text-medium-emphasis">
                            {{ table.number }}
                        </span>
                    </div>

                    <!-- Info -->
                    <div class="flex-grow-1 min-width-0">
                        <div class="d-flex align-center mb-1">
                            <v-icon
                                icon="mdi-circle-small"
                                color="secondary"
                                size="small"
                                class="mr-1"
                            />
                            <span class="text-caption font-weight-medium text-medium-emphasis">
                                {{ isClosed ? 'Fechada' : 'Livre' }}
                            </span>
                        </div>
                        <div class="text-body-2 text-medium-emphasis">
                            {{ isClosed ? closedAtLabel(table.closed_at) : 'Toque para abrir uma comanda' }}
                        </div>
                    </div>

                    <!-- Chevron -->
                    <v-icon icon="mdi-chevron-right" class="text-medium-emphasis flex-shrink-0 ml-2" />
                </div>
            </v-card-text>
        </v-card>
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    table: {
        type: Object,
        required: true,
    },
    restaurantSlug: {
        type: String,
        required: true,
    },
    scope: {
        type: String,
        default: 'mine',
    },
});

const isOpen = computed(() => !props.table.closed_at);
const isClosed = computed(() => !!props.table.closed_at);

const toSendCount = computed(() => {
    if (!props.table.items) {
        return 0;
    }

    return props.table.items.filter((item) => item.to_send === true || item.status === 'new').length;
});

function elapsed(iso) {
    if (!iso) {
        return '';
    }

    const diff = Math.floor((Date.now() - new Date(iso).getTime()) / 60000);

    if (diff < 1) {
        return 'aberta agora';
    }

    if (diff < 60) {
        return `${diff}min`;
    }

    const hours = Math.floor(diff / 60);
    const mins = diff % 60;

    return `${hours}h ${mins}min`;
}

function closedAtLabel(iso) {
    if (!iso) {
        return '';
    }

    const date = new Date(iso);

    return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' });
}
</script>
