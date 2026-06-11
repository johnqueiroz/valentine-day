// Astronomia de baixa precisão (sem libs) para projetar o céu de uma data/local.
// Precisão ~0.5–1°: fiel visualmente, não nível efeméride.

const DEG = Math.PI / 180;
const RAD = 180 / Math.PI;

// Dia juliano a partir de um Date (usa o tempo UTC interno).
export function julianDate(date) {
    return date.getTime() / 86400000 + 2440587.5;
}

// Greenwich Mean Sidereal Time em graus (0..360).
export function gmstDeg(jd) {
    const d = jd - 2451545.0;
    let gmst = 280.46061837 + 360.98564736629 * d;
    gmst = ((gmst % 360) + 360) % 360;
    return gmst;
}

// Local Sidereal Time em graus. lngEast = longitude (leste positivo).
export function lstDeg(jd, lngEast) {
    return (((gmstDeg(jd) + lngEast) % 360) + 360) % 360;
}

// Equatorial (RA/Dec graus) -> horizontal (alt/az graus). az: 0=N, 90=E.
export function eqToHorizontal(raDeg, decDeg, latDeg, lst) {
    const ha = (lst - raDeg) * DEG; // ângulo horário
    const dec = decDeg * DEG;
    const lat = latDeg * DEG;

    const sinAlt = Math.sin(dec) * Math.sin(lat) + Math.cos(dec) * Math.cos(lat) * Math.cos(ha);
    const alt = Math.asin(Math.max(-1, Math.min(1, sinAlt)));

    let az = Math.atan2(
        Math.sin(ha),
        Math.cos(ha) * Math.sin(lat) - Math.tan(dec) * Math.cos(lat)
    );
    az = az + Math.PI; // referência ao Norte
    az = ((az % (2 * Math.PI)) + 2 * Math.PI) % (2 * Math.PI);

    return { alt: alt * RAD, az: az * RAD };
}

// Posição da Lua (RA/Dec graus) — fórmula de baixa precisão (Meeus, cap. 47 reduzido).
export function moonEquatorial(date) {
    const jd = julianDate(date);
    const T = (jd - 2451545.0) / 36525;

    const Lp = 218.316 + 481267.8813 * T;            // longitude média
    const M = 357.5291 + 35999.0503 * T;             // anomalia do Sol
    const Mp = 134.963 + 477198.8676 * T;            // anomalia da Lua
    const D = 297.8502 + 445267.1115 * T;            // elongação
    const F = 93.272 + 483202.0175 * T;              // argumento da latitude

    const lambda =
        Lp +
        6.289 * Math.sin(Mp * DEG) +
        1.274 * Math.sin((2 * D - Mp) * DEG) +
        0.658 * Math.sin(2 * D * DEG) +
        0.214 * Math.sin(2 * Mp * DEG) -
        0.186 * Math.sin(M * DEG) -
        0.114 * Math.sin(2 * F * DEG);

    const beta =
        5.128 * Math.sin(F * DEG) +
        0.281 * Math.sin((Mp + F) * DEG) +
        0.278 * Math.sin((Mp - F) * DEG) +
        0.173 * Math.sin((2 * D - F) * DEG);

    const eps = 23.439 - 0.0000004 * (jd - 2451545.0); // obliquidade
    const l = lambda * DEG;
    const b = beta * DEG;
    const e = eps * DEG;

    const ra = Math.atan2(
        Math.sin(l) * Math.cos(e) - Math.tan(b) * Math.sin(e),
        Math.cos(l)
    );
    const dec = Math.asin(Math.sin(b) * Math.cos(e) + Math.cos(b) * Math.sin(e) * Math.sin(l));

    return {
        ra: (((ra * RAD) % 360) + 360) % 360,
        dec: dec * RAD,
    };
}

// Índice B-V -> cor aproximada da estrela (azul quente -> vermelho frio).
export function bvToColor(bv) {
    const v = typeof bv === 'string' ? parseFloat(bv) : bv;
    if (isNaN(v)) return '#fff';
    if (v < -0.2) return '#9bb0ff';
    if (v < 0.0) return '#aabfff';
    if (v < 0.3) return '#cad7ff';
    if (v < 0.6) return '#f8f7ff';
    if (v < 0.8) return '#fff4ea';
    if (v < 1.0) return '#ffd2a1';
    if (v < 1.4) return '#ffb56c';
    return '#ff9b6b';
}
