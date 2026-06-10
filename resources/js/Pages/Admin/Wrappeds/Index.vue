<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    wrappeds: { type: Array, default: () => [] },
});

const flash = computed(() => usePage().props.flash?.success);

function destroy(wrapped) {
    if (confirm(`Remover o Wrapped de ${wrapped.couple_name_1} & ${wrapped.couple_name_2}?`)) {
        router.delete(route('admin.wrappeds.destroy', wrapped.id));
    }
}
</script>

<template>
    <Head title="Wrappeds" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Wrappeds dos casais
                </h2>
                <Link :href="route('admin.wrappeds.create')">
                    <PrimaryButton>+ Novo Wrapped</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    v-if="flash"
                    class="mb-4 rounded-md bg-green-50 px-4 py-3 text-sm text-green-700"
                >
                    {{ flash }}
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <table v-if="wrappeds.length" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                <th class="px-6 py-3">Casal</th>
                                <th class="px-6 py-3">Slides</th>
                                <th class="px-6 py-3">Fotos</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="w in wrappeds" :key="w.id">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ w.couple_name_1 }} &amp; {{ w.couple_name_2 }}
                                    <div class="text-xs text-gray-400">/w/{{ w.slug }}</div>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ w.slides_count }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ w.photos_count }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="w.published_at
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-gray-100 text-gray-600'"
                                        class="rounded-full px-2 py-1 text-xs font-medium"
                                    >
                                        {{ w.published_at ? 'Publicado' : 'Rascunho' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <a
                                        v-if="w.published_at"
                                        :href="route('wrapped.show', w.slug)"
                                        target="_blank"
                                        class="mr-3 text-pink-600 hover:underline"
                                    >Ver</a>
                                    <Link
                                        :href="route('admin.wrappeds.edit', w.id)"
                                        class="mr-3 text-indigo-600 hover:underline"
                                    >Editar</Link>
                                    <button
                                        class="text-red-600 hover:underline"
                                        @click="destroy(w)"
                                    >Excluir</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-else class="p-10 text-center text-gray-500">
                        Nenhum Wrapped ainda. Clique em
                        <span class="font-medium">“+ Novo Wrapped”</span> para começar.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
