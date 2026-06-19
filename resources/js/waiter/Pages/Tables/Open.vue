<template>
    <WaiterLayout>
        <v-container>
            <div class="d-flex align-center mb-6">
                <v-btn
                    variant="tonal"
                    color="on-surface"
                    icon="mdi-arrow-left"
                    class="mr-2"
                    @click="$inertia.visit(route('waiter.tables.index', { restaurant: restaurant.slug }))"
                ></v-btn>
                <h2 class="font-weight-bold">Abrir Mesa</h2>
            </div>

            <v-form @submit.prevent="submit">
                <v-text-field
                    v-model="form.number"
                    label="Número da mesa"
                    type="number"
                    min="1"
                    variant="outlined"
                    :error-messages="form.errors.number"
                    required
                />

                <v-text-field
                    v-model="form.person_count"
                    label="Pessoas"
                    type="number"
                    min="1"
                    variant="outlined"
                    :error-messages="form.errors.person_count"
                    required
                />

                <v-text-field
                    v-model="form.name"
                    label="Nome (opcional)"
                    variant="outlined"
                    :error-messages="form.errors.name"
                />

                <v-textarea
                    v-model="form.description"
                    label="Descrição (opcional)"
                    variant="outlined"
                    rows="3"
                    :error-messages="form.errors.description"
                />

                <v-btn
                    type="submit"
                    color="primary"
                    block
                    size="large"
                    :loading="form.processing"
                >
                    Abrir mesa
                </v-btn>
            </v-form>
        </v-container>
    </WaiterLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import WaiterLayout from '../../Layouts/WaiterLayout.vue';

const props = defineProps({
    restaurant: Object,
});

const form = useForm({
    number: '',
    person_count: 1,
    name: '',
    description: '',
});

function submit() {
    form.post(route('waiter.tables.store', { restaurant: props.restaurant.slug }));
}
</script>
