<script setup>
import { onMounted, ref } from 'vue';

const props = defineProps({
    date: { type: String, default: null }, // YYYY-MM-DD (semente do céu)
    moon: { type: Object, default: null }, // { name, emoji, illumination }
});

const canvas = ref(null);

// PRNG determinístico (mulberry32) para um céu estável por data.
function seededRandom(seed) {
    return function () {
        seed |= 0;
        seed = (seed + 0x6d2b79f5) | 0;
        let t = Math.imul(seed ^ (seed >>> 15), 1 | seed);
        t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
        return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
}

function hashString(str) {
    let h = 2166136261;
    for (let i = 0; i < str.length; i++) {
        h ^= str.charCodeAt(i);
        h = Math.imul(h, 16777619);
    }
    return h >>> 0;
}

function draw() {
    const el = canvas.value;
    if (!el) return;
    const dpr = window.devicePixelRatio || 1;
    const w = el.clientWidth;
    const h = el.clientHeight;
    el.width = w * dpr;
    el.height = h * dpr;
    const ctx = el.getContext('2d');
    ctx.scale(dpr, dpr);
    ctx.clearRect(0, 0, w, h);

    const rand = seededRandom(hashString(props.date ?? 'noite'));

    // Estrelas
    const count = Math.floor((w * h) / 1600);
    for (let i = 0; i < count; i++) {
        const x = rand() * w;
        const y = rand() * h;
        const r = rand() * 1.4 + 0.2;
        const alpha = rand() * 0.7 + 0.3;
        ctx.beginPath();
        ctx.arc(x, y, r, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(255,255,255,${alpha})`;
        ctx.fill();
    }

    // Algumas estrelas maiores com brilho
    for (let i = 0; i < 6; i++) {
        const x = rand() * w;
        const y = rand() * h * 0.6;
        const grd = ctx.createRadialGradient(x, y, 0, x, y, 6);
        grd.addColorStop(0, 'rgba(255,255,255,0.9)');
        grd.addColorStop(1, 'rgba(255,255,255,0)');
        ctx.fillStyle = grd;
        ctx.fillRect(x - 6, y - 6, 12, 12);
    }
}

onMounted(draw);
</script>

<template>
    <div class="relative h-full w-full overflow-hidden rounded-2xl bg-gradient-to-b from-[#0b1026] to-[#05070f]">
        <canvas ref="canvas" class="absolute inset-0 h-full w-full"></canvas>

        <!-- Lua -->
        <div v-if="moon" class="absolute right-6 top-6 text-right">
            <div class="text-6xl drop-shadow-[0_0_20px_rgba(255,255,255,0.4)]">{{ moon.emoji }}</div>
        </div>
    </div>
</template>
