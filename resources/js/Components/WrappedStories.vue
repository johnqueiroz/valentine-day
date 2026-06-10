<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import StarMap from '@/Components/Public/StarMap.vue';
import { themeOf } from '@/themes';

const props = defineProps({
    wrapped: { type: Object, required: true },
});

const emit = defineEmits(['close']);

const accent = computed(() => themeOf(props.wrapped.theme).accent);
const couple = computed(() => ({
    name1: props.wrapped.couple_name_1,
    name2: props.wrapped.couple_name_2,
    daysTogether: props.wrapped.days_together,
}));

// Sequência de stories: intro -> tempo juntos -> slides autorais -> lua/mapa -> galeria -> outro.
const stories = computed(() => {
    const list = [{ kind: 'intro' }];
    if (props.wrapped.days_together != null) list.push({ kind: 'days' });
    props.wrapped.slides.forEach((s) => list.push({ kind: 'slide', slide: s }));
    if (props.wrapped.moon) list.push({ kind: 'starmap' });
    if (props.wrapped.photos.length) list.push({ kind: 'gallery' });
    list.push({ kind: 'outro' });
    return list;
});

const DURATION = 5000;
const current = ref(0);
const progress = ref(0);
const paused = ref(false);
let raf = null;
let last = 0;

function tick(now) {
    if (!paused.value) {
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
    } else {
        close();
    }
}
function prev() {
    if (current.value > 0) current.value--;
    progress.value = 0;
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

const typeLabel = {
    stat: 'Em números',
    music: 'Trilha sonora',
    place: 'Nossos lugares',
    milestone: 'Um marco',
    message: 'Para você',
};

function bigNumber(slide) {
    return slide.meta?.value || slide.meta?.plays || slide.meta?.count || null;
}
</script>

<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black"
        @pointerdown="paused = true"
        @pointerup="paused = false"
        @pointercancel="paused = false"
    >
        <div class="relative flex h-full w-full max-w-md flex-col overflow-hidden bg-[#121212] sm:h-[92vh] sm:rounded-3xl">
            <!-- Barras de progresso -->
            <div class="absolute left-0 right-0 top-0 z-20 flex gap-1 p-3">
                <div v-for="(s, i) in stories" :key="i" class="h-1 flex-1 overflow-hidden rounded-full bg-white/25">
                    <div
                        class="h-full rounded-full bg-white"
                        :style="{ width: i < current ? '100%' : i === current ? progress + '%' : '0%' }"
                    ></div>
                </div>
            </div>

            <button class="absolute right-3 top-6 z-20 text-2xl leading-none text-white/80 hover:text-white" @click.stop="close">×</button>

            <!-- Zonas de navegação -->
            <div class="absolute inset-0 z-10 flex">
                <div class="h-full w-1/3" @click.stop="onClickZone('left')"></div>
                <div class="h-full w-2/3" @click.stop="onClickZone('right')"></div>
            </div>

            <div class="relative z-0 flex flex-1 flex-col items-center justify-center px-8 text-center text-white">
                <transition name="fade" mode="out-in">
                    <div :key="'s' + current" class="flex w-full flex-col items-center">
                        <!-- Intro -->
                        <template v-if="stories[current].kind === 'intro'">
                            <p class="text-sm uppercase tracking-[0.3em] text-white/60">A retrospectiva de</p>
                            <h1 class="mt-4 text-4xl font-extrabold leading-tight">
                                {{ couple.name1 }}<span class="block text-2xl font-light">&amp;</span>{{ couple.name2 }}
                            </h1>
                            <p class="mt-8 animate-pulse text-sm text-white/60">toque para começar →</p>
                        </template>

                        <!-- Tempo juntos -->
                        <template v-else-if="stories[current].kind === 'days'">
                            <p class="text-xs uppercase tracking-[0.3em] text-white/60">Juntos há</p>
                            <p class="my-3 text-8xl font-black" :style="{ color: accent }">{{ couple.daysTogether }}</p>
                            <p class="text-2xl font-light">dias</p>
                        </template>

                        <!-- Slide autoral -->
                        <template v-else-if="stories[current].kind === 'slide'">
                            <p class="text-xs uppercase tracking-[0.3em] text-white/60">{{ typeLabel[stories[current].slide.type] ?? '' }}</p>
                            <div v-if="bigNumber(stories[current].slide)" class="my-4 text-7xl font-black" :style="{ color: accent }">
                                {{ bigNumber(stories[current].slide) }}
                                <span class="block text-base font-medium text-white/70">{{ stories[current].slide.meta?.unit || '' }}</span>
                            </div>
                            <h2 class="mt-2 text-3xl font-bold">{{ stories[current].slide.title }}</h2>
                            <p v-if="stories[current].slide.meta?.artist" class="mt-1 text-lg text-white/70">{{ stories[current].slide.meta.artist }}</p>
                            <p v-if="stories[current].slide.meta?.location" class="mt-1 text-lg text-white/70">📍 {{ stories[current].slide.meta.location }}</p>
                            <p v-if="stories[current].slide.meta?.date" class="mt-1 text-lg text-white/70">🗓️ {{ stories[current].slide.meta.date }}</p>
                            <p v-if="stories[current].slide.body" class="mt-4 max-w-xs text-white/80">{{ stories[current].slide.body }}</p>
                        </template>

                        <!-- Mapa estelar + lua -->
                        <template v-else-if="stories[current].kind === 'starmap'">
                            <p class="mb-3 text-xs uppercase tracking-[0.3em] text-white/60">O céu da noite em que tudo começou</p>
                            <div class="h-72 w-full max-w-xs">
                                <StarMap :date="wrapped.relationship_started_on" :moon="wrapped.moon" />
                            </div>
                            <p class="mt-4 text-lg font-semibold">{{ wrapped.moon.emoji }} {{ wrapped.moon.name }}</p>
                            <p class="text-sm text-white/60">{{ Math.round(wrapped.moon.illumination * 100) }}% iluminada</p>
                        </template>

                        <!-- Galeria -->
                        <template v-else-if="stories[current].kind === 'gallery'">
                            <p class="mb-5 text-xs uppercase tracking-[0.3em] text-white/60">Nossos momentos</p>
                            <div class="grid grid-cols-2 gap-2">
                                <img v-for="photo in wrapped.photos.slice(0, 6)" :key="photo.id" :src="photo.url" class="h-28 w-28 rounded-xl object-cover shadow-lg" :alt="photo.caption ?? ''" />
                            </div>
                        </template>

                        <!-- Outro -->
                        <template v-else>
                            <h2 class="text-3xl font-bold">Aqui é só o começo 💞</h2>
                            <p class="mt-4 max-w-xs text-white/80">
                                {{ couple.name1 }} &amp; {{ couple.name2 }}
                                <span v-if="couple.daysTogether"> — {{ couple.daysTogether }} dias e contando.</span>
                            </p>
                            <button class="mt-8 rounded-full px-6 py-2 font-semibold text-black" :style="{ backgroundColor: accent }" @click.stop="close">Voltar</button>
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
    transition: opacity 0.35s ease, transform 0.35s ease;
}
.fade-enter-from {
    opacity: 0;
    transform: translateY(12px);
}
.fade-leave-to {
    opacity: 0;
    transform: translateY(-12px);
}
</style>
