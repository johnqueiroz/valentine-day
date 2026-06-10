<script setup>
// Barra inferior decorativa, imitando a navegação do app do Spotify.
const items = [
    { label: 'Início', icon: 'M12 3 2 12h3v8h6v-6h2v6h6v-8h3z' },
    { label: 'Buscar', icon: 'M10 4a6 6 0 1 0 3.7 10.7l4.3 4.3 1.4-1.4-4.3-4.3A6 6 0 0 0 10 4m0 2a4 4 0 1 1 0 8 4 4 0 0 1 0-8' },
    { label: 'Sua biblioteca', icon: 'M4 4h2v16H4zM8 4h2v16H8zM13 4l5 1-3 15-5-1z' },
];
</script>

<template>
    <nav class="pointer-events-none flex items-center justify-around border-t border-white/5 bg-black/40 px-6 py-3 text-[11px] text-white/50 backdrop-blur">
        <div v-for="item in items" :key="item.label" class="flex flex-col items-center gap-1">
            <svg viewBox="0 0 24 24" class="h-5 w-5 fill-current">
                <path :d="item.icon" />
            </svg>
            <span>{{ item.label }}</span>
        </div>
    </nav>
</template>
