<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    highlight: { type: Object, required: true },
    yearsTogether: { type: Number, default: null },
});

const emit = defineEmits(['close']);

const photos = computed(() => props.highlight.photos || []);

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
    if (current.value < photos.value.length - 1) {
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

// Reinicia se trocar de destaque sem desmontar.
watch(() => props.highlight, () => { current.value = 0; progress.value = 0; last = 0; });

onMounted(() => {
    raf = requestAnimationFrame(tick);
    window.addEventListener('keydown', onKey);
});
onBeforeUnmount(() => {
    cancelAnimationFrame(raf);
    window.removeEventListener('keydown', onKey);
});
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black">
        <div
            class="relative flex h-full w-full max-w-md flex-col overflow-hidden bg-black sm:h-[92vh] sm:rounded-3xl"
            @pointerdown="paused = true"
            @pointerup="paused = false"
            @pointercancel="paused = false"
        >
            <!-- Foto atual -->
            <img
                v-if="photos[current]"
                :src="photos[current]"
                class="absolute inset-0 h-full w-full object-cover"
                :alt="highlight.title"
            />
            <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-transparent to-black/70"></div>

            <!-- Barras de progresso -->
            <div class="absolute left-0 right-0 top-0 z-20 flex gap-1 p-3">
                <div v-for="(p, i) in photos" :key="i" class="h-1 flex-1 overflow-hidden rounded-full bg-white/30">
                    <div
                        class="h-full rounded-full bg-white"
                        :style="{ width: i < current ? '100%' : i === current ? progress + '%' : '0%' }"
                    ></div>
                </div>
            </div>

            <!-- Topo: voltar + título + fechar -->
            <div class="absolute left-0 right-0 top-6 z-20 flex items-center justify-between px-4 text-white">
                <button class="text-2xl leading-none" aria-label="Voltar" @click.stop="close">‹</button>
                <span class="text-sm font-bold drop-shadow">{{ highlight.title }}</span>
                <button class="text-2xl leading-none text-white/80 hover:text-white" aria-label="Fechar" @click.stop="close">×</button>
            </div>

            <!-- Zonas de navegação -->
            <div class="absolute inset-0 z-10 flex">
                <div class="h-full w-1/3" @click.stop="onClickZone('left')"></div>
                <div class="h-full w-2/3" @click.stop="onClickZone('right')"></div>
            </div>

            <!-- Rodapé: card do destaque -->
            <div class="absolute inset-x-0 bottom-0 z-20 p-4">
                <div class="flex items-center gap-3 rounded-2xl bg-black/40 p-3 backdrop-blur-sm">
                    <img v-if="highlight.photos?.[0]" :src="highlight.photos[0]" class="h-11 w-11 rounded-lg object-cover" :alt="highlight.title" />
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-white">{{ highlight.title }}</p>
                        <p v-if="yearsTogether != null" class="text-xs text-white/60">{{ yearsTogether }} anos juntos</p>
                    </div>
                    <span class="text-white/70" aria-hidden="true">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 5 6 9H2v6h4l5 4z"/><line x1="23" y1="9" x2="17" y2="15"/><line x1="17" y1="9" x2="23" y2="15"/></svg>
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
