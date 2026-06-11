<script setup>
import { onMounted, ref, watch } from 'vue';
import { julianDate, lstDeg, eqToHorizontal, moonEquatorial, bvToColor } from '@/lib/astro';

// Catálogo carregado sob demanda (chunk separado — fora do bundle inicial).
const starsData = ref(null);

// Céu real (domo all-sky, zênite no centro) para uma data/local.
const props = defineProps({
    lat: { type: Number, required: true },
    lng: { type: Number, required: true },
    datetime: { type: String, required: true }, // ISO com fuso, ex.: 2025-02-22T21:00:00-03:00
    moon: { type: Object, default: null }, // { emoji, name }
});

const canvas = ref(null);

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

    const cx = w / 2;
    const cy = h / 2;
    const R = Math.min(w, h) / 2 - 6;

    // Disco do céu (horizonte).
    const grad = ctx.createRadialGradient(cx, cy, 0, cx, cy, R);
    grad.addColorStop(0, '#0b1026');
    grad.addColorStop(1, '#04060d');
    ctx.beginPath();
    ctx.arc(cx, cy, R, 0, Math.PI * 2);
    ctx.fillStyle = grad;
    ctx.fill();

    // Recorta no disco.
    ctx.save();
    ctx.beginPath();
    ctx.arc(cx, cy, R, 0, Math.PI * 2);
    ctx.clip();

    const date = new Date(props.datetime);
    const jd = julianDate(date);
    const lst = lstDeg(jd, props.lng);

    // alt/az -> x,y no domo (zênite no centro, Norte para cima).
    const project = (alt, az) => {
        const r = ((90 - alt) / 90) * R;
        const a = az * (Math.PI / 180);
        return { x: cx + r * Math.sin(a), y: cy - r * Math.cos(a) };
    };

    // Estrelas (só depois do catálogo carregar).
    for (const f of starsData.value?.features ?? []) {
        const mag = f.properties.mag;
        if (mag > 5.5) continue;
        const [ra, dec] = f.geometry.coordinates;
        const { alt, az } = eqToHorizontal(ra, dec, props.lat, lst);
        if (alt <= 0) continue;

        const { x, y } = project(alt, az);
        const radius = Math.max(0.4, (6 - mag) * 0.42);
        const alpha = Math.max(0.35, Math.min(1, (6 - mag) / 5));

        ctx.beginPath();
        ctx.arc(x, y, radius, 0, Math.PI * 2);
        ctx.fillStyle = bvToColor(f.properties.bv);
        ctx.globalAlpha = alpha;
        ctx.fill();

        // Halo das mais brilhantes.
        if (mag < 1.5) {
            const g = ctx.createRadialGradient(x, y, 0, x, y, radius * 4);
            g.addColorStop(0, 'rgba(255,255,255,0.5)');
            g.addColorStop(1, 'rgba(255,255,255,0)');
            ctx.fillStyle = g;
            ctx.fillRect(x - radius * 4, y - radius * 4, radius * 8, radius * 8);
        }
    }
    ctx.globalAlpha = 1;

    // Lua na posição real (se acima do horizonte).
    const m = moonEquatorial(date);
    const mh = eqToHorizontal(m.ra, m.dec, props.lat, lst);
    if (mh.alt > 0) {
        const { x, y } = project(mh.alt, mh.az);
        const g = ctx.createRadialGradient(x, y, 0, x, y, 26);
        g.addColorStop(0, 'rgba(255,255,255,0.35)');
        g.addColorStop(1, 'rgba(255,255,255,0)');
        ctx.fillStyle = g;
        ctx.fillRect(x - 26, y - 26, 52, 52);

        ctx.font = '26px serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(props.moon?.emoji || '🌕', x, y);
    }

    ctx.restore();

    // Borda do horizonte.
    ctx.beginPath();
    ctx.arc(cx, cy, R, 0, Math.PI * 2);
    ctx.strokeStyle = 'rgba(255,255,255,0.12)';
    ctx.lineWidth = 1.5;
    ctx.stroke();
}

onMounted(async () => {
    draw(); // desenha disco/lua de imediato
    const mod = await import('@/lib/stars.6.json');
    starsData.value = mod.default;
    draw(); // redesenha com as estrelas
});
watch(() => [props.lat, props.lng, props.datetime], draw);
</script>

<template>
    <div class="relative h-full w-full">
        <canvas ref="canvas" class="h-full w-full"></canvas>
    </div>
</template>
