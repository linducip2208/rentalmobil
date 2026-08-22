<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Statement {{ $account->name }} — {{ $from->format('d M Y') }} s/d {{ $to->format('d M Y') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: sans-serif; font-size: 11px; color: #1e293b; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #1d4ed8; padding-bottom: 12px; margin-bottom: 16px; }
        .header h1 { font-size: 18px; color: #1e3a8a; }
        .muted { color: #64748b; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #1e3a5f; color: #fff; padding: 7px 8px; text-align: left; font-size: 9.5px; text-transform: uppercase; letter-spacing: .04em; }
        td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; }
        .num { text-align: right; white-space: nowrap; }
        .totals { margin-top: 14px; width: 45%; margin-left: auto; }
        .totals td { border: none; padding: 4px 8px; }
        .totals .grand { background: #eff6ff; font-weight: bold; font-size: 13px; }
        .credit-box { margin-top: 16px; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; background: #f8fafc; width: 48%; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>Statement Rental — {{ $account->name }}</h1>
            <div class="muted">NPWP: {{ $account->tax_id ?? '-' }} · Kontak: {{ $account->contact_name ?? '-' }} ({{ $account->contact_phone ?? '-' }})</div>
            <div class="muted">Periode: {{ $from->format('d M Y') }} s/d {{ $to->format('d M Y') }} · Termin pembayaran {{ $account->payment_terms_days }} hari</div>
        </div>
        <div style="text-align:right">
            <div style="font-weight:bold;font-size:13px;color:#1e3a8a">RentalMobil</div>
            <div class="muted">Dicetak {{ now()->format('d M Y H:i') }}</div>
        </div>
    </div>

    <table>
        <thead><tr>
            <th>No Order</th><th>Pelanggan</th><th>Kendaraan</th><th>Periode Sewa</th><th>PO</th>
            <th class="num">Ditagihkan</th><th class="num">Dibayar</th><th class="num">Sisa</th>
        </tr></thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['order']->order_number }}</td>
                    <td>{{ $row['order']->customer?->name }}</td>
                    <td>{{ $row['order']->vehicle?->name }}</td>
                    <td>{{ $row['order']->start_date?->format('d/m') }}–{{ $row['order']->end_date?->format('d/m/y') }}</td>
                    <td>{{ $row['order']->purchase_order_number ?? '-' }}</td>
                    <td class="num">Rp {{ number_format($row['invoiced'], 0, ',', '.') }}</td>
                    <td class="num">Rp {{ number_format($row['paid'], 0, ',', '.') }}</td>
                    <td class="num">Rp {{ number_format($row['balance'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;padding:20px" class="muted">Tidak ada order pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if ($rows->isNotEmpty())
        <table class="totals">
            <tr><td>Total Ditagihkan</td><td class="num">Rp {{ number_format($totalInvoiced, 0, ',', '.') }}</td></tr>
            <tr><td>Total Dibayar</td><td class="num">Rp {{ number_format($totalPaid, 0, ',', '.') }}</td></tr>
            <tr class="grand"><td>Sisa Periode Ini</td><td class="num">Rp {{ number_format($totalBalance, 0, ',', '.') }}</td></tr>
        </table>
    @endif

    <div class="credit-box">
        <b>Ringkasan Piutang Akun</b><br>
        Limit kredit: Rp {{ number_format($creditLimit, 0, ',', '.') }}<br>
        Total outstanding seluruh periode: <b>Rp {{ number_format($outstanding, 0, ',', '.') }}</b><br>
        Sisa limit tersedia: <b>Rp {{ number_format(max(0, $creditLimit - $outstanding), 0, ',', '.') }}</b>
    </div>
</body>
</html>
