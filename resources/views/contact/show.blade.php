@extends('portal.layout')
@section('content')
@php $pageTitle = 'Kontak Kami'; @endphp

<div class="max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold text-stone-900 mb-2">Kontak Kami</h1>
    <p class="text-stone-500 mb-8">Hubungi kami untuk pertanyaan, saran, atau pemesanan khusus.</p>

    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-sm mb-6">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm mb-6">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('contact.store') }}" class="bg-white rounded-xl border border-stone-200 p-6 space-y-4">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full px-3 py-2.5 rounded-lg border border-stone-300 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full px-3 py-2.5 rounded-lg border border-stone-300 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-stone-700 mb-1">Telepon (Opsional)</label>
            <input type="text" name="phone" value="{{ old('phone') }}"
                   class="w-full px-3 py-2.5 rounded-lg border border-stone-300 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-stone-700 mb-1">Subjek</label>
            <input type="text" name="subject" value="{{ old('subject') }}" required
                   class="w-full px-3 py-2.5 rounded-lg border border-stone-300 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-stone-700 mb-1">Pesan</label>
            <textarea name="message" rows="5" required
                      class="w-full px-3 py-2.5 rounded-lg border border-stone-300 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">{{ old('message') }}</textarea>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 transition shadow-sm">Kirim Pesan</button>
    </form>
</div>
@endsection
