// Extrai o ID de 11 caracteres de um link do YouTube (vários formatos).
export function idFrom(url) {
    if (!url) return null;
    const patterns = [
        /youtu\.be\/([\w-]{11})/i,
        /[?&]v=([\w-]{11})/i,
        /\/embed\/([\w-]{11})/i,
        /\/shorts\/([\w-]{11})/i,
    ];
    for (const re of patterns) {
        const m = url.match(re);
        if (m) return m[1];
    }
    if (/^[\w-]{11}$/.test(url.trim())) return url.trim();
    return null;
}
