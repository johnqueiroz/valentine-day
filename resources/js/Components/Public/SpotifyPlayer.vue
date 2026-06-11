<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import SpotifyNav from '@/Components/Public/SpotifyNav.vue';
import { coverGradient, themeOf } from '@/themes';

const props = defineProps({
    wrapped: { type: Object, required: true },
    tracks: { type: Array, default: () => [] },
    trackIndex: { type: Number, default: 0 },
    isPlaying: { type: Boolean, default: false },
    currentTime: { type: Number, default: 0 },
    duration: { type: Number, default: 0 },
    hasAudio: { type: Boolean, default: false },
});

const emit = defineEmits(['toggle', 'seek', 'openStories', 'back', 'changeTrack', 'openHighlight']);

const accent = computed(() => themeOf(props.wrapped.theme).accent);

// Faixa atual: dirige capa, título/artista e a cor de fundo.
const coupleName = computed(() => `${props.wrapped.couple_name_1} & ${props.wrapped.couple_name_2}`);
const currentTrack = computed(() => props.tracks[props.trackIndex] ?? null);
const coverSrc = computed(() => currentTrack.value?.photo_url ?? null);
const gradient = computed(() => coverGradient(currentTrack.value?.photo_color, props.wrapped.theme));
const hasMultiple = computed(() => props.tracks.length > 1);

const title = computed(() => currentTrack.value?.title || coupleName.value);
const artist = computed(() => currentTrack.value?.artist || coupleName.value);

function nextTrack() {
    if (hasMultiple.value) emit('changeTrack', props.trackIndex + 1);
}
function prevTrack() {
    if (hasMultiple.value) emit('changeTrack', props.trackIndex - 1);
}

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

// Mensagem especial: recolhe em 4 linhas com botão se transbordar.
const showMessage = ref(false);
const messageRef = ref(null);
const isOverflowing = ref(false);
onMounted(async () => {
    await nextTick();
    const el = messageRef.value;
    if (el) isOverflowing.value = el.scrollHeight > el.clientHeight + 1;
});

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

const startYear = computed(() => {
    if (!props.wrapped.relationship_started_on) return null;
    return new Date(props.wrapped.relationship_started_on + 'T00:00:00').getFullYear();
});

const couplePhoto = computed(() => props.wrapped.couple_photo || coverSrc.value);
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
                    <!-- Faixa anterior -->
                    <button class="text-white/90 disabled:opacity-30" :disabled="!hasMultiple" aria-label="Faixa anterior" @click="prevTrack">
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
                    <!-- Próxima faixa -->
                    <button class="text-white/90 disabled:opacity-30" :disabled="!hasMultiple" aria-label="Próxima faixa" @click="nextTrack">
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

                <!-- Sobre o casal: foto + nome + desde + contador -->
                <div class="mt-8 overflow-hidden rounded-2xl bg-white/[0.04]">
                    <div class="relative aspect-[4/3] w-full overflow-hidden">
                        <img
                            v-if="couplePhoto"
                            :src="couplePhoto"
                            class="h-full w-full object-cover"
                            alt="o casal"
                        />
                        <div v-else class="flex h-full w-full items-center justify-center text-6xl">💞</div>
                        <div class="absolute inset-x-0 top-0 bg-gradient-to-b from-black/50 to-transparent p-4">
                            <span class="text-lg font-bold drop-shadow">Sobre o casal</span>
                        </div>
                    </div>
                    <div class="p-5">
                        <h2 class="text-2xl font-extrabold">{{ coupleName }}</h2>
                        <p v-if="startYear" class="mt-1 text-sm text-white/50">Juntos desde {{ startYear }}</p>

                        <div v-if="liveCounter" class="mt-5 grid grid-cols-3 gap-3">
                            <div class="rounded-xl bg-white/[0.05] py-4 text-center">
                                <p class="text-2xl font-extrabold" :style="{ color: accent }">{{ liveCounter.years }}</p>
                                <p class="mt-1 text-xs text-white/50">Anos</p>
                            </div>
                            <div class="rounded-xl bg-white/[0.05] py-4 text-center">
                                <p class="text-2xl font-extrabold" :style="{ color: accent }">{{ liveCounter.months }}</p>
                                <p class="mt-1 text-xs text-white/50">Meses</p>
                            </div>
                            <div class="rounded-xl bg-white/[0.05] py-4 text-center">
                                <p class="text-2xl font-extrabold" :style="{ color: accent }">{{ liveCounter.days }}</p>
                                <p class="mt-1 text-xs text-white/50">Dias</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mensagem especial -->
                <div v-if="wrapped.love_letter" class="mt-6 rounded-2xl bg-white/[0.04] p-5">
                    <h2 class="mb-3 text-lg font-bold">Mensagem especial</h2>
                    <p
                        ref="messageRef"
                        class="whitespace-pre-line text-lg font-bold leading-relaxed text-white/90"
                        :class="{ 'line-clamp-4 [-webkit-mask-image:linear-gradient(to_bottom,black_55%,transparent)] [mask-image:linear-gradient(to_bottom,black_55%,transparent)]': !showMessage }"
                    >{{ wrapped.love_letter }}</p>
                    <button
                        v-if="isOverflowing"
                        class="mt-4 rounded-full bg-white px-5 py-2 text-sm font-bold text-black transition hover:scale-[1.02]"
                        @click="showMessage = !showMessage"
                    >
                        {{ showMessage ? 'Ocultar' : 'Mostrar Mensagem' }}
                    </button>
                </div>

                <!-- Conheça o casal: destaques -->
                <div v-if="wrapped.highlights?.length" class="mt-6 rounded-2xl bg-white/[0.04] p-5">
                    <h2 class="mb-4 text-xl font-extrabold">Conheça {{ wrapped.couple_name_1 }} e {{ wrapped.couple_name_2 }}</h2>
                    <div class="flex gap-3 overflow-x-auto pb-1">
                        <button
                            v-for="(h, i) in wrapped.highlights"
                            :key="h.id"
                            class="relative aspect-[9/16] w-24 shrink-0 overflow-hidden rounded-xl transition active:scale-95"
                            @click="emit('openHighlight', i)"
                        >
                            <img v-if="h.cover" :src="h.cover" class="h-full w-full object-cover" :alt="h.title" />
                            <div v-else class="flex h-full w-full items-center justify-center bg-white/5 text-3xl">💞</div>
                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 to-transparent p-2">
                                <span class="text-sm font-bold leading-tight drop-shadow">{{ h.title }}</span>
                            </div>
                        </button>
                    </div>
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
