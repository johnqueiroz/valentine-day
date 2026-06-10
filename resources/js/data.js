// Dados do seu Wrapped (edite à mão). Para imagens, descomente os imports e
// aponte `photo`/`src` para os arquivos em ./assets/.
//
// import cover1 from './assets/musica1.jpg';
// import foto1 from './assets/foto1.jpg';

export default {
    couple_name_1: 'Ana',
    couple_name_2: 'Beto',
    gifter_name: 'Leonardo',
    relationship_started_on: '2022-02-14', // YYYY-MM-DD
    theme: 'green', // green | blue | purple | pink
    love_letter:
        'Desde o dia em que te conheci, tudo ficou mais colorido.\n\nObrigado por cada risada, cada viagem e cada abraço. Eu te amo. 💚',

    // Playlist do player. Cada faixa: música (YouTube) + título + artista + foto.
    tracks: [
        { title: 'Still Loving You', artist: 'Panda', youtube_url: 'https://www.youtube.com/watch?v=jdYJf_ybyVo', photo: null },
        { title: 'Nosso Talismã', artist: 'Anavitória', youtube_url: 'https://www.youtube.com/watch?v=2Vv-BfVoq4g', photo: null },
    ],

    // Galeria da retrospectiva (stories). Ex.: { src: foto1, caption: 'Praia' }
    photos: [],

    // Slides extras da retrospectiva (opcional).
    slides: [
        { type: 'stat', title: 'dias de nós dois', body: 'Cada um valeu a pena.', meta: { value: '847', unit: 'dias' } },
        { type: 'message', title: 'Eu te amo', body: 'Que venham muitos outros. 💞', meta: {} },
    ],
};
