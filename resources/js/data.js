// Dados do seu Wrapped. Edite os campos do casal abaixo.
// Imagens (capa de cada faixa) e MP3 ficam em ./assets/, na ordem.
import track1 from './assets/track-1.jpeg';
import track2 from './assets/track-2.jpeg';
import track3 from './assets/track-3.jpeg';
import track4 from './assets/track-4.jpeg';
import track5 from './assets/track-5.jpeg';
import track6 from './assets/track-6.jpeg';
import track7 from './assets/track-7.jpeg';
import track8 from './assets/track-8.jpeg';
import track9 from './assets/track-9.jpeg';
import track10 from './assets/track-10.jpeg';
import track11 from './assets/track-11.jpeg';
import track12 from './assets/track-12.jpeg';
import track13 from './assets/track-13.jpeg';
import track14 from './assets/track-14.jpeg';
import track15 from './assets/track-15.jpeg';
import track16 from './assets/track-16.jpeg';
import track17 from './assets/track-17.jpeg';
import track18 from './assets/track-18.jpeg';
import track19 from './assets/track-19.jpeg';
import track20 from './assets/track-20.jpeg';
import track21 from './assets/track-21.jpeg';
import track22 from './assets/track-22.jpeg';
import track23 from './assets/track-23.jpeg';
import track24 from './assets/track-24.jpeg';
import track25 from './assets/track-25.jpeg';
import track26 from './assets/track-26.jpeg';
import track27 from './assets/track-27.jpeg';
import track28 from './assets/track-28.jpeg';
import track29 from './assets/track-29.jpeg';
import track30 from './assets/track-30.jpeg';
import track31 from './assets/track-31.jpeg';
import track32 from './assets/track-32.jpeg';
import audio1 from './assets/track-1.mp3';
import audio2 from './assets/track-2.mp3';
import audio3 from './assets/track-3.mp3';
import audio4 from './assets/track-4.mp3';
import audio5 from './assets/track-5.mp3';
import audio6 from './assets/track-6.mp3';
import couplePhoto from './assets/couple.jpeg';

export default {
    // TODO: trocar pelos dados reais do casal.
    couple_name_1: 'John',
    couple_name_2: 'Nattália',
    gifter_name: 'John',
    relationship_started_on: '2025-02-22', // AAAA-MM-DD
    couple_photo: couplePhoto, // foto fixa do casal (card abaixo do player)
    // Céu real da noite em que começaram a namorar (cena da retrospectiva).
    star_map: { city: 'Martins - RN', lat: -6.09, lng: -37.91, datetime: '2025-02-22T21:00:00-03:00' },
    theme: 'green', // green | blue | purple | pink
    love_letter:
        'E pensar que, depois de tudo, a vida nos deu a chance de nos reencontrarmos, e que todo aquele amor continuava vivo dentro de nós. Olha só para nós agora: escrevendo nossa própria história, construindo sonhos juntos e vivendo um amor que só cresce a cada dia.\n\n' +
        'Minha vida ao seu lado é muito mais bonita, cheia de carinho, companheirismo, aventuras e momentos que guardarei para sempre no coração. Fico imaginando tudo o que ainda nos espera: as viagens que faremos, as risadas que compartilharemos, os filmes que assistiremos abraçados, os hambúrgueres que vamos dividir e todas as memórias que ainda criaremos juntos.\n\n' +
        'Só quero que você saiba o quanto sou feliz por ter você na minha vida e o quanto tenho orgulho de te chamar de meu amor. ❤️',

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

    // Mini-jogos (Termo/Wordle) na retrospectiva. `answers` rotaciona a cada acerto
    // (após a última, volta à primeira). Acentos são removidos automaticamente.
    games: [
        {
            question: 'O que mais gosto em você',
            message: 'Te amo, minha gata!',
            answers: ['sorriso', 'olhos', 'bochecha', 'cabelo', 'persistência', 'inteligência'],
        },
    ],

    // Roletas de prêmio na retrospectiva (após os jogos).
    wheels: [
        {
            title: 'Seu presente será:',
            options: [
                'Jantar especial',
                'Perfume',
                'Foto + moldura',
                'Garrafa Térmico',
                'Bolsa',
                'Sapato',
                'Colar/pulseira',
                'Vixe, vou ter que escolher',
                'Eba! Escolha, John!!',
            ],
        },
    ],

    // Destaques (estilo "stories"): card "Conheça ...". Capa = photos[0].
    // Troque/adicione as imagens depois (import no topo + referência aqui).
    highlights: [
        { title: 'Nossos Dates', photos: [track7, track8, track9, track10, track11, track12, track13, track14, track15, track16] },
        { title: 'Fotos aleatórias', photos: [track17, track18, track19, track20, track21] },
        { title: 'Primeira viagem', photos: [track22, track23, track24, track25, track26, track27, track28, track29, track30, track31, track32] },
    ],
};
