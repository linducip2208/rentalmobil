<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $permit->spj_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: sans-serif; font-size: 11px; color: #1e293b; }
        .header { display: flex; justify-content: space-between; border-bottom: 3px solid #1e3a5f; padding-bottom: 10px; margin-bottom: 14px; }
        h1 { font-size: 17px; color: #1e3a5f; letter-spacing: .03em; }
        .spj-no { font-family: monospace; font-weight: bold; font-size: 13px; }
        .muted { color: #64748b; font-size: 9.5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th { background: #eff6ff; text-align: left; padding: 6px 8px; font-size: 9.5px; text-transform: uppercase; color: #1e40af; border-bottom: 1px solid #bfdbfe; }
        td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .num { text-align: right; white-space: nowrap; }
        .sign-row { margin-top: 34px; display: flex; justify-content: space-between; }
        .sign-box { width: 30%; text-align: center; }
        .sign-line { margin-top: 46px; border-top: 1px solid #334155; padding-top: 4px; font-size: 10px; }
        .note { background: #fefce8; border: 1px solid #fde047; padding: 8px 10px; border-radius: 6px; font-size: 9.5px; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>SURAT PERJALANAN JALAN (SPJ)</h1>
            <div class="muted">RentalMobil — dokumen operasional sewa dengan supir</div>
        </div>
        <div style="text-align:right">
            <span class="spj-no">{{ $permit->spj_number }}</span><br>
            <span class="muted">Dicetak {{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    <table>
        <tr>
            <th style="width:25%">No. Order</th><td style="width:25%">{{ $permit->rentalOrder?->order_number }}</td>
            <th style="width:25%">Supir</th><td>{{ $permit->driver?->name }}{{ $permit->driver?->phone ? ' ('.$permit->driver->phone.')' : '' }}</td>
        </tr>
        <tr>
            <th>Penyewa</th><td>{{ $permit->rentalOrder?->customer?->name }}</td>
            <th>Kendaraan</th><td>{{ $permit->rentalOrder?->vehicle?->name }} · {{ $permit->rentalOrder?->vehicle?->plate_number }}</td>
        </tr>
        <tr>
            <th>Rute</th><td>{{ $permit->route_from }} → {{ $permit->route_to }}</td>
            <th>Status</th><td>{{ $permit->status === 'open' ? 'SEDANG BERJALAN' : 'SELESAI'.($permit->finished_at ? ' — '.$permit->finished_at->format('d/m/Y H:i') : '') }}</td>
        </tr>
        <tr>
            <th>Berangkat</th><td>{{ $permit->started_at?->format('d/m/Y H:i') ?? '-' }}</td>
            <th>Kembali</th><td>{{ $permit->finished_at?->format('d/m/Y H:i') ?? '-' }}</td>
        </tr>
    </table>

    <table>
        <thead><tr><th colspan="2">Kondisi & Biaya Operasional</th></tr></thead>
        <tbody>
            <tr><td style="width:50%">BBM berangkat</td><td>{{ ['full'=>'Penuh','three_quarter'=>'3/4','half'=>'1/2','quarter'=>'1/4','empty'=>'Kosong'][$permit->fuel_start_level] ?? '-' }}{{ $permit->fuel_end_level ? ' → kembali '.(['full'=>'Penuh','three_quarter'=>'3/4','half'=>'1/2','quarter'=>'1/4','empty'=>'Kosong'][$permit->fuel_end_level]) : '' }}</td></tr>
            <tr><td>Odometer</td><td>{{ number_format($permit->odometer_start) }} km {{ $permit->odometer_end ? '→ '.number_format($permit->odometer_end).' km (tempuh '.number_format(max(0, $permit->odometer_end - $permit->odometer_start)).' km)' : '' }}</td></tr>
            <tr><td>Tol</td><td class="num">Rp {{ number_format((float) $permit->toll_cost, 0, ',', '.') }}</td></tr>
            <tr><td>Parkir</td><td class="num">Rp {{ number_format((float) $permit->parking_cost, 0, ',', '.') }}</td></tr>
            <tr><td>Akomodasi / uang makan</td><td class="num">Rp {{ number_format((float) $permit->accommodation_cost, 0, ',', '.') }}</td></tr>
            <tr><td><b>Total biaya operasional</b></td><td class="num"><b>Rp {{ number_format($permit->totalOperationalCost(), 0, ',', '.') }}</b></td></tr>
        </tbody>
    </table>

    @if ($permit->notes)
        <div class="note"><b>Catatan:</b> {{ $permit->notes }}</div>
    @endif

    <p style="margin-top:14px" class="muted">SPJ ini wajib dibawa supir selama perjalanan. Biaya operasional di atas menjadi dasar reimbursement dan penagihan ke penyewa sesuai kesepakatan.</p>

    <div class="sign-row">
        <div class="sign-box"><div class="sign-line">Supir</div></div>
        <div class="sign-box"><div class="sign-line">Penyewa</div></div>
        <div class="sign-box"><div class="sign-line">Admin Operasional</div></div>
    </div>
</body>
</html>
