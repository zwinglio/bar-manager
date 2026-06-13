<template>
    <WaiterLayout>
        <v-container class="d-flex flex-column align-center justify-center" style="min-height: 100vh;">
            <div class="text-center mb-8">
                <h1 class="text-h5 font-weight-bold">{{ restaurant.name }}</h1>
                <p class="text-body-2 text-medium-emphasis">Login do Garçom</p>
            </div>

            <v-card class="w-100" max-width="400" flat>
                <v-form @submit.prevent="submit">
                    <v-text-field
                        v-model="form.username"
                        label="Usuário"
                        variant="outlined"
                        :error-messages="form.errors.username"
                        required
                        autofocus
                    />

                    <v-btn
                        type="submit"
                        color="primary"
                        block
                        :loading="form.processing"
                    >
                        Entrar
                    </v-btn>
                </v-form>
            </v-card>
        </v-container>
    </WaiterLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import WaiterLayout from '../Layouts/WaiterLayout.vue';

const props = defineProps({
    restaurant: Object,
});

const form = useForm({
    username: '',
});

function submit() {
    form.post(route('waiter.login.attempt', { restaurant: props.restaurant.slug }));
}
</script>
