<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import RealSky from '@/Components/Public/RealSky.vue';
import WordleGame from '@/Components/Public/WordleGame.vue';
import PrizeWheel from '@/Components/Public/PrizeWheel.vue';
import { themeOf } from '@/themes';

const props = defineProps({
    wrapped: { type: Object, required: true },
    isPlaying: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'toggle-audio']);

const theme = computed(() => themeOf(props.wrapped.theme));
const accent = computed(() => theme.value.accent);
const couple = computed(() => ({
    name1: props.wrapped.couple_name_1,
    name2: props.wrapped.couple_name_2,
    days: props.wrapped.days_together,
}));

// Sequência de cenas (estilo "vídeo" Wrapped).
const stories = computed(() => {
    const w = props.wrapped;
    const list = [{ kind: 'intro' }];
    if (w.days_together != null) list.push({ kind: 'days' });
    if (w.star_map) list.push({ kind: 'sky' });
    if (w.season) list.push({ kind: 'season' });
    (w.games || []).forEach((g) => {
        list.push({ kind: 'gameIntro', game: g });
        list.push({ kind: 'game', game: g });
    });
    (w.wheels || []).forEach((wh) => {
        list.push({ kind: 'wheelIntro', wheel: wh });
        list.push({ kind: 'wheel', wheel: wh });
    });
    list.push({ kind: 'outro' });
    return list;
});

const DURATION = 6000;
const current = ref(0);
const progress = ref(0);
const paused = ref(false);
let raf = null;
let last = 0;

function tick(now) {
    if (!paused.value && scene.value.kind !== 'game' && scene.value.kind !== 'wheel') {
        if (!last) last = now;
        progress.value += ((now - last) / DURATION) * 100;
        last = now;
        if (progress.value >= 100) next();
    } else {
        last = now;
    }
    raf = requestAnimationFrame(tick);
}

function next() {
    if (current.value < stories.value.length - 1) {
        current.value++;
        progress.value = 0;
        last = 0;
    } else {
        close();
    }
}
function prev() {
    if (current.value > 0) current.value--;
    progress.value = 0;
    last = 0;
}
function close() {
    emit('close');
}
function onClickZone(zone) {
    zone === 'left' ? prev() : next();
}
function onKey(e) {
    if (e.key === 'ArrowRight' || e.key === ' ') next();
    else if (e.key === 'ArrowLeft') prev();
    else if (e.key === 'Escape') close();
}

onMounted(() => {
    raf = requestAnimationFrame(tick);
    window.addEventListener('keydown', onKey);
});
onBeforeUnmount(() => {
    cancelAnimationFrame(raf);
    window.removeEventListener('keydown', onKey);
});

// Barras do equalizer (decorativo nos cantos).
const bars = [0, 1, 2, 3, 4, 5];

// Fundo por cena.
const introBg = computed(
    () => `linear-gradient(160deg, ${accent.value} 0%, ${theme.value.tint} 55%, #0a0a0a 100%)`
);
const scene = computed(() => stories.value[current.value]);
</script>

