// Fase da lua para uma data (port de App\Support\MoonPhase). Sem API.
const SYNODIC = 29.53058867; // mês sinódico (dias)
const REF_NEW_MOON = Date.UTC(2000, 0, 6, 18, 14, 0); // lua nova de referência

const LABELS = [
    ['Lua nova', '🌑'],
    ['Crescente côncava', '🌒'],
    ['Quarto crescente', '🌓'],
    ['Crescente gibosa', '🌔'],
    ['Lua cheia', '🌕'],
    ['Minguante gibosa', '🌖'],
    ['Quarto minguante', '🌗'],
    ['Minguante côncava', '🌘'],
];

export function moonForDate(date) {
    const d = date instanceof Date ? date : new Date(date);
    const daysSince = (d.getTime() - REF_NEW_MOON) / 86400000;

    let age = daysSince % SYNODIC;
    if (age < 0) age += SYNODIC;

    const fraction = age / SYNODIC; // 0..1
    const illumination = (1 - Math.cos(2 * Math.PI * fraction)) / 2;
    const index = Math.floor(fraction * 8 + 0.5) % 8;
    const [name, emoji] = LABELS[index];

    return {
        name,
        emoji,
        illumination: Math.round(illumination * 100) / 100,
        age_days: Math.round(age * 10) / 10,
    };
}
