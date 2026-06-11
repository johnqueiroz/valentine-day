<script setup>
import { onMounted, ref } from 'vue';

const props = defineProps({
    title: { type: String, default: 'Seu presente será:' },
    options: { type: Array, default: () => [] },
    accent: { type: String, default: '#1DB954' },
});

const emit = defineEmits(['done']);

const canvas = ref(null);
const rotation = ref(0); // graus
const spinning = ref(false);
const result = ref(null);

const COLORS = ['#f0bfca', '#d98aa0'];
const SIZE = 300;

function draw() {
    const el = canvas.value;
    if (!el) return;
    const dpr = window.devicePixelRatio || 1;
    el.width = SIZE * dpr;
    el.height = SIZE * dpr;
    const ctx = el.getContext('2d');
    ctx.scale(dpr, dpr);

    const n = props.options.length;
    const cx = SIZE / 2;
    const cy = SIZE / 2;
    const r = SIZE / 2 - 4;
    const seg = (Math.PI * 2) / n;

    for (let i = 0; i < n; i++) {
        const start = i * seg;
        const end = start + seg;
        ctx.beginPath();
        ctx.moveTo(cx, cy);
        ctx.arc(cx, cy, r, start, end);
        ctx.closePath();
        ctx.fillStyle = COLORS[i % COLORS.length];
        // Garante contraste se nº ímpar (última = primeira cor)
        if (i === n - 1 && n % 2 === 1) ctx.fillStyle = '#e0a3b4';
        ctx.fill();

        // Rótulo
        ctx.save();
        ctx.translate(cx, cy);
        ctx.rotate(start + seg / 2);
        ctx.textAlign = 'right';
        ctx.textBaseline = 'middle';
        ctx.fillStyle = '#fff';
        ctx.font = '600 13px system-ui, sans-serif';
        const label = props.options[i];
        const max = 18;
        ctx.fillText(label.length > max ? label.slice(0, max - 1) + '…' : label, r - 12, 0);
        ctx.restore();
    }

    // Borda
    ctx.beginPath();
    ctx.arc(cx, cy, r, 0, Math.PI * 2);
    ctx.strokeStyle = 'rgba(0,0,0,0.25)';
    ctx.lineWidth = 3;
    ctx.stroke();
}

function spin() {
    if (spinning.value) return;
    spinning.value = true;
    result.value = null;

    const n = props.options.length;
    const segDeg = 360 / n;
    const idx = Math.floor(Math.random() * n);
    const centerDeg = idx * segDeg + segDeg / 2;
    // Ponteiro no topo (270°). Alinhar centro da fatia ao topo.
    const align = ((270 - centerDeg) % 360 + 360) % 360;
    const base = rotation.value - (rotation.value % 360);
    rotation.value = base + align + 360 * 6;

    const onEnd = () => {
        canvas.value.removeEventListener('transitionend', onEnd);
        result.value = props.options[idx];
        spinning.value = false;
    };
    canvas.value.addEventListener('transitionend', onEnd);
}

onMounted(draw);
</script>

<template>
    <div class="flex h-full w-full flex-col items-center overflow-y-auto px-5 pt-12 text-white">
        <h2 class="text-center text-3xl font-black leading-tight">{{ title }}</h2>

        <div class="relative mt-8 flex flex-1 flex-col items-center justify-center">
            <!-- Ponteiro -->
            <div class="absolute left-1/2 top-0 z-20 -translate-x-1/2" style="margin-top: -2px">
                <div class="h-0 w-0 border-l-[12px] border-r-[12px] border-t-[18px] border-l-transparent border-r-transparent" :style="{ borderTopColor: '#d98aa0' }"></div>
            </div>

            <div class="relative" :style="{ width: SIZE + 'px', height: SIZE + 'px' }">
                <canvas
                    ref="canvas"
                    class="h-full w-full"
                    :style="{
                        transform: `rotate(${rotation}deg)`,
                        transition: spinning ? 'transform 5s cubic-bezier(0.17,0.67,0.2,1)' : 'none',
                    }"
                ></canvas>
                <!-- Coração central -->
                <div class="absolute left-1/2 top-1/2 z-10 flex h-12 w-12 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-[#e9b8c4] text-2xl shadow">❤️</div>
            </div>

            <!-- Resultado -->
            <p v-if="result" class="mt-6 text-center text-2xl font-black" :style="{ color: accent }">{{ result }}</p>
        </div>

        <!-- Botão -->
        <div class="w-full max-w-sm pb-[calc(1.5rem+env(safe-area-inset-bottom))]">
            <button
                v-if="!result"
                class="w-full rounded-xl bg-white py-4 text-lg font-bold text-black shadow-lg transition active:scale-[0.98] disabled:opacity-50"
                :disabled="spinning"
                @click="spin"
            >
                {{ spinning ? 'Girando…' : 'Girar Roleta' }}
            </button>
            <button
                v-else
                class="w-full rounded-full py-4 text-lg font-bold text-black"
                :style="{ backgroundColor: accent }"
                @click="emit('done')"
            >
                Próxima Seção →
            </button>
        </div>
    </div>
</template>
