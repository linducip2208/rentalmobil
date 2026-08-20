@extends('layouts.public')
@section('content')
@php $pageTitle = $post->meta_title ?? $post->title; @endphp

<article class="max-w-3xl mx-auto">
    <a href="{{ route('blog.index') }}" class="text-sm text-blue-600 hover:underline inline-flex items-center gap-1 mb-4">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Kembali ke Blog
    </a>

    @if ($post->category)
        <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full">{{ $post->category->name }}</span>
    @endif

    <h1 class="text-3xl font-bold text-stone-900 mt-3 mb-4">{{ $post->title }}</h1>

    <div class="flex items-center gap-3 text-sm text-stone-500 mb-6">
        <span>{{ $post->published_at->format('d M Y') }}</span>
        <span>&middot;</span>
        <span>{{ number_format($post->views_count) }} views</span>
    </div>

    @if ($post->featured_image)
        <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full rounded-xl mb-8">
    @endif

    <div class="prose prose-stone max-w-none">
        {!! $post->content !!}
    </div>

    @if ($related->isNotEmpty())
    <div class="mt-12 pt-8 border-t border-stone-200">
        <h2 class="font-bold text-stone-900 mb-4">Artikel Terkait</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @foreach ($related as $rel)
                <a href="{{ route('blog.show', $rel->slug) }}" class="block p-4 rounded-lg border border-stone-200 hover:bg-stone-50 transition">
                    <h3 class="text-sm font-medium text-stone-900 line-clamp-2">{{ $rel->title }}</h3>
                    <p class="text-xs text-stone-500 mt-1">{{ $rel->published_at->format('d M Y') }}</p>
                </a>
            @endforeach
        </div>
    </div>
    @endif
</article>
@endsection
