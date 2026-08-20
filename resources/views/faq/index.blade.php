@extends('layouts.public')
@section('content')
@php $pageTitle = 'FAQ'; @endphp

<div class="max-w-3xl mx-auto">
    <h1 class="text-3xl font-bold text-stone-900 mb-2">Pertanyaan yang Sering Diajukan</h1>
    <p class="text-stone-500 mb-8">Temukan jawaban atas pertanyaan umum seputar layanan rental mobil kami.</p>

    @if ($faqs->isEmpty())
        <div class="text-center py-12 text-stone-400">Belum ada FAQ tersedia.</div>
    @else
        @foreach ($faqs as $category => $items)
            <div class="mb-8" x-data="{ open: false }">
                <h2 class="text-lg font-bold text-stone-900 mb-4 capitalize">{{ $category ?: 'Umum' }}</h2>
                <div class="space-y-3">
                    @foreach ($items as $faq)
                        <div class="bg-white rounded-xl border border-stone-200 overflow-hidden" x-data="{ show: false }">
                            <button @click="show = !show" class="w-full flex items-center justify-between p-4 text-left">
                                <span class="text-sm font-medium text-stone-900 pr-4">{{ $faq->question }}</span>
                                <svg class="w-5 h-5 text-stone-400 shrink-0 transition-transform duration-200" :class="show ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                </svg>
                            </button>
                            <div x-show="show" x-collapse x-cloak class="px-4 pb-4 text-sm text-stone-600 leading-relaxed">
                                {!! $faq->answer !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
