<x-filament-panels::page.simple>
    @php($brand = app(\App\Services\WhitelabelService::class))
    <div class="rm-login-shell">
        <section class="rm-login-story" aria-label="Keunggulan {{ $brand->name() }}">
            <div class="rm-login-road" aria-hidden="true"></div>
            <a href="{{ route('home') }}" class="rm-login-brand"><span>{{ $brand->initials() }}</span><strong>{{ $brand->name() }}</strong></a>
            <div class="rm-login-copy">
                <p class="rm-login-kicker">{{ $brand->get('login_eyebrow', 'Fleet operations platform') }}</p>
                <h1>{{ $brand->get('login_headline', 'Kendaraan bergerak. Operasi tetap terkendali.') }}</h1>
                <p>{{ $brand->get('login_description', 'Kelola reservasi, serah-terima, GPS, keuangan, dan risiko dalam satu pusat kendali rental.') }}</p>
                <div class="rm-login-benefits">
                    <div><b>01</b><span>Dispatch real-time</span></div>
                    <div><b>02</b><span>GPS BYOK</span></div>
                    <div><b>03</b><span>Kontrol risiko</span></div>
                </div>
            </div>
            <small>&copy; {{ date('Y') }} {{ $brand->name() }} · {{ $brand->get('brand_copyright', 'Hak cipta dilindungi.') }}</small>
        </section>
        <section class="rm-login-form">
            <div class="rm-login-form-inner">
                <a href="{{ route('home') }}" class="rm-login-mobile-brand">&larr; {{ $brand->name() }}</a>
                <p class="rm-login-kicker">Area pengelola</p>
                <h2>Masuk ke pusat kendali</h2>
                <p class="rm-login-intro">Gunakan akun sesuai peran Anda. Hak menu dan data akan mengikuti tanggung jawab operasional.</p>
                {{ $this->content }}
                <div class="rm-demo-box">
                    <strong>Akun demo</strong>
                    <div><span>Owner</span><code>admin@rentalmobil.test / password</code></div>
                    <div><span>Manager</span><code>manager@rentalmobil.test / password</code></div>
                    <div><span>Kasir</span><code>kasir@rentalmobil.test / password</code></div>
                    <div><span>Driver</span><code>driver@rentalmobil.test / password</code></div>
                </div>
            </div>
        </section>
    </div>
</x-filament-panels::page.simple>
