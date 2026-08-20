@extends('pseo._layout')

@section('content')
<section class="py-16 lg:py-24">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="font-bold text-4xl lg:text-5xl text-stone-900 mb-4">Alternatif {{ $vehicleName ?? 'Kendaraan' }}</h1>
        <p class="text-lg text-stone-500 mb-12">Jika Anda mencari kendaraan serupa, berikut beberapa alternatif yang layak dipertimbangkan</p>

        <div class="prose prose-stone prose-lg max-w-none mb-12">
            <p>{{ $intro ?? "Memilih kendaraan rental yang tepat bisa jadi tantangan. {$vehicleName ?? 'Kendaraan'} mungkin menjadi pilihan populer, namun ada beberapa alternatif yang menawarkan fitur serupa dengan harga atau keunggulan berbeda. Berikut daftar kendaraan yang bisa menjadi pertimbangan Anda." }}</p>
        </div>

        <div class="space-y-6 mb-12">
            @forelse($alternatives ?? [] as $alt)
            <a href="/sewa/{{ $alt->slug ?? $alt['slug'] ?? '#' }}" class="flex flex-col sm:flex-row items-start gap-5 bg-white border border-stone-200 rounded-2xl p-5 card-lift block hover:border-brand-200 transition-colors">
                <div class="w-full sm:w-40 aspect-video sm:aspect-square bg-gradient-to-br from-brand-50 to-brand-100 rounded-xl flex items-center justify-center shrink-0">
                    <span class="text-4xl">🚗</span>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-lg text-stone-900 mb-1">{{ $alt->name ?? $alt['name'] ?? 'Kendaraan' }}</h3>
                    <p class="text-sm text-stone-500 mb-2">{{ $alt->category ?? $alt['category'] ?? '—' }} &middot; {{ $alt->transmission ?? $alt['transmission'] ?? 'Automatic' }} &middot; {{ $alt->seats ?? $alt['seats'] ?? '5' }} kursi</p>
                    <p class="text-sm text-stone-600 mb-3">{{ $alt->description ?? $alt['description'] ?? 'Kendaraan alternatif dengan kondisi terawat dan asuransi all-risk.' }}</p>
                    <div class="flex items-center gap-4">
                        <span class="font-bold text-brand-600">Rp {{ number_format($alt->price_per_day ?? $alt['price_per_day'] ?? 0, 0, ',', '.') }}/hari</span>
                        <span class="text-xs text-stone-400">⭐ {{ $alt->rating ?? $alt['rating'] ?? '4.5' }}</span>
                    </div>
                </div>
            </a>
            @empty
            <div class="text-center py-12 text-stone-400">
                <div class="text-4xl mb-3">🔍</div>
                <p>Alternatif sedang dalam proses update. Silakan hubungi kami untuk rekomendasi.</p>
            </div>
            @endforelse
        </div>

        <div class="bg-brand-600 rounded-2xl p-8 text-center text-white">
            <h3 class="font-bold text-2xl mb-2">Butuh Rekomendasi?</h3>
            <p class="text-brand-100 mb-6">Tim kami siap membantu Anda memilih kendaraan terbaik</p>
            <a href="/contact" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-brand-700 font-bold rounded-xl hover:bg-brand-50 transition-all">Hubungi Kami →</a>
        </div>
    </div>
</section>
@endsection
