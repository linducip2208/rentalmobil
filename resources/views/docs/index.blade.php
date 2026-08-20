@extends('portal.layout')
@section('content')
@php $pageTitle = 'Dokumentasi'; @endphp

<div class="max-w-4xl mx-auto">
    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-stone-900 mb-2">Dokumentasi</h1>
        <p class="text-stone-500">Panduan lengkap menggunakan layanan rental mobil kami.</p>
    </div>

    <div class="bg-white rounded-xl border border-stone-200 p-6 mb-8">
        <h2 class="font-bold text-stone-900 mb-3">Akun Demo</h2>
        <p class="text-sm text-stone-500 mb-4">Gunakan akun berikut untuk mencoba portal customer:</p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-stone-100 text-left text-xs font-semibold text-stone-500 uppercase">
                        <th class="pb-2">Role</th>
                        <th class="pb-2">Email</th>
                        <th class="pb-2">Password</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($demoAccounts as $account)
                        <tr class="border-b border-stone-50">
                            <td class="py-2 font-medium text-stone-900">{{ $account['role'] }}</td>
                            <td class="py-2 text-stone-600 font-mono text-xs">{{ $account['email'] }}</td>
                            <td class="py-2 text-stone-600 font-mono text-xs">{{ $account['password'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-8">
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <h2 class="font-bold text-stone-900 mb-4">Cara Booking Mobil</h2>
            <ol class="space-y-3 text-sm text-stone-600">
                <li class="flex gap-3">
                    <span class="shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">1</span>
                    <span>Masuk ke portal customer dengan email dan password Anda.</span>
                </li>
                <li class="flex gap-3">
                    <span class="shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">2</span>
                    <span>Klik tombol "Booking Baru" di dashboard atau sidebar.</span>
                </li>
                <li class="flex gap-3">
                    <span class="shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">3</span>
                    <span>Pilih kendaraan, tanggal, lokasi pengambilan, dan tipe rental.</span>
                </li>
                <li class="flex gap-3">
                    <span class="shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">4</span>
                    <span>Tambahkan addons jika diperlukan, lalu klik "Buat Booking".</span>
                </li>
                <li class="flex gap-3">
                    <span class="shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">5</span>
                    <span>Booking Anda akan diverifikasi oleh tim kami.</span>
                </li>
            </ol>
        </div>

        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <h2 class="font-bold text-stone-900 mb-4">Cara Pembayaran</h2>
            <ol class="space-y-3 text-sm text-stone-600">
                <li class="flex gap-3">
                    <span class="shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">1</span>
                    <span>Buka menu "Invoice" untuk melihat tagihan Anda.</span>
                </li>
                <li class="flex gap-3">
                    <span class="shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">2</span>
                    <span>Pilih invoice yang belum dibayar, lalu klik "Detail".</span>
                </li>
                <li class="flex gap-3">
                    <span class="shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">3</span>
                    <span>Isi form bukti pembayaran: jumlah, tanggal, referensi, dan upload bukti transfer.</span>
                </li>
                <li class="flex gap-3">
                    <span class="shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">4</span>
                    <span>Kirim dan tunggu verifikasi dari tim kami.</span>
                </li>
            </ol>
        </div>

        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl p-6 text-center text-white">
            <h2 class="font-bold text-xl mb-2">Siap Mencoba?</h2>
            <p class="text-blue-100 text-sm mb-4">Masuk ke portal customer untuk mulai booking.</p>
            <a href="{{ route('portal.login') }}" class="inline-flex items-center gap-2 bg-white text-blue-700 px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-50 transition">
                Masuk ke Portal
            </a>
        </div>
    </div>
</div>
@endsection