<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black"
        @pointerdown="paused = true"
        @pointerup="paused = false"
        @pointercancel="paused = false"
    >
        <div class="relative flex h-full w-full max-w-md flex-col overflow-hidden bg-[#0a0a0a] sm:h-[92vh] sm:rounded-3xl">
            <!-- Barras de progresso -->
            <div class="absolute left-0 right-0 top-0 z-30 flex gap-1 p-3">
                <div v-for="(s, i) in stories" :key="i" class="h-1 flex-1 overflow-hidden rounded-full bg-white/25">
                    <div
                        class="h-full rounded-full bg-white"
                        :style="{ width: i < current ? '100%' : i === current ? progress + '%' : '0%' }"
                    ></div>
                </div>
            </div>

            <!-- Mute + fechar -->
            <div class="absolute right-3 top-6 z-30 flex items-center gap-3">
                <button class="text-white/80 hover:text-white" aria-label="Som" @click.stop="emit('toggle-audio')">
                    <svg v-if="isPlaying" viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 5 6 9H2v6h4l5 4z"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
                    <svg v-else viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 5 6 9H2v6h4l5 4z"/><line x1="23" y1="9" x2="17" y2="15"/><line x1="17" y1="9" x2="23" y2="15"/></svg>
                </button>
                <button class="text-2xl leading-none text-white/80 hover:text-white" aria-label="Fechar" @click.stop="close">×</button>
            </div>

            <!-- Zonas de navegação (desativadas em jogo/roleta) -->
            <div v-if="scene.kind !== 'game' && scene.kind !== 'wheel'" class="absolute inset-0 z-20 flex">
                <div class="h-full w-1/3" @click.stop="onClickZone('left')"></div>
                <div class="h-full w-2/3" @click.stop="onClickZone('right')"></div>
            </div>

            <!-- Cena -->
            <div
                class="relative z-10 flex flex-1 flex-col items-center justify-center text-center text-white"
                :class="scene.kind === 'game' || scene.kind === 'wheel' ? 'overflow-y-auto' : 'overflow-hidden px-8'"
            >
                <transition name="fade" mode="out-in">
                    <div :key="current" class="flex h-full w-full flex-col items-center justify-center">
                        <!-- INTRO -->
                        <template v-if="scene.kind === 'intro'">
                            <div class="absolute inset-0" :style="{ background: introBg }"></div>
                            <div class="eq eq-tl"><span v-for="b in bars" :key="b" :style="{ animationDelay: b * 0.12 + 's', background: accent }"></span></div>
                            <div class="eq eq-tr"><span v-for="b in bars" :key="b" :style="{ animationDelay: b * 0.1 + 's', background: accent }"></span></div>
                            <div class="eq eq-bl"><span v-for="b in bars" :key="b" :style="{ animationDelay: b * 0.14 + 's', background: accent }"></span></div>
                            <div class="eq eq-br"><span v-for="b in bars" :key="b" :style="{ animationDelay: b * 0.08 + 's', background: accent }"></span></div>
                            <div class="relative">
                                <h1 class="text-5xl font-black leading-tight drop-shadow">O Wrapped<br />de vocês</h1>
                                <p class="mx-auto mt-6 max-w-xs text-lg font-semibold text-white/85">
                                    {{ couple.days }} dias de histórias, momentos e conexões
                                </p>
                            </div>
                        </template>

                        <!-- DIAS -->
                        <template v-else-if="scene.kind === 'days'">
                            <p class="text-xs uppercase tracking-[0.3em] text-white/60">Juntos há</p>
                            <p class="my-3 text-8xl font-black tabular-nums" :style="{ color: accent }">{{ couple.days }}</p>
                            <p class="text-2xl font-light">dias</p>
                        </template>

                        <!-- CÉU REAL -->
                        <template v-else-if="scene.kind === 'sky'">
                            <p class="absolute left-0 right-0 top-16 z-10 mx-auto max-w-xs px-6 text-2xl font-bold leading-snug drop-shadow">
                                Como estavam as estrelas na noite em que começamos a namorar
                            </p>
                            <div class="absolute inset-0">
                                <RealSky
                                    :lat="wrapped.star_map.lat"
                                    :lng="wrapped.star_map.lng"
                                    :datetime="wrapped.star_map.datetime"
                                    :moon="wrapped.moon"
                                />
                            </div>
                            <div class="absolute bottom-12 left-0 right-0 z-10 text-center">
                                <p v-if="wrapped.moon" class="text-lg font-semibold">{{ wrapped.moon.emoji }} {{ wrapped.moon.name }}</p>
                                <p class="text-sm text-white/60">{{ wrapped.star_map.city }} · 22/02/2025</p>
                            </div>
                        </template>

                        <!-- ESTAÇÃO -->
                        <template v-else-if="scene.kind === 'season'">
                            <p class="text-2xl font-bold">Durante a estação de</p>
                            <div class="my-8 text-8xl float">{{ wrapped.season.icon }}</div>
                            <p class="text-4xl font-black" :style="{ color: wrapped.season.color }">{{ wrapped.season.name }}</p>
                        </template>

                        <!-- INTRO DO JOGO -->
                        <template v-else-if="scene.kind === 'gameIntro'">
                            <div class="eq eq-tl"><span v-for="b in bars" :key="b" :style="{ animationDelay: b * 0.12 + 's', background: accent }"></span></div>
                            <div class="eq eq-br"><span v-for="b in bars" :key="b" :style="{ animationDelay: b * 0.08 + 's', background: accent }"></span></div>
                            <p class="text-xl font-semibold text-white/80">Vamos jogar um</p>
                            <p class="mt-1 text-7xl font-black" :style="{ color: accent }">Jogo?</p>
                        </template>

                        <!-- JOGO (Termo) -->
                        <template v-else-if="scene.kind === 'game'">
                            <div class="absolute inset-0">
                                <WordleGame
                                    :key="scene.game.id"
                                    :question="scene.game.question"
                                    :answers="scene.game.answers"
                                    :message="scene.game.message"
                                    :accent="accent"
                                    :storage-key="'wrapped_word_' + scene.game.id"
                                    @done="next"
                                />
                            </div>
                        </template>

                        <!-- INTRO DA ROLETA -->
                        <template v-else-if="scene.kind === 'wheelIntro'">
                            <div class="eq eq-tl"><span v-for="b in bars" :key="b" :style="{ animationDelay: b * 0.12 + 's', background: accent }"></span></div>
                            <div class="eq eq-br"><span v-for="b in bars" :key="b" :style="{ animationDelay: b * 0.08 + 's', background: accent }"></span></div>
                            <p class="text-xl font-semibold text-white/80">Será que você está com</p>
                            <p class="mt-1 text-6xl font-black" :style="{ color: accent }">Sorte Hoje?</p>
                        </template>

                        <!-- ROLETA -->
                        <template v-else-if="scene.kind === 'wheel'">
                            <div class="absolute inset-0">
                                <PrizeWheel
                                    :key="scene.wheel.id"
                                    :title="scene.wheel.title"
                                    :options="scene.wheel.options"
                                    :accent="accent"
                                    @done="next"
                                />
                            </div>
                        </template>

                        <!-- OUTRO -->
                        <template v-else>
                            <h2 class="text-4xl font-black">Aqui é só o começo 💞</h2>
                            <p class="mt-4 max-w-xs text-white/80">
                                {{ couple.name1 }} &amp; {{ couple.name2 }}
                                <span v-if="couple.days"> — {{ couple.days }} dias e contando.</span>
                            </p>
                            <button class="mt-8 rounded-full px-6 py-3 font-bold text-black" :style="{ backgroundColor: accent }" @click.stop="close">Voltar</button>
                        </template>
                    </div>
                </transition>
            </div>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.45s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Equalizer nos cantos */
.eq {
    position: absolute;
    display: flex;
    align-items: flex-end;
    gap: 4px;
    height: 90px;
    opacity: 0.85;
}
.eq span {
    width: 8px;
    height: 100%;
    border-radius: 4px;
    transform-origin: bottom;
    animation: eq 0.9s ease-in-out infinite alternate;
}
@keyframes eq {
    0% { transform: scaleY(0.25); }
    100% { transform: scaleY(1); }
}
.eq-tl { top: 36px; left: 16px; }
.eq-tr { top: 36px; right: 16px; transform: scaleX(-1); }
.eq-bl { bottom: 24px; left: 16px; transform: scaleY(-1); }
.eq-br { bottom: 24px; right: 16px; transform: scale(-1, -1); }

/* Flutuar (ícone da estação) */
.float { animation: float 3s ease-in-out infinite; }
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-14px); }
}
</style>
