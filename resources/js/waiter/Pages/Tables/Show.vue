<template>
    <WaiterLayout>
        <v-container class="px-4 pt-4 pb-8">
            <!-- Back link -->
            <div class="d-flex align-center mb-4">
                <Link
                    :href="route('waiter.tables.index', { restaurant: restaurant.slug })"
                    class="text-body-2 text-medium-emphasis mr-4 text-decoration-none d-flex align-center"
                >
                    <v-icon icon="mdi-arrow-left" size="small" class="mr-1" />
                    Voltar
                </Link>
            </div>

            <!-- Detail mode -->
            <div v-if="mode === 'detail'">
                <!-- Red header card -->
                <v-card color="primary" class="mb-4" :elevation="0">
                    <v-card-text class="pa-4 d-flex align-center justify-space-between text-white">
                        <div>
                            <div class="text-h6 font-weight-bold">
                                Mesa {{ table.number }}
                                <span v-if="table.name" class="text-subtitle-2 font-weight-regular opacity-80"> — {{ table.name }}</span>
                            </div>
                            <div class="text-caption opacity-90 mt-1">
                                Pedido #{{ table.id }}
                            </div>
                        </div>
                        <div class="text-right">
                            <v-chip color="white" variant="flat" size="small" class="font-weight-bold text-primary">
                                {{ table.person_count }} pessoas
                            </v-chip>
                        </div>
                    </v-card-text>
                </v-card>

                <!-- Items card -->
                <v-card class="mb-4">
                    <v-card-text class="pa-0">
                        <v-empty-state
                            v-if="table.items.length === 0"
                            headline="Nenhum item ainda."
                        />

                        <template v-else>
                            <div
                                v-for="(item, index) in table.items"
                                :key="item.id"
                                class="d-flex align-center pa-4"
                                :class="{ 'border-b': index < table.items.length - 1 }"
                            >
                                <!-- Quantity badge -->
                                <div
                                    class="d-flex align-center justify-center rounded-lg mr-3 flex-shrink-0"
                                    style="width: 36px; height: 36px; background-color: rgb(var(--v-theme-primary-100));"
                                >
                                    <span class="text-body-2 font-weight-bold" style="color: rgb(var(--v-theme-primary-700));">
                                        {{ item.quantity }}
                                    </span>
                                </div>

                                <!-- Name + price -->
                                <div class="flex-grow-1 min-width-0">
                                    <div class="text-body-1 font-weight-medium truncate">
                                        {{ item.name }}
                                    </div>
                                    <div class="text-caption text-medium-emphasis">
                                        R$ {{ item.unit_price }} unidade
                                    </div>
                                </div>

                                <!-- Quantity controls or subtotal -->
                                <div class="d-flex align-center flex-shrink-0 ml-3">
                                    <template v-if="!table.is_closed">
                                        <v-btn
                                            icon="mdi-minus"
                                            size="small"
                                            variant="tonal"
                                            density="comfortable"
                                            @click="updateQuantity(item, item.quantity - 1)"
                                        />
                                        <span class="text-body-2 font-weight-medium mx-2" style="min-width: 24px; text-align: center;">
                                            {{ item.quantity }}
                                        </span>
                                        <v-btn
                                            icon="mdi-plus"
                                            size="small"
                                            variant="tonal"
                                            density="comfortable"
                                            @click="updateQuantity(item, item.quantity + 1)"
                                        />
                                    </template>
                                    <span v-else class="text-body-2 font-weight-medium">
                                        R$ {{ item.subtotal }}
                                    </span>
                                </div>
                            </div>

                            <!-- Total row -->
                            <v-divider />
                            <div class="d-flex justify-space-between align-center pa-4">
                                <span class="text-body-1 text-medium-emphasis">Total</span>
                                <span class="text-h6 font-weight-bold" style="color: rgb(var(--v-theme-primary));">
                                    R$ {{ subtotal }}
                                </span>
                            </div>
                        </template>
                    </v-card-text>
                </v-card>

                <!-- Action row -->
                <div v-if="!table.is_closed" class="d-flex flex-column gap-3 mb-6">
                    <Link
                        :href="route('waiter.tables.show', { restaurant: restaurant.slug, table: table.id, mode: 'menu' })"
                        class="text-decoration-none"
                    >
                        <v-btn
                            variant="outlined"
                            color="primary"
                            block
                            size="large"
                            prepend-icon="mdi-plus"
                        >
                            Adicionar item
                        </v-btn>
                    </Link>

                    <v-btn
                        color="primary"
                        block
                        size="large"
                        prepend-icon="mdi-send"
                        :loading="closeForm.processing"
                        @click="closeTable"
                    >
                        Fechar mesa
                    </v-btn>
                </div>

            </div>

            <!-- Menu mode -->
            <div v-else>
                <v-card class="mb-4 pa-4">
                    <v-text-field
                        v-model="search"
                        label="Buscar produto..."
                        variant="outlined"
                        density="compact"
                        prepend-inner-icon="mdi-magnify"
                        hide-details
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
                        hide-details
                    />
                </v-card>

                <v-empty-state
                    v-if="filteredProducts.length === 0"
                    headline="Nenhum produto encontrado."
                />

                <v-card
                    v-for="product in filteredProducts"
                    :key="product.id"
                    class="mb-3"
                >
                    <v-card-text class="pa-3 d-flex align-center">
                        <div class="flex-grow-1 min-width-0 mr-3">
                            <div class="text-body-1 font-weight-medium truncate">
                                {{ product.name }}
                            </div>
                            <div class="text-caption text-medium-emphasis">
                                R$ {{ product.price }}
                            </div>
                        </div>
                        <v-btn
                            color="primary"
                            size="small"
                            :loading="addForms[product.id]?.processing"
                            @click="addProduct(product.id)"
                        >
                            Adicionar
                        </v-btn>
                    </v-card-text>
                </v-card>

                <Link
                    :href="route('waiter.tables.show', { restaurant: restaurant.slug, table: table.id })"
                    class="text-decoration-none"
                >
                    <v-btn variant="outlined" block class="mt-4" size="large">
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

<style scoped>
.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.opacity-80 {
    opacity: 0.8;
}

.opacity-90 {
    opacity: 0.9;
}

.gap-3 {
    gap: 12px;
}
</style>
