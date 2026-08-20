@extends('pseo._layout')

@section('content')
<section class="py-16 lg:py-24">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center gap-2 text-sm text-stone-400 mb-8">
            <a href="/" class="hover:text-brand-600 transition-colors">Beranda</a>
            <span>/</span>
            <a href="/blog" class="hover:text-brand-600 transition-colors">Blog</a>
            <span>/</span>
            <span class="text-stone-700">{{ $post->title ?? 'Artikel' }}</span>
        </nav>

        <div class="flex flex-col lg:flex-row gap-10">
            {{-- Article --}}
            <article class="flex-1">
                <div class="mb-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="px-2.5 py-0.5 bg-brand-50 text-brand-700 text-xs font-semibold rounded-md">{{ $post->category->name ?? $post['category'] ?? 'Artikel' }}</span>
                        <span class="text-sm text-stone-400">{{ $post->published_at?->format('d M Y') ?? $post['date'] ?? '' }}</span>
                    </div>
                    <h1 class="font-bold text-3xl lg:text-4xl text-stone-900 mb-4">{{ $post->title ?? 'Judul Artikel' }}</h1>
                    <div class="flex items-center gap-3 text-sm text-stone-500">
                        <span>Oleh {{ $post->author->name ?? $post['author'] ?? 'Admin' }}</span>
                        <span>&middot;</span>
                        <span>{{ $post->reading_time ?? '5 menit baca' }}</span>
                    </div>
                </div>

                <div class="aspect-[16/9] bg-gradient-to-br from-brand-100 to-brand-200 rounded-2xl flex items-center justify-center mb-8">
                    <span class="text-6xl">📝</span>
                </div>

                <div class="prose prose-stone prose-lg max-w-none">
                    {!! $post->content ?? '<p>Konten artikel sedang dalam proses penulisan.</p>' !!}
                </div>

                {{-- Tags --}}
                @if(isset($post->tags) && count($post->tags))
                <div class="flex flex-wrap gap-2 mt-8">
                    @foreach($post->tags as $tag)
                    <span class="px-3 py-1 bg-stone-100 text-stone-600 text-xs font-medium rounded-lg">#{{ is_array($tag) ? $tag['name'] ?? '' : $tag->name ?? '' }}</span>
                    @endforeach
                </div>
                @endif

                {{-- Share --}}
                <div class="border-t border-stone-200 mt-8 pt-8">
                    <h3 class="font-semibold text-stone-800 mb-3">Bagikan Artikel</h3>
                    <div class="flex gap-3">
                        <a href="https://wa.me/?text={{ urlencode(($post->title ?? '') . ' ' . url()->current()) }}" target="_blank" class="px-4 py-2 bg-emerald-500 text-white text-sm font-semibold rounded-lg hover:bg-emerald-600 transition-colors">WhatsApp</a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($post->title ?? '') }}" target="_blank" class="px-4 py-2 bg-sky-500 text-white text-sm font-semibold rounded-lg hover:bg-sky-600 transition-colors">Twitter</a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">Facebook</a>
                    </div>
                </div>
            </article>

            {{-- Sidebar --}}
            <aside class="w-full lg:w-72 shrink-0">
                <div class="bg-white border border-stone-200 rounded-2xl p-5 mb-6 sticky top-20">
                    <h3 class="font-bold text-stone-900 mb-3">Artikel Terkait</h3>
                    <ul class="space-y-4">
                        @foreach($relatedPosts ?? [] as $related)
                        <li>
                            <a href="/blog/{{ $related->slug ?? $related['slug'] ?? '#' }}" class="flex gap-3 group">
                                <div class="w-16 h-16 bg-stone-100 rounded-lg flex items-center justify-center shrink-0"><span class="text-xl">📄</span></div>
                                <div>
                                    <h4 class="text-sm font-semibold text-stone-800 group-hover:text-brand-600 transition-colors leading-snug">{{ $related->title ?? $related['title'] ?? '' }}</h4>
                                    <span class="text-xs text-stone-400">{{ $related->published_at?->format('d M Y') ?? $related['date'] ?? '' }}</span>
                                </div>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>

                <div class="bg-brand-600 rounded-2xl p-5 text-center text-white">
                    <div class="text-2xl mb-2">🚗</div>
                    <h3 class="font-bold text-sm mb-1">Sewa Mobil Sekarang</h3>
                    <p class="text-brand-100 text-xs mb-3">Temukan kendaraan terbaik</p>
                    <a href="/" class="inline-block px-4 py-2 bg-white text-brand-700 text-xs font-bold rounded-lg hover:bg-brand-50 transition-all">Browse Mobil</a>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection
