<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import WrappedFormFields from '@/Components/Admin/WrappedFormFields.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    slideTypes: { type: Object, required: true },
    themes: { type: Object, required: true },
});

const form = useForm({
    couple_name_1: '',
    couple_name_2: '',
    gifter_name: '',
    song_title: '',
    song_artist: '',
    youtube_url: '',
    love_letter: '',
    relationship_started_on: '',
    theme: Object.keys(props.themes)[0],
    published: false,
    slides: [],
});

function submit() {
    form.post(route('admin.wrappeds.store'));
}
</script>

<template>
    <Head title="Novo Wrapped" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Novo Wrapped</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <form
                    class="space-y-6 rounded-lg bg-white p-6 shadow-sm"
                    @submit.prevent="submit"
                >
                    <WrappedFormFields :form="form" :slide-types="slideTypes" :themes="themes" />

                    <div class="flex items-center justify-end gap-3 border-t pt-4">
                        <Link :href="route('admin.wrappeds.index')" class="text-sm text-gray-600 hover:underline">
                            Cancelar
                        </Link>
                        <PrimaryButton :disabled="form.processing">Criar e adicionar fotos</PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
