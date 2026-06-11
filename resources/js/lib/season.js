// Estação do ano no hemisfério Sul (Brasil), pelo mês da data.
const SEASONS = {
    verao: { name: 'Verão', icon: '☀️', color: '#fbbf24' },
    outono: { name: 'Outono', icon: '🍂', color: '#d97706' },
    inverno: { name: 'Inverno', icon: '❄️', color: '#60a5fa' },
    primavera: { name: 'Primavera', icon: '🌸', color: '#f472b6' },
};

export function seasonForDate(date) {
    const d = date instanceof Date ? date : new Date(date + 'T00:00:00');
    const m = d.getMonth() + 1; // 1..12
    if (m === 12 || m <= 2) return SEASONS.verao;
    if (m <= 5) return SEASONS.outono;
    if (m <= 8) return SEASONS.inverno;
    return SEASONS.primavera;
}
