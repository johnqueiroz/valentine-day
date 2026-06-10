<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import WrappedFormFields from '@/Components/Admin/WrappedFormFields.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    wrapped: { type: Object, required: true },
    slideTypes: { type: Object, required: true },
    themes: { type: Object, required: true },
    publicUrl: { type: String, required: true },
});

const flash = computed(() => usePage().props.flash?.success);

const form = useForm({
    couple_name_1: props.wrapped.couple_name_1,
    couple_name_2: props.wrapped.couple_name_2,
    gifter_name: props.wrapped.gifter_name ?? '',
    love_letter: props.wrapped.love_letter ?? '',
    relationship_started_on: (props.wrapped.relationship_started_on ?? '').slice(0, 10),
    theme: props.wrapped.theme,
    published: !!props.wrapped.published_at,
    slides: props.wrapped.slides.map((s) => ({
        type: s.type,
        title: s.title,
        body: s.body ?? '',
        meta: s.meta ?? {},
    })),
    tracks: props.wrapped.tracks.map((t) => ({
        id: t.id,
        title: t.title,
        artist: t.artist ?? '',
        youtube_url: t.youtube_url ?? '',
    })),
});

function submit() {
    form.put(route('admin.wrappeds.update', props.wrapped.id), { preserveScroll: true });
}

// Upload de fotos (formulário multipart separado).
const photoForm = useForm({ photos: [] });
const fileInput = ref(null);

function uploadPhotos(event) {
    photoForm.photos = Array.from(event.target.files);
    if (!photoForm.photos.length) return;
    photoForm.post(route('admin.wrappeds.photos.store', props.wrapped.id), {
        preserveScroll: true,
        onSuccess: () => {
            photoForm.reset();
            if (fileInput.value) fileInput.value.value = '';
        },
    });
}

function uploadTrackPhoto(track, event) {
    const file = event.target.files[0];
    if (!file) return;
    router.post(route('admin.wrappeds.tracks.photo', [props.wrapped.id, track.id]), { photo: file }, {
        preserveScroll: true,
        forceFormData: true,
    });
}

function deletePhoto(photo) {
    if (confirm('Remover esta foto?')) {
        router.delete(route('admin.wrappeds.photos.destroy', [props.wrapped.id, photo.id]), {
            preserveScroll: true,
        });
    }
}

function copyLink() {
    navigator.clipboard.writeText(props.publicUrl);
}
</script>

<template>
    <Head title="Editar Wrapped" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Editar Wrapped — {{ wrapped.couple_name_1 }} &amp; {{ wrapped.couple_name_2 }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
                <div
                    v-if="flash"
                    class="rounded-md bg-green-50 px-4 py-3 text-sm text-green-700"
                >
                    {{ flash }}
                </div>

                <!-- Link público -->
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-white p-4 shadow-sm">
                    <div class="text-sm">
                        <span class="text-gray-500">Link público:</span>
                        <a :href="publicUrl" target="_blank" class="ml-1 text-pink-600 hover:underline">{{ publicUrl }}</a>
                        <span v-if="!wrapped.published_at" class="ml-2 text-xs text-amber-600">(publique para liberar)</span>
                    </div>
                    <SecondaryButton type="button" @click="copyLink">Copiar link</SecondaryButton>
                </div>

                <!-- Formulário principal -->
                <form class="space-y-6 rounded-lg bg-white p-6 shadow-sm" @submit.prevent="submit">
                    <WrappedFormFields :form="form" :slide-types="slideTypes" :themes="themes" />
                    <div class="flex items-center justify-end gap-3 border-t pt-4">
                        <Link :href="route('admin.wrappeds.index')" class="text-sm text-gray-600 hover:underline">Voltar</Link>
                        <PrimaryButton :disabled="form.processing">Salvar alterações</PrimaryButton>
                    </div>
                </form>

                <!-- Foto de cada faixa -->
                <div v-if="wrapped.tracks.length" class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="mb-1 text-lg font-medium text-gray-900">Foto de cada faixa</h3>
                    <p class="mb-4 text-sm text-gray-500">Aparece como capa no player ao tocar a faixa. (Salve as faixas antes.)</p>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div
                            v-for="track in wrapped.tracks"
                            :key="track.id"
                            class="flex items-center gap-4 rounded-lg border border-gray-200 p-3"
                        >
                            <img
                                v-if="track.photo_url"
                                :src="track.photo_url"
                                class="h-16 w-16 shrink-0 rounded object-cover"
                                :alt="track.title"
                            />
                            <div v-else class="flex h-16 w-16 shrink-0 items-center justify-center rounded bg-gray-100 text-2xl">🎵</div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-medium text-gray-800">{{ track.title }}</p>
                                <p class="truncate text-sm text-gray-500">{{ track.artist }}</p>
                                <input
                                    type="file"
                                    accept="image/*"
                                    class="mt-1 block w-full text-xs text-gray-600 file:mr-2 file:rounded file:border-0 file:bg-indigo-50 file:px-2 file:py-1 file:text-xs file:font-medium file:text-indigo-700 hover:file:bg-indigo-100"
                                    @change="(e) => uploadTrackPhoto(track, e)"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fotos da galeria (retrospectiva) -->
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="mb-1 text-lg font-medium text-gray-900">Galeria (retrospectiva)</h3>
                    <p class="mb-4 text-sm text-gray-500">Fotos exibidas nos stories da retrospectiva.</p>

                    <div class="mb-4 grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                        <div
                            v-for="photo in wrapped.photos"
                            :key="photo.id"
                            class="group relative overflow-hidden rounded-lg"
                        >
                            <img :src="photo.url" class="h-32 w-full object-cover" :alt="photo.caption ?? ''" />
                            <button
                                type="button"
                                class="absolute right-1 top-1 rounded-full bg-black/60 px-2 py-0.5 text-xs text-white opacity-0 transition group-hover:opacity-100"
                                @click="deletePhoto(photo)"
                            >✕</button>
                        </div>
                    </div>

                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/*"
                        multiple
                        class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100"
                        @change="uploadPhotos"
                    />
                    <p v-if="photoForm.progress" class="mt-2 text-sm text-gray-500">
                        Enviando… {{ photoForm.progress.percentage }}%
                    </p>
                    <InputError v-if="photoForm.errors['photos.0']" :message="photoForm.errors['photos.0']" class="mt-2" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
