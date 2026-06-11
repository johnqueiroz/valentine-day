// Skin Spotify: fundo escuro fixo + cor de acento por tema.
// O player usa um degradê do acento (no topo) até o fundo escuro.
export const themes = {
    green: { accent: '#1DB954', tint: '#1f3d2b' },
    blue: { accent: '#3b82f6', tint: '#1e2a4a' },
    purple: { accent: '#a855f7', tint: '#2e1f47' },
    pink: { accent: '#ec4899', tint: '#43203a' },
};

export const BASE = '#121212';

export function themeOf(key) {
    return themes[key] ?? themes.green;
}

// Degradê estilo "tocando agora": acento esmaecido descendo para o preto.
export function playerGradient(key) {
    const t = themeOf(key);
    return `linear-gradient(180deg, ${t.tint} 0%, #18181b 55%, ${BASE} 100%)`;
}

// Escurece uma cor hex por um fator (0..1).
function darken(hex, factor) {
    const n = hex.replace('#', '');
    const r = parseInt(n.slice(0, 2), 16);
    const g = parseInt(n.slice(2, 4), 16);
    const b = parseInt(n.slice(4, 6), 16);
    const d = (c) => Math.round(c * (1 - factor));
    return `rgb(${d(r)}, ${d(g)}, ${d(b)})`;
}

// Degradê do player a partir da cor dominante da capa (como o Spotify real).
// Cai para o degradê do tema quando não há cor.
export function coverGradient(hex, themeKey) {
    if (!hex) return playerGradient(themeKey);
    return `linear-gradient(180deg, ${darken(hex, 0.15)} 0%, ${darken(hex, 0.55)} 45%, ${BASE} 100%)`;
}

// Cor sólida do topo do gradiente — usada na status bar do iOS (theme-color).
export function coverTopColor(hex, themeKey) {
    return hex ? darken(hex, 0.15) : themeOf(themeKey).tint;
}
