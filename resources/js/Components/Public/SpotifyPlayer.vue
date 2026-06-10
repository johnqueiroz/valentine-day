<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import SpotifyNav from '@/Components/Public/SpotifyNav.vue';
import { coverGradient, themeOf } from '@/themes';

const props = defineProps({
    wrapped: { type: Object, required: true },
    isPlaying: { type: Boolean, default: false },
    currentTime: { type: Number, default: 0 },
    duration: { type: Number, default: 0 },
    hasAudio: { type: Boolean, default: false },
});

const emit = defineEmits(['toggle', 'seek', 'openStories', 'back']);

const accent = computed(() => themeOf(props.wrapped.theme).accent);

// Galeria navegável: fotos enviadas (com cor dominante) ou a capa como item único.
const gallery = computed(() => {
    if (props.wrapped.photos?.length) return props.wrapped.photos;
    if (props.wrapped.cover_url) return [{ url: props.wrapped.cover_url, color: props.wrapped.cover_color }];
    return [];
});

// Começa na foto definida como capa, se houver.
const coverIndex = gallery.value.findIndex((p) => p.url === props.wrapped.cover_url);
const photoIndex = ref(coverIndex >= 0 ? coverIndex : 0);

const currentPhoto = computed(() => gallery.value[photoIndex.value] ?? null);
const coverSrc = computed(() => currentPhoto.value?.url ?? props.wrapped.cover_url);
const gradient = computed(() => coverGradient(currentPhoto.value?.color ?? props.wrapped.cover_color, props.wrapped.theme));
const hasGallery = computed(() => gallery.value.length > 1);

function nextPhoto() {
    if (!hasGallery.value) return;
    photoIndex.value = (photoIndex.value + 1) % gallery.value.length;
}
function prevPhoto() {
    if (!hasGallery.value) return;
    photoIndex.value = (photoIndex.value - 1 + gallery.value.length) % gallery.value.length;
}

const title = computed(() => props.wrapped.song_title || `${props.wrapped.couple_name_1} & ${props.wrapped.couple_name_2}`);
const artist = computed(() => props.wrapped.song_artist || `${props.wrapped.couple_name_1} & ${props.wrapped.couple_name_2}`);

const progress = computed(() => (props.duration ? (props.currentTime / props.duration) * 100 : 0));

function fmt(seconds) {
    if (!seconds || seconds < 0) return '0:00';
    const m = Math.floor(seconds / 60);
    const s = Math.floor(seconds % 60).toString().padStart(2, '0');
    return `${m}:${s}`;
}

const remaining = computed(() => (props.duration ? `-${fmt(props.duration - props.currentTime)}` : '0:00'));

function onSeek(e) {
    const rect = e.currentTarget.getBoundingClientRect();
    const fraction = Math.min(1, Math.max(0, (e.clientX - rect.left) / rect.width));
    emit('seek', fraction);
}

// Contador de tempo juntos ao vivo.
const now = ref(new Date());
let timer = null;
onMounted(() => { timer = setInterval(() => { now.value = new Date(); }, 1000); });
onBeforeUnmount(() => timer && clearInterval(timer));

const liveCounter = computed(() => {
    if (!props.wrapped.relationship_started_on) return null;
    const start = new Date(props.wrapped.relationship_started_on + 'T00:00:00');
    let diff = Math.max(0, now.value - start);
    const day = 86400000;
    const years = Math.floor(diff / (365.25 * day));
    diff -= years * 365.25 * day;
    const months = Math.floor(diff / (30.44 * day));
    diff -= months * 30.44 * day;
    const days = Math.floor(diff / day);
    return { years, months, days };
});
</script>

