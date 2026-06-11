import { onBeforeUnmount, ref } from 'vue';

/**
 * Player de áudio (MP3 estático) com a mesma superfície do antigo player de YouTube,
 * para encaixar sem mudar o SpotifyPlayer.vue.
 */
export function useAudioPlayer({ onEnded } = {}) {
    const isPlaying = ref(false);
    const currentTime = ref(0);
    const duration = ref(0);

    const audio = new Audio();
    audio.preload = 'auto';

    audio.addEventListener('loadedmetadata', () => { duration.value = audio.duration || 0; });
    audio.addEventListener('timeupdate', () => { currentTime.value = audio.currentTime || 0; });
    audio.addEventListener('play', () => { isPlaying.value = true; });
    audio.addEventListener('pause', () => { isPlaying.value = false; });
    audio.addEventListener('ended', () => { onEnded?.(); });

    // Carrega a primeira faixa sem tocar (pronto para o gesto do usuário).
    function prepare(src) {
        if (src) audio.src = src;
    }

    // Inicia a reprodução — chamar DENTRO de um gesto do usuário (autoplay com som).
    function play() {
        audio.play().catch(() => {});
    }

    // Troca a faixa e já toca (usado no ⏮/⏭, dentro do clique).
    function load(src) {
        if (!src) return;
        audio.src = src;
        currentTime.value = 0;
        audio.play().catch(() => {});
    }

    function toggle() {
        if (isPlaying.value) audio.pause();
        else audio.play().catch(() => {});
    }

    function seekToFraction(fraction) {
        if (duration.value) audio.currentTime = fraction * duration.value;
    }

    onBeforeUnmount(() => {
        audio.pause();
        audio.src = '';
    });

    return { isPlaying, currentTime, duration, prepare, play, load, toggle, seekToFraction };
}
