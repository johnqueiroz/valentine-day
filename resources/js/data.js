// Dados do seu Wrapped. Edite os campos do casal abaixo.
// As imagens ficam em ./assets/ (capa de cada faixa, na ordem).
import track1 from './assets/track-1.jpeg';
import track2 from './assets/track-2.jpeg';
import track3 from './assets/track-3.jpeg';
import track4 from './assets/track-4.jpeg';
import track5 from './assets/track-5.jpeg';
import track6 from './assets/track-6.jpeg';

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
        { title: 'Lembrei de Nós', artist: 'João Gomes', youtube_url: 'https://www.youtube.com/watch?v=E90LDzNOjfE', photo: track1 },
        { title: 'Still Loving You', artist: 'Scorpions', youtube_url: 'https://www.youtube.com/watch?v=7pOr3dBFAeY', photo: track2 },
        { title: 'Um Amor Puro', artist: 'Djavan', youtube_url: 'https://www.youtube.com/watch?v=Af7ieNv0wXY', photo: track3 },
        { title: 'João e Maria', artist: 'Chico Buarque', youtube_url: 'https://www.youtube.com/watch?v=Fxu-pE74m5A', photo: track4 },
        { title: 'O Velho e a Flor', artist: 'Toquinho e Vinícius de Moraes', youtube_url: 'https://www.youtube.com/watch?v=qzg4ID1IMl0', photo: track5 },
        { title: 'Wonderwall', artist: 'Oasis', youtube_url: 'https://www.youtube.com/watch?v=NbSzTi0d6pQ', photo: track6 },
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