<template>
    <div class="flex h-full flex-col text-white transition-[background] duration-500" :style="{ background: gradient }">
        <div class="flex-1 overflow-y-auto">
            <!-- Header -->
            <div class="flex items-center justify-between px-5 pt-6">
                <button class="text-white/90" aria-label="Voltar" @click="emit('back')">
                    <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 9l6 6 6-6" />
                    </svg>
                </button>
                <span class="text-sm font-semibold">Juntos para sempre ❤️</span>
                <button class="text-2xl text-white/80">⋯</button>
            </div>

            <div class="px-6">
                <!-- Capa -->
                <div class="mx-auto mt-6 aspect-square w-full max-w-sm overflow-hidden rounded-lg shadow-2xl">
                    <img
                        v-if="coverSrc"
                        :src="coverSrc"
                        class="h-full w-full object-cover transition-opacity duration-300"
                        alt="capa"
                    />
                    <div v-else class="flex h-full w-full items-center justify-center bg-white/5 text-6xl">💞</div>
                </div>

                <!-- Título / artista -->
                <div class="mt-6 flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h1 class="truncate text-2xl font-bold">{{ title }}</h1>
                        <p class="truncate text-white/60">{{ artist }}</p>
                    </div>
                    <div
                        class="mt-1 flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-sm font-bold text-white"
                        :style="{ backgroundColor: accent }"
                    >✓</div>
                </div>

                <!-- Barra de progresso -->
                <div class="mt-5">
                    <div class="group relative h-1 cursor-pointer rounded-full bg-white/25" @click="onSeek">
                        <div class="h-full rounded-full bg-white" :style="{ width: progress + '%' }"></div>
                        <div
                            class="absolute top-1/2 h-3 w-3 -translate-x-1/2 -translate-y-1/2 rounded-full bg-white shadow"
                            :style="{ left: progress + '%' }"
                        ></div>
                    </div>
                    <div class="mt-1 flex justify-between text-xs text-white/60">
                        <span>{{ fmt(currentTime) }}</span>
                        <span>{{ remaining }}</span>
                    </div>
                </div>

                <!-- Controles -->
                <div class="mt-4 flex items-center justify-between">
                    <!-- Shuffle -->
                    <button class="text-white/70">
                        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3h5v5"/><path d="M4 20 21 3"/><path d="M21 16v5h-5"/><path d="M15 15l6 6"/><path d="M4 4l5 5"/></svg>
                    </button>
                    <!-- Anterior -->
                    <button class="text-white/90 disabled:opacity-30" :disabled="!hasGallery" aria-label="Foto anterior" @click="prevPhoto">
                        <svg viewBox="0 0 24 24" class="h-9 w-9 fill-current"><path d="M7 6v12H5V6zm2 6 10 6V6z"/></svg>
                    </button>
                    <!-- Play / Pause -->
                    <button
                        class="flex h-16 w-16 items-center justify-center rounded-full bg-white shadow-lg transition active:scale-95 disabled:opacity-40"
                        :disabled="!hasAudio"
                        @click="emit('toggle')"
                    >
                        <svg v-if="isPlaying" viewBox="0 0 24 24" class="h-7 w-7" :style="{ fill: accent }"><path d="M7 5h4v14H7zM13 5h4v14h-4z"/></svg>
                        <svg v-else viewBox="0 0 24 24" class="ml-0.5 h-7 w-7" :style="{ fill: accent }"><path d="M8 5v14l11-7z"/></svg>
                    </button>
                    <!-- Próxima -->
                    <button class="text-white/90 disabled:opacity-30" :disabled="!hasGallery" aria-label="Próxima foto" @click="nextPhoto">
                        <svg viewBox="0 0 24 24" class="h-9 w-9 fill-current"><path d="M17 6v12h2V6zM15 12 5 6v12z"/></svg>
                    </button>
                    <!-- Repeat -->
                    <button class="text-white/70">
                        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 2l4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><path d="M7 22l-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                    </button>
                </div>

                <p v-if="!hasAudio" class="mt-3 text-center text-xs text-white/40">
                    (Nenhuma música configurada)
                </p>

                <!-- Contador de tempo juntos -->
                <div v-if="liveCounter" class="mt-8 rounded-2xl bg-white/[0.04] p-5 text-center">
                    <p class="text-xs uppercase tracking-[0.2em] text-white/50">Juntos há</p>
                    <p class="mt-2 text-3xl font-extrabold" :style="{ color: accent }">
                        {{ liveCounter.years }}a {{ liveCounter.months }}m {{ liveCounter.days }}d
                    </p>
                    <p class="mt-1 text-sm text-white/50">e contando, em tempo real ⏱️</p>
                </div>

                <!-- Sobre o casal -->
                <div v-if="wrapped.love_letter" class="mt-6 rounded-2xl bg-white/[0.04] p-5">
                    <h2 class="mb-2 text-lg font-bold">Sobre o casal</h2>
                    <p class="whitespace-pre-line leading-relaxed text-white/80">{{ wrapped.love_letter }}</p>
                </div>

                <!-- CTA retrospectiva -->
                <button
                    class="mt-8 w-full rounded-full py-4 text-lg font-bold text-black transition hover:scale-[1.02]"
                    :style="{ backgroundColor: accent }"
                    @click="emit('openStories')"
                >
                    ▶ Ver retrospectiva
                </button>

                <div class="h-10"></div>
            </div>
        </div>

        <SpotifyNav />
    </div>
</template>
