@extends('layouts.public')
@section('content')
@php $pageTitle = 'Blog'; @endphp
<div class="mb-8">
    <h1 class="text-3xl font-bold text-stone-900">Blog</h1>
    <p class="text-stone-500 mt-2">Tips, berita, dan informasi seputar rental mobil.</p>
</div>

@if ($categories->isNotEmpty())
<div class="flex flex-wrap gap-2 mb-8">
    @foreach ($categories as $cat)
        <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700">{{ $cat->name }} ({{ $cat->published_posts_count }})</span>
    @endforeach
</div>
@endif

@if ($posts->isEmpty())
    <div class="text-center py-12 text-stone-400">Belum ada artikel.</div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($posts as $post)
            <a href="{{ route('blog.show', $post->slug) }}" class="bg-white rounded-xl border border-stone-200 overflow-hidden hover:shadow-lg transition group">
                @if ($post->featured_image)
                    <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-48 object-cover group-hover:scale-105 transition duration-300">
                @else
                    <div class="w-full h-48 bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-4xl text-white/30">&#128221;</div>
                @endif
                <div class="p-5">
                    <div class="flex items-center gap-2 mb-2">
                        @if ($post->category)
                            <span class="text-xs font-medium text-blue-600">{{ $post->category->name }}</span>
                        @endif
                        <span class="text-xs text-stone-400">{{ $post->published_at->format('d M Y') }}</span>
                    </div>
                    <h2 class="font-bold text-stone-900 mb-2 group-hover:text-blue-600 transition line-clamp-2">{{ $post->title }}</h2>
                    <p class="text-sm text-stone-500 line-clamp-2">{{ $post->excerpt }}</p>
                </div>
            </a>
        @endforeach
    </div>
    <div class="mt-8">{{ $posts->links() }}</div>
@endif
@endsection
