// Dados do seu Wrapped. Edite os campos do casal abaixo.
// Imagens (capa de cada faixa) e MP3 ficam em ./assets/, na ordem.
import track1 from './assets/track-1.jpeg';
import track2 from './assets/track-2.jpeg';
import track3 from './assets/track-3.jpeg';
import track4 from './assets/track-4.jpeg';
import track5 from './assets/track-5.jpeg';
import track6 from './assets/track-6.jpeg';
import audio1 from './assets/track-1.mp3';
import audio2 from './assets/track-2.mp3';
import audio3 from './assets/track-3.mp3';
import audio4 from './assets/track-4.mp3';
import audio5 from './assets/track-5.mp3';
import audio6 from './assets/track-6.mp3';

export default {
    // TODO: trocar pelos dados reais do casal.
    couple_name_1: 'John Emerson',
    couple_name_2: 'Nattália Reis',
    gifter_name: 'John',
    relationship_started_on: '2025-02-22', // AAAA-MM-DD
    theme: 'green', // green | blue | purple | pink
    love_letter:
        'Escreva aqui a cartinha do casal. Aparece abaixo do player. 💚',

    // Playlist do player — música + título + artista + foto de capa.
    tracks: [
        { title: 'Lembrei de Nós', artist: 'João Gomes', audio: audio1, photo: track1 },
        { title: 'Still Loving You', artist: 'Scorpions', audio: audio2, photo: track2 },
        { title: 'Um Amor Puro', artist: 'Djavan', audio: audio3, photo: track3 },
        { title: 'João e Maria', artist: 'Chico Buarque', audio: audio4, photo: track4 },
        { title: 'O Velho e a Flor', artist: 'Toquinho e Vinícius de Moraes', audio: audio5, photo: track5 },
        { title: 'Wonderwall', artist: 'Oasis', audio: audio6, photo: track6 },
    ],

    // Galeria da retrospectiva (stories) — reaproveita as 6 fotos.
    photos: [
        { src: track1, caption: 'São João' },
        { src: track2, caption: 'Nós dois' },
        { src: track3, caption: 'Luzes' },
        { src: track4, caption: 'Cavalgada' },
        { src: track5, caption: 'Vista da cidade' },
        { src: track6, caption: 'Praia' },
    ],

    // Slides extras da retrospectiva (opcional).
    slides: [
        { type: 'message', title: 'Pra você', body: 'Nossa trilha sonora, do nosso jeito. 💞', meta: {} },
    ],
};
