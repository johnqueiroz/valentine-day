<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';

const props = defineProps({
    question: { type: String, default: '' },
    answers: { type: Array, required: true }, // lista de palavras A–Z; rotaciona por acerto
    message: { type: String, default: '' },
    accent: { type: String, default: '#1DB954' },
    storageKey: { type: String, default: 'wrapped_word' },
});

const emit = defineEmits(['done']);

// Índice persistente: cada acerto avança p/ a próxima palavra (volta à 1ª no fim).
const wordIdx = ref(0);
onMounted(() => {
    const stored = Number(localStorage.getItem(props.storageKey) || 0);
    wordIdx.value = Number.isFinite(stored) ? stored : 0;
});

const ANSWER = computed(() => {
    const list = props.answers.length ? props.answers : ['SORRISO'];
    return (list[wordIdx.value % list.length] || '').toUpperCase();
});
const LEN = computed(() => ANSWER.value.length);
const MAX_ROWS = 6;

const guesses = ref([]); // [{ word, states: ['correct'|'present'|'absent'] }]
const current = ref('');
const finished = ref(false);
const won = ref(false);
const keyStatus = reactive({}); // letra -> estado

const KEYS = [
    ['Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P'],
    ['A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L'],
    ['Z', 'X', 'C', 'V', 'B', 'N', 'M', 'DEL'],
];

const COLORS = {
    correct: '#3aa55a',
    present: '#c9a227',
    absent: '#3a3a3c',
};

function evaluate(guess) {
    const ans = ANSWER.value.split('');
    const states = new Array(LEN.value).fill('absent');
    const counts = {};
    ans.forEach((c) => (counts[c] = (counts[c] || 0) + 1));
    // 1ª passada: corretas
    for (let i = 0; i < LEN.value; i++) {
        if (guess[i] === ans[i]) {
            states[i] = 'correct';
            counts[guess[i]]--;
        }
    }
    // 2ª passada: presentes
    for (let i = 0; i < LEN.value; i++) {
        if (states[i] === 'correct') continue;
        const c = guess[i];
        if (counts[c] > 0) {
            states[i] = 'present';
            counts[c]--;
        }
    }
    return states;
}

const PRIORITY = { absent: 0, present: 1, correct: 2 };
function bumpKey(letter, state) {
    if (PRIORITY[state] > PRIORITY[keyStatus[letter] ?? -1]) keyStatus[letter] = state;
}

function submit() {
    if (finished.value || current.value.length !== LEN.value) return;
    const word = current.value;
    const states = evaluate(word);
    guesses.value.push({ word, states });
    word.split('').forEach((c, i) => bumpKey(c, states[i]));
    current.value = '';

    if (word === ANSWER.value) {
        won.value = true;
        finished.value = true;
        burstConfetti();
        // Avança a palavra p/ a próxima execução (cicla).
        const list = props.answers.length ? props.answers : ['SORRISO'];
        localStorage.setItem(props.storageKey, String((wordIdx.value + 1) % list.length));
    } else if (guesses.value.length >= MAX_ROWS) {
        finished.value = true;
    }
}

function addLetter(l) {
    if (finished.value || current.value.length >= LEN.value) return;
    current.value += l;
}
function backspace() {
    current.value = current.value.slice(0, -1);
}
function pressKey(k) {
    if (k === 'ENTER') submit();
    else if (k === 'DEL') backspace();
    else addLetter(k);
}

function onKeydown(e) {
    if (e.key === 'Enter') submit();
    else if (e.key === 'Backspace') backspace();
    else if (/^[a-zA-Z]$/.test(e.key)) addLetter(e.key.toUpperCase());
}

onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown));

// Célula (linha r, coluna c).
function cell(r, c) {
    if (r < guesses.value.length) {
        const g = guesses.value[r];
        return { letter: g.word[c], color: COLORS[g.states[c]], filled: true };
    }
    if (r === guesses.value.length && !finished.value) {
        return { letter: current.value[c] || '', color: null, filled: !!current.value[c] };
    }
    return { letter: '', color: null, filled: false };
}
const rowsRange = computed(() => Array.from({ length: MAX_ROWS }, (_, i) => i));
const colsRange = computed(() => Array.from({ length: LEN.value }, (_, i) => i));
const answerCells = computed(() => ANSWER.value.split(''));

// Confete (explosão).
const confetti = ref([]);
const PALETTE = ['#1DB954', '#ec4899', '#3b82f6', '#fbbf24', '#a855f7', '#ef4444', '#22d3ee'];
function burstConfetti() {
    const pieces = [];
    for (let i = 0; i < 110; i++) {
        const angle = Math.random() * Math.PI * 2;
        const dist = 120 + Math.random() * 320;
        pieces.push({
            id: i,
            color: PALETTE[i % PALETTE.length],
            x: Math.cos(angle) * dist + 'px',
            y: Math.sin(angle) * dist + Math.random() * 200 + 'px',
            rot: Math.random() * 720 - 360 + 'deg',
            delay: Math.random() * 0.15 + 's',
            duration: 1.6 + Math.random() * 1.2 + 's',
        });
    }
    confetti.value = pieces;
    setTimeout(() => (confetti.value = []), 3000);
}
</script>

