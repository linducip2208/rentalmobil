<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #1a1a1a; font-size: 13px; line-height: 1.5; }
        .container { padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #1e40af; }
        .company-name { font-size: 22px; font-weight: 800; color: #1e40af; margin-bottom: 4px; }
        .company-details { font-size: 11px; color: #666; }
        .invoice-badge { background: #1e40af; color: #fff; padding: 8px 16px; border-radius: 6px; font-size: 14px; font-weight: 700; text-align: center; }
        .invoice-badge .small { font-size: 10px; font-weight: 400; opacity: 0.8; display: block; }
        .info-grid { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .info-block { width: 48%; }
        .info-block h4 { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #999; margin-bottom: 6px; }
        .info-block p { font-size: 12px; color: #333; }
        .info-block .bold { font-weight: 700; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead th { background: #1e3a5f; color: #fff; padding: 10px 12px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; }
        thead th:last-child, thead th:nth-child(3), thead th:nth-child(4) { text-align: right; }
        tbody td { padding: 10px 12px; border-bottom: 1px solid #eee; font-size: 12px; }
        tbody td:last-child, tbody td:nth-child(3), tbody td:nth-child(4) { text-align: right; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        .totals { display: flex; justify-content: flex-end; margin-bottom: 30px; }
        .totals-box { width: 280px; }
        .totals-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 12px; }
        .totals-row.total { border-top: 2px solid #1e40af; padding-top: 10px; margin-top: 6px; font-size: 16px; font-weight: 800; color: #1e40af; }
        .payment-info { background: #f0f4ff; border: 1px solid #d0daf0; border-radius: 8px; padding: 16px; margin-bottom: 20px; }
        .payment-info h4 { font-size: 12px; font-weight: 700; color: #1e40af; margin-bottom: 8px; }
        .payment-info p { font-size: 11px; color: #555; margin-bottom: 3px; }
        .footer-note { text-align: center; font-size: 10px; color: #999; margin-top: 30px; padding-top: 16px; border-top: 1px solid #eee; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-unpaid { background: #fee2e2; color: #991b1b; }
        .status-pending { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <div class="company-name">{{ $companyName ?? 'RentalMobil' }}</div>
                <div class="company-details">
                    {{ $companyAddress ?? 'Jl. Sudirman No. 123, Jakarta Selatan 12190' }}<br>
                    {{ $companyPhone ?? '+62 812-3456-7890' }} &middot; {{ $companyEmail ?? 'hello@rentalmobil.id' }}<br>
                    {{ $companyWebsite ?? 'www.rentalmobil.id' }}
                </div>
            </div>
            <div style="text-align: right;">
                <div class="invoice-badge">
                    INVOICE
                    <span class="small">{{ $invoiceNumber ?? 'INV-2026-0001' }}</span>
                </div>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-block">
                <h4>Tagih Kepada</h4>
                <p class="bold">{{ $customerName ?? 'Nama Pelanggan' }}</p>
                <p>{{ $customerEmail ?? 'email@pelanggan.com' }}</p>
                <p>{{ $customerPhone ?? '+62 812-xxxx-xxxx' }}</p>
                <p>{{ $customerAddress ?? '' }}</p>
            </div>
            <div class="info-block" style="text-align: right;">
                <h4>Detail Invoice</h4>
                <p><span class="bold">Tanggal Invoice:</span> {{ $invoiceDate ?? date('d M Y') }}</p>
                <p><span class="bold">Jatuh Tempo:</span> {{ $dueDate ?? date('d M Y', strtotime('+7 days')) }}</p>
                <p><span class="bold">Status:</span>
                    <span class="status-badge status-{{ $paymentStatus ?? 'unpaid' }}">
                        {{ ucfirst($paymentStatus ?? 'belum dibayar') }}
                    </span>
                </p>
                <p><span class="bold">No. Booking:</span> {{ $bookingNumber ?? 'BK-0001' }}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%">#</th>
                    <th style="width: 35%">Deskripsi</th>
                    <th style="width: 15%">Durasi</th>
                    <th style="width: 20%">Harga/Hari</th>
                    <th style="width: 25%; text-align: right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items ?? [['name' => 'Toyota Avanza 2024', 'duration' => '3 hari', 'daily_rate' => 350000, 'subtotal' => 1050000]] as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ $item['name'] ?? $item->name ?? 'Kendaraan' }}</strong>
                        @if($item['description'] ?? $item->description ?? null)
                        <br><span style="color: #888; font-size: 11px;">{{ $item['description'] ?? $item->description }}</span>
                        @endif
                    </td>
                    <td>{{ $item['duration'] ?? $item->duration ?? '-' }}</td>
                    <td style="text-align: right">Rp {{ number_format($item['daily_rate'] ?? $item->daily_rate ?? 0, 0, ',', '.') }}</td>
                    <td style="text-align: right">Rp {{ number_format($item['subtotal'] ?? $item->subtotal ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="totals-box">
                <div class="totals-row">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($subtotal ?? 1050000, 0, ',', '.') }}</span>
                </div>
                <div class="totals-row">
                    <span>Asuransi</span>
                    <span>{{ $insuranceFee ?? 'Termasuk' }}</span>
                </div>
                <div class="totals-row">
                    <span>Pajak (11%)</span>
                    <span>Rp {{ number_format($tax ?? 115500, 0, ',', '.') }}</span>
                </div>
                @if($discount ?? 0)
                <div class="totals-row" style="color: #059669;">
                    <span>Diskon</span>
                    <span>- Rp {{ number_format($discount, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="totals-row total">
                    <span>Total</span>
                    <span>Rp {{ number_format($total ?? 1165500, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="payment-info">
            <h4>Informasi Pembayaran</h4>
            <p><strong>Bank Transfer:</strong></p>
            <p>Bank BCA &mdash; a.n. {{ $companyName ?? 'RentalMobil' }}</p>
            <p>No. Rek: {{ $bankAccount ?? '1234 5678 9012' }}</p>
            <p style="margin-top: 8px;"><strong>E-Wallet:</strong> GoPay / OVO / Dana &mdash; {{ $companyPhone ?? '+62 812-3456-7890' }}</p>
        </div>

        @if($notes ?? null)
        <div style="margin-bottom: 20px;">
            <h4 style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #999; margin-bottom: 6px;">Catatan</h4>
            <p style="font-size: 11px; color: #555;">{{ $notes }}</p>
        </div>
        @endif

        <div class="footer-note">
            Terima kasih telah menggunakan layanan {{ $companyName ?? 'RentalMobil' }}!<br>
            Invoice ini dibuat secara otomatis dan sah tanpa tanda tangan.<br>
            Pertanyaan? Hubungi {{ $companyPhone ?? '+62 812-3456-7890' }} atau {{ $companyEmail ?? 'hello@rentalmobil.id' }}
        </div>
    </div>
</body>
</html>
