@extends('pseo._layout')

@section('content')
<section class="py-16 lg:py-24">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="font-bold text-4xl lg:text-5xl text-stone-900 mb-4">Beli Aplikasi Rental Mobil</h1>
        <p class="text-lg text-stone-500 mb-12">Source code aplikasi rental mobil berbasis Laravel. Fitur lengkap, siap deploy.</p>

        <div class="bg-gradient-to-br from-stone-900 to-stone-800 rounded-2xl p-8 lg:p-12 text-white mb-12">
            <h2 class="font-bold text-2xl mb-4">Aplikasi Rental Mobil — Full Source Code</h2>
            <p class="text-stone-300 mb-6">Bangun bisnis rental mobil Anda dengan aplikasi lengkap berbasis Laravel. Sudah termasuk booking online, manajemen armada, invoice otomatis, GPS tracking, multi-lokasi, dan masih banyak lagi.</p>
            <div class="grid sm:grid-cols-2 gap-3 text-sm mb-8">
                <div class="flex items-center gap-2"><span class="text-green-400"></span> Booking & Reservation Online</div>
                <div class="flex items-center gap-2"><span class="text-green-400"></span> Manajemen Armada & Inventaris</div>
                <div class="flex items-center gap-2"><span class="text-green-400"></span> Invoice & Pembayaran Otomatis</div>
                <div class="flex items-center gap-2"><span class="text-green-400"></span> GPS Tracking Kendaraan</div>
                <div class="flex items-center gap-2"><span class="text-green-400"></span> Multi-Lokasi & Multi-User</div>
                <div class="flex items-center gap-2"><span class="text-green-400"></span> Dashboard Analytics</div>
                <div class="flex items-center gap-2"><span class="text-green-400"></span> Laporan Keuangan & Pajak</div>
                <div class="flex items-center gap-2"><span class="text-green-400"></span> Customer Portal Self-Service</div>
                <div class="flex items-center gap-2"><span class="text-green-400"></span> Notifikasi WhatsApp & Email</div>
                <div class="flex items-center gap-2"><span class="text-green-400"></span> Integrasi Payment Gateway</div>
                <div class="flex items-center gap-2"><span class="text-green-400"></span> Anti-Fraud & Blacklist</div>
                <div class="flex items-center gap-2"><span class="text-green-400"></span> SEO Optimized & Responsive</div>
            </div>
            <div class="flex flex-wrap gap-4">
                <a href="https://wa.me/6281234567890?text=Halo,%20saya%20tertarik%20dengan%20source%20code%20aplikasi%20rental%20mobil" class="px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-all">Hubungi via WhatsApp</a>
                <a href="/docs" class="px-6 py-3 bg-white/10 text-white font-semibold rounded-lg hover:bg-white/20 transition-all">Lihat Dokumentasi</a>
            </div>
        </div>

        <div class="grid sm:grid-cols-3 gap-6 mb-12">
            <div class="bg-white border border-stone-200 rounded-2xl p-6 text-center card-lift">
                <div class="text-3xl mb-3">️</div>
                <h3 class="font-bold text-stone-900 mb-1">Laravel 11</h3>
                <p class="text-sm text-stone-500">Framework modern dengan performa tinggi dan keamanan terbaik</p>
            </div>
            <div class="bg-white border border-stone-200 rounded-2xl p-6 text-center card-lift">
                <div class="text-3xl mb-3"></div>
                <h3 class="font-bold text-stone-900 mb-1">Responsive</h3>
                <p class="text-sm text-stone-500">Tampilan optimal di desktop, tablet, dan mobile</p>
            </div>
            <div class="bg-white border border-stone-200 rounded-2xl p-6 text-center card-lift">
                <div class="text-3xl mb-3"></div>
                <h3 class="font-bold text-stone-900 mb-1">License Protected</h3>
                <p class="text-sm text-stone-500">Source code dilindungi license pairing untuk keamanan</p>
            </div>
        </div>

        <div class="bg-stone-100 rounded-2xl p-8">
            <h2 class="font-bold text-xl text-stone-900 mb-4">Tech Stack</h2>
            <div class="grid sm:grid-cols-2 gap-2 text-sm text-stone-600">
                <div>• <strong>Backend:</strong> Laravel 11 + PHP 8.2+</div>
                <div>• <strong>Database:</strong> MySQL 8.0</div>
                <div>• <strong>Admin Panel:</strong> Filament v3</div>
                <div>• <strong>Frontend:</strong> Blade + TailwindCSS + Alpine.js</div>
                <div>• <strong>Auth:</strong> Laravel Sanctum</div>
                <div>• <strong>Queue:</strong> Laravel Queue + Redis</div>
                <div>• <strong>Storage:</strong> S3-compatible / Local</div>
                <div>• <strong>Deployment:</strong> Nginx + Supervisor + CI/CD</div>
            </div>
        </div>
    </div>
</section>
@endsection
