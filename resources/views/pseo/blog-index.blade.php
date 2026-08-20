@extends('pseo._layout')

@section('content')
<section class="py-16 lg:py-24">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-12">
            <h1 class="font-bold text-4xl lg:text-5xl text-stone-900 mb-4">Blog RentalMobil</h1>
            <p class="text-lg text-stone-500">Tips, panduan, dan berita terbaru seputar rental mobil di Indonesia</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-10">
            {{-- Main Content --}}
            <div class="flex-1">
                {{-- Featured Post --}}
                @if(isset($featuredPost) && $featuredPost)
                <a href="/blog/{{ $featuredPost->slug ?? $featuredPost['slug'] ?? '#' }}" class="block bg-white border border-stone-200 rounded-2xl overflow-hidden card-lift mb-8">
                    <div class="aspect-[16/9] bg-gradient-to-br from-brand-100 to-brand-200 flex items-center justify-center">
                        <span class="text-6xl">📝</span>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="px-2.5 py-0.5 bg-brand-50 text-brand-700 text-xs font-semibold rounded-md">{{ $featuredPost->category->name ?? $featuredPost['category'] ?? 'Artikel' }}</span>
                            <span class="text-xs text-stone-400">{{ $featuredPost->published_at?->format('d M Y') ?? $featuredPost['date'] ?? '' }}</span>
                        </div>
                        <h2 class="font-bold text-xl text-stone-900 mb-2">{{ $featuredPost->title ?? $featuredPost['title'] ?? '' }}</h2>
                        <p class="text-stone-600 text-sm leading-relaxed">{{ $featuredPost->excerpt ?? $featuredPost['excerpt'] ?? '' }}</p>
                    </div>
                </a>
                @endif

                {{-- Post List --}}
                <div class="space-y-6">
                    @forelse($posts ?? [] as $post)
                    <a href="/blog/{{ $post->slug ?? $post['slug'] ?? '#' }}" class="flex flex-col sm:flex-row gap-5 bg-white border border-stone-200 rounded-2xl p-5 card-lift block hover:border-brand-200 transition-colors">
                        <div class="w-full sm:w-48 aspect-video sm:aspect-square bg-gradient-to-br from-stone-100 to-stone-200 rounded-xl flex items-center justify-center shrink-0">
                            <span class="text-3xl">📄</span>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="px-2.5 py-0.5 bg-brand-50 text-brand-700 text-xs font-semibold rounded-md">{{ $post->category->name ?? $post['category'] ?? 'Artikel' }}</span>
                                <span class="text-xs text-stone-400">{{ $post->published_at?->format('d M Y') ?? $post['date'] ?? '' }}</span>
                            </div>
                            <h3 class="font-bold text-stone-900 mb-1">{{ $post->title ?? $post['title'] ?? '' }}</h3>
                            <p class="text-sm text-stone-500 leading-relaxed">{{ $post->excerpt ?? $post['excerpt'] ?? '' }}</p>
                        </div>
                    </a>
                    @empty
                    <div class="text-center py-16 text-stone-400">
                        <div class="text-4xl mb-3">📝</div>
                        <p>Artikel blog sedang dalam proses penulisan. Silakan kembali lagi nanti.</p>
                    </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if(isset($posts) && $posts instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="mt-8">
                    {{ $posts->links() }}
                </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <aside class="w-full lg:w-72 shrink-0">
                {{-- Categories --}}
                <div class="bg-white border border-stone-200 rounded-2xl p-5 mb-6">
                    <h3 class="font-bold text-stone-900 mb-3">Kategori</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/blog" class="text-stone-600 hover:text-brand-600 transition-colors">Semua Artikel</a></li>
                        @foreach($categories ?? ['Tips & Trik', 'Wisata', 'Bisnis', 'Otomotif'] as $cat)
                        <li><a href="/blog/category/{{ strtolower(str_replace(' ', '-', is_array($cat) ? $cat['name'] ?? '' : $cat)) }}" class="text-stone-600 hover:text-brand-600 transition-colors">{{ is_array($cat) ? $cat['name'] ?? '' : $cat }}</a></li>
                        @endforeach
                    </ul>
                </div>

                {{-- Recent Posts --}}
                <div class="bg-white border border-stone-200 rounded-2xl p-5 mb-6">
                    <h3 class="font-bold text-stone-900 mb-3">Artikel Terbaru</h3>
                    <ul class="space-y-3">
                        @foreach(array_slice($posts ?? [], 0, 4) as $recent)
                        <li>
                            <a href="/blog/{{ $recent->slug ?? $recent['slug'] ?? '#' }}" class="text-sm text-stone-600 hover:text-brand-600 transition-colors leading-snug block">{{ $recent->title ?? $recent['title'] ?? '' }}</a>
                            <span class="text-xs text-stone-400">{{ $recent->published_at?->format('d M Y') ?? $recent['date'] ?? '' }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>

                {{-- CTA --}}
                <div class="bg-brand-600 rounded-2xl p-5 text-center text-white">
                    <div class="text-2xl mb-2">🚗</div>
                    <h3 class="font-bold text-sm mb-1">Sewa Mobil Sekarang</h3>
                    <p class="text-brand-100 text-xs mb-3">Booking online 24/7</p>
                    <a href="/" class="inline-block px-4 py-2 bg-white text-brand-700 text-xs font-bold rounded-lg hover:bg-brand-50 transition-all">Browse Mobil</a>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection
