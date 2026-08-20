@php($d = $this->data())
<x-filament-widgets::widget>
<div class="fleet-dispatch-shell">
    <section class="fleet-dispatch-hero">
        <div class="fleet-road-line" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
        <div class="fleet-hero-copy">
            <p class="fleet-eyebrow">PUSAT KENDALI · {{ now()->translatedFormat('l, d F Y') }}</p>
            <h2>Selamat {{ now()->hour < 11 ? 'pagi' : (now()->hour < 15 ? 'siang' : (now()->hour < 18 ? 'sore' : 'malam')) }}, {{ str($d['user']?->name)->before(' ') }}</h2>
            <p>Prioritas armada hari ini sudah dirangkum. Mulai dari titik yang membutuhkan tindakan.</p>
        </div>
        <div class="fleet-hero-status"><span class="fleet-live-dot"></span><div><strong>Sistem aktif</strong><small>Sinkronisasi {{ now()->format('H:i') }} WIB</small></div></div>
    </section>

    <section class="fleet-priority-grid" aria-label="Prioritas operasional">
        <a href="/admin/rental-orders" class="fleet-priority-card is-danger"><span>Terlambat</span><strong>{{ $d['overdue'] }}</strong><small>rental perlu ditindak</small></a>
        <a href="/admin/gps-trackers" class="fleet-priority-card is-warning"><span>GPS offline</span><strong>{{ $d['offline'] }}</strong><small>tracker tidak merespons</small></a>
        @if($d['approvals'] !== null)<a href="/admin" class="fleet-priority-card is-blue"><span>Persetujuan</span><strong>{{ $d['approvals'] }}</strong><small>menunggu keputusan</small></a>@endif
        <a href="/admin/service-schedules" class="fleet-priority-card is-neutral"><span>Servis dekat</span><strong>{{ $d['serviceDue'] }}</strong><small>dalam 14 hari</small></a>
        <a href="/admin/bookings" class="fleet-priority-card is-green"><span>Pipeline</span><strong>{{ $d['pipeline'] }}</strong><small>booking diproses</small></a>
    </section>

    <section class="fleet-today-grid">
        <div class="fleet-today-panel"><header><div><span class="fleet-panel-kicker">KEBERANGKATAN</span><h3>Keluar hari ini</h3></div><a href="/admin/fleet-calendar">Kalender →</a></header><div class="fleet-run-list">
            @forelse($d['departures'] as $order)<a href="/admin/rental-orders/{{ $order->id }}" class="fleet-run"><time>{{ $order->start_date?->format('H:i') ?: 'Hari ini' }}</time><span class="fleet-run-marker blue"></span><div><strong>{{ $order->vehicle?->name }}</strong><small>{{ $order->customer?->name }} · {{ $order->order_number }}</small></div><b>{{ str_replace('_', ' ', $order->status) }}</b></a>@empty<div class="fleet-empty"><strong>Tidak ada keberangkatan.</strong><span>Hari ini belum memiliki jadwal serah-terima keluar.</span></div>@endforelse
        </div></div>
        <div class="fleet-today-panel"><header><div><span class="fleet-panel-kicker amber">PENGEMBALIAN</span><h3>Kembali hari ini</h3></div><a href="/admin/operational-command-center">Command center →</a></header><div class="fleet-run-list">
            @forelse($d['returns'] as $order)<a href="/admin/rental-orders/{{ $order->id }}" class="fleet-run"><time>{{ $order->end_date?->format('H:i') ?: 'Hari ini' }}</time><span class="fleet-run-marker amber"></span><div><strong>{{ $order->vehicle?->name }}</strong><small>{{ $order->customer?->name }} · {{ $order->order_number }}</small></div><b>{{ str_replace('_', ' ', $order->status) }}</b></a>@empty<div class="fleet-empty"><strong>Tidak ada pengembalian.</strong><span>Belum ada kendaraan yang dijadwalkan kembali hari ini.</span></div>@endforelse
        </div></div>
    </section>
</div>
</x-filament-widgets::widget>
