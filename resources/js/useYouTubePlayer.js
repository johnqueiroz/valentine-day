import { onBeforeUnmount, ref } from 'vue';

// Carrega a IFrame Player API do YouTube uma única vez (promise compartilhada).
let apiPromise = null;
function loadYouTubeApi() {
    if (apiPromise) return apiPromise;

    apiPromise = new Promise((resolve) => {
        if (window.YT && window.YT.Player) {
            resolve(window.YT);
            return;
        }
        const prev = window.onYouTubeIframeAPIReady;
        window.onYouTubeIframeAPIReady = () => {
            prev?.();
            resolve(window.YT);
        };
        const tag = document.createElement('script');
        tag.src = 'https://www.youtube.com/iframe_api';
        document.head.appendChild(tag);
    });

    return apiPromise;
}

/**
 * Controla a reprodução de um vídeo do YouTube como se fosse um player de áudio.
 * O elemento `elementId` deve existir no DOM (um div oculto).
 */
export function useYouTubePlayer() {
    const isReady = ref(false);
    const isPlaying = ref(false);
    const currentTime = ref(0);
    const duration = ref(0);

    let player = null;
    let poll = null;
    let wantPlay = false; // usuário pediu play antes do player ficar pronto

    function startPolling() {
        stopPolling();
        poll = setInterval(() => {
            if (player && player.getCurrentTime) {
                currentTime.value = player.getCurrentTime() || 0;
                duration.value = player.getDuration() || 0;
            }
        }, 250);
    }
    function stopPolling() {
        if (poll) clearInterval(poll);
        poll = null;
    }

    // Cria o player (sem autoplay) o quanto antes, para estar pronto no gesto do usuário.
    async function prepare(elementId, videoId) {
        if (!videoId || player) return;
        const YT = await loadYouTubeApi();
        if (player) return; // outra chamada já criou

        player = new YT.Player(elementId, {
            videoId,
            playerVars: { autoplay: 0, controls: 0, playsinline: 1, rel: 0 },
            events: {
                onReady: (e) => {
                    isReady.value = true;
                    duration.value = e.target.getDuration() || 0;
                    startPolling();
                    if (wantPlay) {
                        wantPlay = false;
                        e.target.playVideo();
                    }
                },
                onStateChange: (e) => {
                    // 1 = tocando, 2 = pausado, 0 = terminou
                    isPlaying.value = e.data === YT.PlayerState.PLAYING;
                    if (e.data === YT.PlayerState.ENDED) {
                        player.seekTo(0);
                        player.playVideo();
                    }
                },
            },
        });
    }

    // Inicia a reprodução — deve ser chamado DENTRO de um gesto do usuário.
    function play() {
        if (player && player.playVideo) player.playVideo();
        else wantPlay = true; // toca assim que o player ficar pronto
    }

    function toggle() {
        if (!player) return;
        if (isPlaying.value) player.pauseVideo();
        else player.playVideo();
    }

    // Troca a faixa (mesmo player). loadVideoById já inicia a reprodução.
    function load(videoId, elementId = 'yt-player') {
        if (!videoId) return;
        if (player && player.loadVideoById) {
            player.loadVideoById(videoId);
        } else {
            wantPlay = true;
            prepare(elementId, videoId);
        }
    }

    function seekToFraction(fraction) {
        if (player && duration.value) {
            player.seekTo(fraction * duration.value, true);
        }
    }

    onBeforeUnmount(() => {
        stopPolling();
        if (player && player.destroy) player.destroy();
    });

    return { isReady, isPlaying, currentTime, duration, prepare, play, load, toggle, seekToFraction };
}