<template>
    <div class="flex h-full w-full flex-col items-center overflow-y-auto px-5 pt-12 pb-[calc(1rem+env(safe-area-inset-bottom))] text-white">
        <!-- Confete -->
        <div v-if="confetti.length" class="pointer-events-none fixed inset-0 z-40 flex items-center justify-center">
            <span
                v-for="p in confetti"
                :key="p.id"
                class="confetti absolute h-2.5 w-2.5 rounded-[2px]"
                :style="{
                    background: p.color,
                    '--x': p.x,
                    '--y': p.y,
                    '--rot': p.rot,
                    animationDelay: p.delay,
                    animationDuration: p.duration,
                }"
            ></span>
        </div>

        <!-- Título -->
        <h2 class="text-center text-3xl font-black leading-tight">{{ question }}</h2>
        <p class="mt-2 text-sm text-white/55">Descubra a palavra secreta ({{ LEN }} letras)</p>

        <!-- Grade (responsiva: cabe na largura mesmo com palavras longas) -->
        <div class="mt-6 flex w-full flex-col items-center justify-center">
            <div
                v-for="r in rowsRange"
                :key="r"
                class="mb-1.5 grid w-full gap-1.5"
                :style="{ gridTemplateColumns: `repeat(${LEN}, minmax(0, 1fr))`, maxWidth: LEN * 3.25 + 'rem' }"
            >
                <div
                    v-for="c in colsRange"
                    :key="c"
                    class="flex aspect-square items-center justify-center rounded-md text-base font-bold uppercase sm:text-xl"
                    :style="{
                        backgroundColor: cell(r, c).color || 'transparent',
                        border: cell(r, c).color ? 'none' : '2px solid rgba(255,255,255,0.18)',
                    }"
                >
                    {{ cell(r, c).letter }}
                </div>
            </div>
        </div>

        <!-- Card de fim -->
        <div v-if="finished" class="mb-4 w-full max-w-sm rounded-2xl bg-white/[0.06] p-5 text-center">
            <p class="text-lg font-bold">{{ won ? 'Parabéns! 🎉' : 'Quase! 💚' }}</p>
            <p class="mt-1 text-sm text-white/60">A palavra era</p>
            <div class="mt-3 flex justify-center gap-1.5">
                <div
                    v-for="(l, i) in answerCells"
                    :key="i"
                    class="flex h-10 w-10 items-center justify-center rounded-md text-lg font-bold"
                    :style="{ backgroundColor: COLORS.correct }"
                >{{ l }}</div>
            </div>
            <p v-if="message" class="mt-4 text-lg italic text-white/90">“{{ message }}”</p>
            <button
                class="mt-5 w-full rounded-full py-3 font-bold text-black"
                :style="{ backgroundColor: accent }"
                @click="emit('done')"
            >
                Próxima Seção →
            </button>
        </div>

        <!-- Teclado -->
        <div v-else class="mt-6 w-full max-w-md">
            <div v-for="(row, ri) in KEYS" :key="ri" class="mb-1.5 flex justify-center gap-1.5">
                <button
                    v-for="k in row"
                    :key="k"
                    class="flex h-11 items-center justify-center rounded-md bg-white/15 font-bold uppercase active:bg-white/30"
                    :class="k === 'DEL' ? 'px-4 text-base' : 'flex-1 text-sm'"
                    :style="keyStatus[k] ? { backgroundColor: COLORS[keyStatus[k]] } : {}"
                    @click="pressKey(k)"
                >
                    <span v-if="k === 'DEL'">⌫</span>
                    <span v-else>{{ k }}</span>
                </button>
            </div>

            <!-- ENTER destacado -->
            <button
                class="mt-3 w-full rounded-xl py-4 text-xl font-extrabold uppercase tracking-wide text-black shadow-lg transition active:scale-[0.98] disabled:opacity-40"
                :style="{ backgroundColor: accent }"
                :disabled="current.length !== LEN"
                @click="submit"
            >
                Enter
            </button>
        </div>
    </div>
</template>

<style scoped>
.confetti {
    top: 50%;
    left: 50%;
    animation-name: confetti-burst;
    animation-timing-function: cubic-bezier(0.2, 0.6, 0.3, 1);
    animation-fill-mode: forwards;
}
@keyframes confetti-burst {
    0% { transform: translate(0, 0) rotate(0deg); opacity: 1; }
    100% { transform: translate(var(--x), var(--y)) rotate(var(--rot)); opacity: 0; }
}
</style>
