<script setup>
import { computed } from 'vue';
import SpotifyNav from '@/Components/Public/SpotifyNav.vue';
import { themeOf } from '@/themes';

const props = defineProps({
    gifterName: { type: String, default: '' },
    theme: { type: String, default: 'green' },
});

defineEmits(['open']);

const accent = computed(() => themeOf(props.theme).accent);
</script>

<template>
    <div class="flex h-full flex-col bg-[#121212] text-white">

        <!-- Conteúdo central -->
        <div class="flex flex-1 flex-col items-center justify-center px-8 text-center">
            <div class="rounded-3xl bg-white/[0.03] px-6 py-16">
                <h1 class="text-4xl font-extrabold leading-tight">
                    <span v-if="gifterName">{{ gifterName }}</span>
                    <span v-else>Alguém</span>
                    separou um
                    <span :style="{ color: accent }">presente</span>
                    especial!
                </h1>
                <p class="mx-auto mt-6 max-w-xs text-lg text-white/60">
                    Um momento único feito com carinho para celebrar a nossa jornada
                </p>

                <button
                    class="mt-12 rounded-full px-12 py-4 text-lg font-bold text-black transition hover:scale-105"
                    :style="{ backgroundColor: accent }"
                    @click="$emit('open')"
                >
                    Ver Presente
                </button>
            </div>
        </div>

        <SpotifyNav />
    </div>
</template>
