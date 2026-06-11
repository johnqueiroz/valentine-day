<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import GiftIntro from '@/Components/Public/GiftIntro.vue';
import SpotifyPlayer from '@/Components/Public/SpotifyPlayer.vue';
import WrappedStories from '@/Components/WrappedStories.vue';
import { useYouTubePlayer } from '@/useYouTubePlayer';
import { idFrom } from '@/lib/youtube';
import { moonForDate } from '@/lib/moon';
import { dominantColor } from '@/lib/dominantColor';
import data from '@/data.js';

// Cores dominantes (preenchidas em runtime a partir das imagens).
const colors = reactive({}); // src -> hex

// Monta o objeto `wrapped` que os componentes esperam, a partir do data.js.
const wrapped = computed(() => {
    const date = data.relationship_started_on;
    const days = date
        ? Math.max(0, Math.floor((Date.now() - new Date(date + 'T00:00:00').getTime()) / 86400000))
        : null;

    return {
        couple_name_1: data.couple_name_1,
        couple_name_2: data.couple_name_2,
        gifter_name: data.gifter_name,
        love_letter: data.love_letter,
        relationship_started_on: date,
        theme: data.theme || 'green',
        days_together: days,
        moon: date ? moonForDate(date) : null,
        slides: data.slides || [],
        tracks: (data.tracks || []).map((t, i) => ({
            id: i,
            title: t.title,
            artist: t.artist,
            youtube_id: idFrom(t.youtube_url),
            photo_url: t.photo || null,
            photo_color: t.photo ? colors[t.photo] : null,
        })),
        photos: (data.photos || []).map((p, i) => ({
            id: i,
            url: p.src,
            color: colors[p.src],
            caption: p.caption || null,
        })),
    };
});

const tracks = computed(() => wrapped.value.tracks);
const trackIndex = ref(0);
const screen = ref('gift');
const showStories = ref(false);
const hasAudio = computed(() => tracks.value.some((t) => t.youtube_id));

const yt = useYouTubePlayer();
const started = ref(false);

function openPresent() {
    // play() síncrono dentro do clique → autoplay com som permitido.
    if (hasAudio.value) {
        yt.play();
        started.value = true;
    }
    screen.value = 'player';
}
function changeTrack(i) {
    if (!tracks.value.length) return;
    trackIndex.value = (i + tracks.value.length) % tracks.value.length;
    const vid = tracks.value[trackIndex.value]?.youtube_id;
    if (vid && started.value) yt.load(vid);
}

// Cria o player do YouTube já no mount, pronto para tocar no primeiro toque.
onMounted(() => {
    if (hasAudio.value) yt.prepare('yt-player', tracks.value[0]?.youtube_id);
});

// Extrai as cores das imagens ao montar (fundo do player + galeria).
onMounted(() => {
    const srcs = new Set();
    (data.tracks || []).forEach((t) => t.photo && srcs.add(t.photo));
    (data.photos || []).forEach((p) => p.src && srcs.add(p.src));
    srcs.forEach(async (src) => {
        const hex = await dominantColor(src);
        if (hex) colors[src] = hex;
    });
});
</script>

<template>
    <div class="flex min-h-screen justify-center bg-black">
        <div class="relative h-screen w-full max-w-md overflow-hidden bg-[#121212]">
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
                    :tracks="tracks"
                    :track-index="trackIndex"
                    :is-playing="yt.isPlaying.value"
                    :current-time="yt.currentTime.value"
                    :duration="yt.duration.value"
                    :has-audio="hasAudio"
                    @toggle="yt.toggle"
                    @seek="yt.seekToFraction"
                    @change-track="changeTrack"
                    @open-stories="showStories = true"
                    @back="screen = 'gift'"
                />
            </transition>

            <WrappedStories v-if="showStories" :wrapped="wrapped" @close="showStories = false" />
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
