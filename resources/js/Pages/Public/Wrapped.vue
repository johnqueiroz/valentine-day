<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import GiftIntro from '@/Components/Public/GiftIntro.vue';
import SpotifyPlayer from '@/Components/Public/SpotifyPlayer.vue';
import WrappedStories from '@/Components/WrappedStories.vue';
import { useYouTubePlayer } from '@/useYouTubePlayer';

const props = defineProps({
    wrapped: { type: Object, required: true },
});

const screen = ref('gift'); // 'gift' | 'player'
const showStories = ref(false);

const hasAudio = computed(() => !!props.wrapped.youtube_id);

const yt = useYouTubePlayer();
const started = ref(false);

function openPresent() {
    // Toque do usuário: inicia o áudio (autoplay com som permitido) e vai ao player.
    // Só inicializa uma vez — ao voltar e reabrir, a música continua de onde parou.
    if (hasAudio.value && !started.value) {
        yt.init('yt-player', props.wrapped.youtube_id, { autoplay: true });
        started.value = true;
    }
    screen.value = 'player';
}
</script>

<template>
    <Head :title="`${wrapped.couple_name_1} & ${wrapped.couple_name_2} — Wrapped`" />

    <!-- Container mobile-first centralizado -->
    <div class="flex min-h-screen justify-center bg-black">
        <div class="relative h-screen w-full max-w-md overflow-hidden bg-[#121212]">
            <!-- Player oculto do YouTube (áudio) -->
            <div class="pointer-events-none absolute h-px w-px opacity-0">
                <div id="yt-player"></div>
            </div>

            <transition name="screen" mode="out-in">
                <GiftIntro
                    v-if="screen === 'gift'"
                    key="gift"
                    :gifter-name="wrapped.gifter_name"
                    :theme="wrapped.theme"
                    @open="openPresent"
                />
                <SpotifyPlayer
                    v-else
                    key="player"
                    :wrapped="wrapped"
                    :is-playing="yt.isPlaying.value"
                    :current-time="yt.currentTime.value"
                    :duration="yt.duration.value"
                    :has-audio="hasAudio"
                    @toggle="yt.toggle"
                    @seek="yt.seekToFraction"
                    @open-stories="showStories = true"
                    @back="screen = 'gift'"
                />
            </transition>

            <WrappedStories
                v-if="showStories"
                :wrapped="wrapped"
                @close="showStories = false"
            />
        </div>
    </div>
</template>

<style scoped>
.screen-enter-active,
.screen-leave-active {
    transition: opacity 0.4s ease;
}
.screen-enter-from,
.screen-leave-to {
    opacity: 0;
}
</style>
