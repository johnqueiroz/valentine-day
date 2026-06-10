<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    form: { type: Object, required: true },
    slideTypes: { type: Object, required: true },
    themes: { type: Object, required: true },
});

// Campos de "meta" sugeridos por tipo de slide.
const metaFields = {
    stat: [
        { key: 'value', label: 'Número (ex.: 365)' },
        { key: 'unit', label: 'Unidade (ex.: dias)' },
    ],
    music: [
        { key: 'artist', label: 'Artista' },
        { key: 'plays', label: 'Vezes ouvida' },
    ],
    place: [
        { key: 'location', label: 'Cidade / lugar' },
        { key: 'count', label: 'Quantas visitas' },
    ],
    milestone: [{ key: 'date', label: 'Data (ex.: 14/02/2024)' }],
    message: [],
};

function addTrack() {
    props.form.tracks.push({ id: null, title: '', artist: '', youtube_url: '' });
}

function removeTrack(index) {
    props.form.tracks.splice(index, 1);
}

function addSlide() {
    props.form.slides.push({ type: 'stat', title: '', body: '', meta: {} });
}

function removeSlide(index) {
    props.form.slides.splice(index, 1);
}

function moveSlide(index, dir) {
    const target = index + dir;
    if (target < 0 || target >= props.form.slides.length) return;
    const slides = props.form.slides;
    [slides[index], slides[target]] = [slides[target], slides[index]];
}
</script>

<template>
    <div class="space-y-8">
        <!-- Dados do casal -->
        <section class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <InputLabel for="name1" value="Nome (pessoa 1)" />
                <TextInput id="name1" v-model="form.couple_name_1" required class="mt-1 block w-full" />
                <InputError :message="form.errors.couple_name_1" class="mt-1" />
            </div>
            <div>
                <InputLabel for="name2" value="Nome (pessoa 2)" />
                <TextInput id="name2" v-model="form.couple_name_2" required class="mt-1 block w-full" />
                <InputError :message="form.errors.couple_name_2" class="mt-1" />
            </div>
            <div>
                <InputLabel for="started" value="Início do relacionamento" />
                <TextInput
                    id="started"
                    v-model="form.relationship_started_on"
                    type="date"
                    required
                    class="mt-1 block w-full"
                />
                <InputError :message="form.errors.relationship_started_on" class="mt-1" />
            </div>
            <div>
                <InputLabel for="theme" value="Cor de acento (tema Spotify)" />
                <select
                    id="theme"
                    v-model="form.theme"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option v-for="(label, key) in themes" :key="key" :value="key">
                        {{ label }}
                    </option>
                </select>
            </div>
            <div>
                <InputLabel for="gifter" value="Quem está presenteando" />
                <TextInput id="gifter" v-model="form.gifter_name" required class="mt-1 block w-full" placeholder="Ex.: Leonardo" />
                <InputError :message="form.errors.gifter_name" class="mt-1" />
            </div>
        </section>

        <!-- Faixas (playlist do player) -->
        <section>
            <div class="mb-1 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Faixas (músicas)</h3>
                <SecondaryButton type="button" @click="addTrack">+ Adicionar faixa</SecondaryButton>
            </div>
            <p class="mb-3 text-sm text-gray-500">
                Cada faixa tem música, título e artista. A foto de cada faixa é enviada na edição,
                depois de salvar.
            </p>

            <p v-if="!form.tracks.length" class="rounded-md bg-gray-50 p-4 text-sm text-gray-500">
                Nenhuma faixa ainda. Adicione ao menos uma música.
            </p>

            <div
                v-for="(track, index) in form.tracks"
                :key="index"
                class="mb-4 rounded-lg border border-gray-200 p-4"
            >
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-500">Faixa {{ index + 1 }}</span>
                    <DangerButton type="button" @click="removeTrack(index)">Remover</DangerButton>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <InputLabel value="Link da música no YouTube" />
                        <TextInput v-model="track.youtube_url" required class="mt-1 block w-full" placeholder="https://youtu.be/..." />
                        <InputError :message="form.errors[`tracks.${index}.youtube_url`]" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Nome da música" />
                        <TextInput v-model="track.title" required class="mt-1 block w-full" placeholder="Ex.: Still Loving You" />
                        <InputError :message="form.errors[`tracks.${index}.title`]" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Artista" />
                        <TextInput v-model="track.artist" required class="mt-1 block w-full" placeholder="Ex.: Panda" />
                        <InputError :message="form.errors[`tracks.${index}.artist`]" class="mt-1" />
                    </div>
                </div>
            </div>
        </section>

        <!-- Sobre o casal -->
        <section>
            <InputLabel for="letter" value="Sobre o casal (cartinha)" />
            <textarea
                id="letter"
                v-model="form.love_letter"
                rows="4"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="Uma mensagem que aparece abaixo do player..."
            ></textarea>
            <InputError :message="form.errors.love_letter" class="mt-1" />
        </section>

        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input v-model="form.published" type="checkbox" class="rounded border-gray-300 text-indigo-600" />
            Publicar (torna o link acessível)
        </label>

        <!-- Slides -->
        <section>
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Slides do Wrapped</h3>
                <SecondaryButton type="button" @click="addSlide">+ Adicionar slide</SecondaryButton>
            </div>

            <p v-if="!form.slides.length" class="rounded-md bg-gray-50 p-4 text-sm text-gray-500">
                Nenhum slide ainda. Adicione momentos, estatísticas e uma mensagem final.
            </p>

            <div
                v-for="(slide, index) in form.slides"
                :key="index"
                class="mb-4 rounded-lg border border-gray-200 p-4"
            >
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-500">Slide {{ index + 1 }}</span>
                    <div class="flex items-center gap-2">
                        <button type="button" class="text-gray-400 hover:text-gray-700" @click="moveSlide(index, -1)">↑</button>
                        <button type="button" class="text-gray-400 hover:text-gray-700" @click="moveSlide(index, 1)">↓</button>
                        <DangerButton type="button" @click="removeSlide(index)">Remover</DangerButton>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <InputLabel value="Tipo" />
                        <select
                            v-model="slide.type"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option v-for="(label, key) in slideTypes" :key="key" :value="key">
                                {{ label }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Título" />
                        <TextInput v-model="slide.title" class="mt-1 block w-full" />
                        <InputError :message="form.errors[`slides.${index}.title`]" class="mt-1" />
                    </div>
                </div>

                <div class="mt-4">
                    <InputLabel value="Texto / descrição" />
                    <textarea
                        v-model="slide.body"
                        rows="2"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    ></textarea>
                </div>

                <div
                    v-if="metaFields[slide.type]?.length"
                    class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2"
                >
                    <div v-for="field in metaFields[slide.type]" :key="field.key">
                        <InputLabel :value="field.label" />
                        <TextInput
                            :model-value="slide.meta?.[field.key] ?? ''"
                            class="mt-1 block w-full"
                            @update:model-value="(v) => { slide.meta = { ...slide.meta, [field.key]: v }; }"
                        />
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
