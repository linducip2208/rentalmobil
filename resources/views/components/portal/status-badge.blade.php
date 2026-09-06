@props(['status' => '', 'type' => 'default'])

@php
    $key = strtolower((string) $status);

    $maps = [
        'booking' => [
            'hold' => ['Ditahan', 'bg-slate-100 text-slate-600'],
            'pending_verification' => ['Menunggu verifikasi', 'bg-amber-50 text-amber-700'],
            'pending_payment' => ['Menunggu pembayaran', 'bg-amber-50 text-amber-700'],
            'confirmed' => ['Dikonfirmasi', 'bg-blue-50 text-blue-700'],
            'converted' => ['Jadi pesanan', 'bg-emerald-50 text-emerald-700'],
            'expired' => ['Kedaluwarsa', 'bg-slate-100 text-slate-500'],
            'cancelled' => ['Dibatalkan', 'bg-red-50 text-red-600'],
        ],
        'order' => [
            'draft' => ['Draf', 'bg-slate-100 text-slate-600'],
            'ready_for_preparation' => ['Siap disiapkan', 'bg-blue-50 text-blue-700'],
            'preparing' => ['Disiapkan', 'bg-blue-50 text-blue-700'],
            'ready_for_handover' => ['Siap serah terima', 'bg-indigo-50 text-indigo-700'],
            'active' => ['Aktif', 'bg-emerald-50 text-emerald-700'],
            'checked_out' => ['Keluar', 'bg-emerald-50 text-emerald-700'],
            'overdue' => ['Terlambat', 'bg-red-50 text-red-600'],
            'returned' => ['Kembali', 'bg-slate-100 text-slate-600'],
            'completed' => ['Selesai', 'bg-emerald-50 text-emerald-700'],
            'cancelled' => ['Dibatalkan', 'bg-red-50 text-red-600'],
        ],
        'invoice' => [
            'draft' => ['Draf', 'bg-slate-100 text-slate-600'],
            'unpaid' => ['Belum dibayar', 'bg-red-50 text-red-600'],
            'pending' => ['Menunggu verifikasi', 'bg-amber-50 text-amber-700'],
            'partial' => ['Sebagian', 'bg-amber-50 text-amber-700'],
            'paid' => ['Lunas', 'bg-emerald-50 text-emerald-700'],
            'overdue' => ['Jatuh tempo', 'bg-red-50 text-red-600'],
            'cancelled' => ['Dibatalkan', 'bg-slate-100 text-slate-500'],
        ],
        'deposit' => [
            'expected' => ['Menunggu pembayaran', 'bg-amber-50 text-amber-700'],
            'pending' => ['Menunggu verifikasi', 'bg-amber-50 text-amber-700'],
            'paid' => ['Dibayar', 'bg-emerald-50 text-emerald-700'],
            'received' => ['Diterima', 'bg-emerald-50 text-emerald-700'],
            'held' => ['Ditahan', 'bg-blue-50 text-blue-700'],
            'partially_deducted' => ['Dipotong sebagian', 'bg-orange-50 text-orange-700'],
            'refunded' => ['Dikembalikan', 'bg-emerald-50 text-emerald-700'],
            'forfeited' => ['Hangus', 'bg-red-50 text-red-600'],
            'disputed' => ['Sengketa', 'bg-red-50 text-red-600'],
        ],
        'document' => [
            'pending' => ['Menunggu verifikasi', 'bg-amber-50 text-amber-700'],
            'verified' => ['Terverifikasi', 'bg-emerald-50 text-emerald-700'],
            'rejected' => ['Ditolak', 'bg-red-50 text-red-600'],
            'expired' => ['Kadaluarsa', 'bg-slate-100 text-slate-500'],
        ],
    ];

    $map = $maps[$type] ?? [];
    // Fallback: coba cari di semua tipe agar pemakaian tanpa type tetap berwarna.
    if (! isset($map[$key])) {
        foreach ($maps as $m) {
            if (isset($m[$key])) {
                $map = $m;
                break;
            }
        }
    }

    [$label, $tone] = $map[$key] ?? [ucwords(str_replace('_', ' ', $key)), 'bg-slate-100 text-slate-600'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-lg px-3 py-1 text-xs font-bold '.$tone]) }}>{{ $label }}</span>
