<template>
    <WaiterLayout>
        <v-container>
            <div class="d-flex align-center mb-4">
                <Link
                    :href="route('waiter.tables.index', { restaurant: restaurant.slug })"
                    class="text-body-2 text-medium-emphasis mr-4 text-decoration-none"
                >
                    &larr; Voltar
                </Link>
                <h1 class="text-h6 font-weight-bold">
                    Mesa {{ table.number }}
                    <span v-if="table.name" class="text-subtitle-1 font-weight-regular text-medium-emphasis"> — {{ table.name }}</span>
                </h1>
            </div>

            <div class="text-body-2 text-medium-emphasis mb-4">
                {{ table.person_count }} pessoas &middot; {{ elapsed(table.opened_at) }}
                <v-chip
                    v-if="table.is_closed"
                    size="x-small"
                    class="ml-2"
                >
                    Fechada
                </v-chip>
            </div>

            <!-- Detail mode -->
            <div v-if="mode === 'detail'">
                <v-empty-state
                    v-if="table.items.length === 0"
                    headline="Nenhum item ainda."
                />

                <v-list v-else lines="two" class="pa-0">
                    <v-list-item
                        v-for="item in table.items"
                        :key="item.id"
                        class="px-0"
                    >
                        <template #title>
                            <span class="text-body-1 font-weight-medium">{{ item.name }}</span>
                        </template>

                        <template #subtitle>
                            <span class="text-caption text-medium-emphasis">R$ {{ item.unit_price }} x {{ item.quantity }}</span>
                        </template>

                        <template #append>
                            <div class="d-flex align-center">
                                <template v-if="!table.is_closed">
                                    <v-btn
                                        icon="mdi-minus"
                                        size="x-small"
                                        variant="tonal"
                                        @click="updateQuantity(item, item.quantity - 1)"
                                    />
                                    <span class="text-body-2 font-weight-medium mx-2" style="min-width: 24px; text-align: center;">
                                        {{ item.quantity }}
                                    </span>
                                    <v-btn
                                        icon="mdi-plus"
                                        size="x-small"
                                        variant="tonal"
                                        @click="updateQuantity(item, item.quantity + 1)"
                                    />
                                </template>
                                <span v-else class="text-body-2 font-weight-medium">
                                    R$ {{ item.subtotal }}
                                </span>
                            </div>
                        </template>
                    </v-list-item>
                </v-list>

                <div v-if="table.items.length > 0" class="mt-4">
                    <v-divider class="mb-2" />
                    <div class="d-flex justify-space-between text-h6 font-weight-bold">
                        <span>Total</span>
                        <span>R$ {{ subtotal }}</span>
                    </div>
                </div>

                <div v-if="!table.is_closed" class="mt-6 d-flex flex-column gap-3">
                    <Link
                        :href="route('waiter.tables.show', { restaurant: restaurant.slug, table: table.id, mode: 'menu' })"
                        class="text-decoration-none"
                    >
                        <v-btn color="primary" block>
                            + Adicionar itens
                        </v-btn>
                    </Link>

                    <v-btn
                        color="error"
                        block
                        :loading="closeForm.processing"
                        @click="closeTable"
                    >
                        Fechar mesa
                    </v-btn>
                </div>
            </div>

            <!-- Menu mode -->
            <div v-else>
                <v-text-field
                    v-model="search"
                    label="Buscar produto..."
                    variant="outlined"
                    density="compact"
                    prepend-inner-icon="mdi-magnify"
                    class="mb-3"
                />

                <v-select
                    v-model="selectedCategory"
                    :items="categoryItems"
                    item-title="title"
                    item-value="value"
                    label="Categoria"
                    variant="outlined"
                    density="compact"
                    class="mb-4"
                />

                <v-empty-state
                    v-if="filteredProducts.length === 0"
                    headline="Nenhum produto encontrado."
                />

                <v-list v-else lines="one" class="pa-0">
                    <v-list-item
                        v-for="product in filteredProducts"
                        :key="product.id"
                        class="px-0"
                    >
                        <template #title>
                            <span class="text-body-1 font-weight-medium">{{ product.name }}</span>
                        </template>

                        <template #subtitle>
                            <span class="text-caption text-medium-emphasis">R$ {{ product.price }}</span>
                        </template>

                        <template #append>
                            <v-btn
                                color="primary"
                                size="small"
                                :loading="addForms[product.id]?.processing"
                                @click="addProduct(product.id)"
                            >
                                Adicionar
                            </v-btn>
                        </template>
                    </v-list-item>
                </v-list>

                <Link
                    :href="route('waiter.tables.show', { restaurant: restaurant.slug, table: table.id })"
                    class="text-decoration-none"
                >
                    <v-btn variant="outlined" block class="mt-6">
                        Concluído
                    </v-btn>
                </Link>
            </div>
        </v-container>
    </WaiterLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import WaiterLayout from '../../Layouts/WaiterLayout.vue';

const props = defineProps({
    restaurant: Object,
    table: Object,
    mode: String,
    products: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
});

const closeForm = useForm({});
const addForms = reactive({});
const search = ref('');
const selectedCategory = ref(null);

const categoryItems = computed(() => [
    { title: 'Todas as categorias', value: null },
    ...props.categories.map((c) => ({ title: c.name, value: c.id })),
]);

const filteredProducts = computed(() => {
    return props.products.filter((product) => {
        const matchesSearch = product.name.toLowerCase().includes(search.value.toLowerCase());
        const matchesCategory = selectedCategory.value === null || product.category_id === selectedCategory.value;
        return matchesSearch && matchesCategory;
    });
});

const subtotal = computed(() => props.table.items.reduce((sum, item) => sum + parseFloat(item.subtotal), 0).toFixed(2));

function elapsed(iso) {
    if (!iso) return '';
    const diff = Math.floor((Date.now() - new Date(iso).getTime()) / 60000);
    if (diff < 1) return 'aberta agora';
    if (diff < 60) return `${diff}min`;
    const hours = Math.floor(diff / 60);
    const mins = diff % 60;
    return `${hours}h ${mins}min`;
}

function updateQuantity(item, quantity) {
    if (quantity <= 0) {
        const form = useForm({});
        form.delete(route('waiter.tables.products.destroy', {
            restaurant: props.restaurant.slug,
            table: props.table.id,
            product: item.id,
        }), { preserveScroll: true });
    } else {
        const form = useForm({ quantity });
        form.patch(route('waiter.tables.products.update', {
            restaurant: props.restaurant.slug,
            table: props.table.id,
            product: item.id,
        }), { preserveScroll: true });
    }
}

function closeTable() {
    closeForm.post(route('waiter.tables.close', {
        restaurant: props.restaurant.slug,
        table: props.table.id,
    }));
}

function addProduct(productId) {
    if (!addForms[productId]) {
        addForms[productId] = useForm({ product_id: productId });
    }
    addForms[productId].post(route('waiter.tables.products.store', {
        restaurant: props.restaurant.slug,
        table: props.table.id,
    }), { preserveScroll: true });
}
</script>
