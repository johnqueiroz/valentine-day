// Cor dominante (média) de uma imagem, via canvas 1x1. Runtime, sem build step.
// Imagens locais (mesma origem) → canvas legível.
export function dominantColor(src) {
    return new Promise((resolve) => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => {
            try {
                const canvas = document.createElement('canvas');
                canvas.width = 1;
                canvas.height = 1;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, 1, 1);
                const [r, g, b] = ctx.getImageData(0, 0, 1, 1).data;
                const hex = '#' + [r, g, b].map((c) => c.toString(16).padStart(2, '0')).join('');
                resolve(hex);
            } catch {
                resolve(null); // canvas "tainted" (cross-origin) → fallback do tema
            }
        };
        img.onerror = () => resolve(null);
        img.src = src;
    });
}
