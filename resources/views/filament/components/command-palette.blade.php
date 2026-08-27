@php
    $resources = collect(filament()->getCurrentPanel()->getResources())
        ->filter(fn (string $resource) => $resource::canViewAny())
        ->map(fn (string $resource) => [
            'label' => $resource::getNavigationLabel(),
            'group' => (string) ($resource::getNavigationGroup() ?? 'Lainnya'),
            'url' => $resource::getUrl(),
            'icon' => $resource::getNavigationIcon() ?: 'heroicon-o-square-3-stack-3d',
        ])
        ->values();

    $pages = collect(filament()->getCurrentPanel()->getPages())
        ->filter(fn (string $page) => $page::canAccess())
        ->map(fn (string $page) => [
            'label' => $page::getNavigationLabel(),
            'group' => (string) ($page::getNavigationGroup() ?? 'Dashboard'),
            'url' => $page::getUrl(),
            'icon' => $page::getNavigationIcon() ?: 'heroicon-o-window',
        ]);

    // Hidden resources and secondary pages remain permission-aware and are
    // intentionally first-class citizens in the command palette.
    $tools = $resources->concat($pages)->unique('url')->sortBy(['group', 'label'])->values();
@endphp

<div
    class="rm-command"
    x-data="{
        open: false,
        query: '',
        favorites: JSON.parse(localStorage.getItem('rm-favorites') || '[]'),
        recent: JSON.parse(localStorage.getItem('rm-recent') || '[]'),
        tools: @js($tools),
        get filtered() {
            const q = this.query.trim().toLowerCase();
            const source = q ? this.tools : [...this.recent, ...this.tools];
            return source.filter((item, index, list) =>
                (!q || `${item.label} ${item.group}`.toLowerCase().includes(q)) &&
                list.findIndex(candidate => candidate.url === item.url) === index
            ).slice(0, 14);
        },
        isFavorite(url) { return this.favorites.some(item => item.url === url) },
        toggleFavorite(item) {
            this.favorites = this.isFavorite(item.url)
                ? this.favorites.filter(saved => saved.url !== item.url)
                : [item, ...this.favorites].slice(0, 12);
            localStorage.setItem('rm-favorites', JSON.stringify(this.favorites));
        },
        visit(item) {
            this.recent = [item, ...this.recent.filter(saved => saved.url !== item.url)].slice(0, 8);
            localStorage.setItem('rm-recent', JSON.stringify(this.recent));
            window.location.href = item.url;
        }
    }"
    x-on:keydown.window.prevent.ctrl.k="open = true; $nextTick(() => $refs.search.focus())"
    x-on:keydown.window.prevent.meta.k="open = true; $nextTick(() => $refs.search.focus())"
    x-on:keydown.escape.window="open = false"
>
    <button type="button" class="rm-command-trigger" x-on:click="open = true; $nextTick(() => $refs.search.focus())">
        <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-4 w-4" />
        <span>Cari fitur, laporan, atau pengaturan</span><kbd>Ctrl K</kbd>
    </button>

    <template x-teleport="body">
        <div x-show="open" x-cloak class="rm-command-overlay" x-transition.opacity x-on:click.self="open = false">
            <section class="rm-command-dialog" role="dialog" aria-modal="true" aria-label="Pusat perintah">
                <header>
                    <x-filament::icon icon="heroicon-o-command-line" class="h-5 w-5" />
                    <input x-ref="search" x-model="query" type="search" placeholder="Ketik nama fitur, laporan, atau master data…">
                    <button type="button" x-on:click="open = false">Esc</button>
                </header>
                <div class="rm-command-list">
                    <p x-show="!query && favorites.length" class="rm-command-caption">Favorit tersimpan di perangkat ini</p>
                    <template x-for="item in (query ? filtered : [...favorites, ...filtered].filter((v,i,a) => a.findIndex(x => x.url === v.url) === i).slice(0,14))" :key="item.url">
                        <div class="rm-command-item">
                            <button type="button" class="rm-command-open" x-on:click="visit(item)">
                                <span class="rm-command-icon" x-html="item.icon.includes('heroicon') ? '⌁' : '•'"></span>
                                <span><strong x-text="item.label"></strong><small x-text="item.group"></small></span>
                            </button>
                            <button type="button" class="rm-command-star" x-on:click="toggleFavorite(item)" :aria-label="isFavorite(item.url) ? 'Hapus favorit' : 'Tambah favorit'" x-text="isFavorite(item.url) ? '★' : '☆'"></button>
                        </div>
                    </template>
                    <div x-show="filtered.length === 0" class="rm-command-empty">Tidak ada fitur yang cocok dengan pencarian.</div>
                </div>
                <footer><span>↑↓ navigasi</span><span>Enter buka</span><span>★ favorit</span></footer>
            </section>
        </div>
    </template>
</div>
