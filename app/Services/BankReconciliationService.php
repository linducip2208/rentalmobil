<?php

namespace App\Services;

use App\Models\BankStatementImport;
use App\Models\BankStatementLine;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BankReconciliationService
{
    /** Kolom yang dikenali parser berdasarkan kata kunci header CSV rekening koran. */
    private array $columnKeywords = [
        'transaction_date' => ['tanggal', 'date', 'tgl transaksi', 'posting date', 'tanggal transaksi'],
        'description' => ['keterangan', 'description', 'deskripsi', 'nama transaksi', 'detail', 'berita'],
        'amount_in' => ['mutasi masuk', 'masuk', 'kredit', 'credit', 'in', 'deposit'],
        'amount_out' => ['mutasi keluar', 'keluar', 'debet', 'debit', 'out', 'withdrawal'],
        'reference' => ['referensi', 'ref', 'no ref', 'reference', 'no referensi', 'keterangan ref'],
    ];

    public function parseCsv(string $content): array
    {
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $rows = array_values(array_filter(explode("\n", trim($content)), fn ($l) => trim($l) !== ''));

        if ($rows === []) {
            return [];
        }

        $delimiter = $this->detectDelimiter($rows[0]);
        $parsed = array_map(fn ($row) => array_map('trim', str_getcsv($row, $delimiter)), $rows);

        $headerIndex = $this->findHeaderRow($parsed);
        if ($headerIndex === null) {
            return [];
        }

        $header = array_map(fn ($h) => mb_strtolower(trim($h, " \t\"'")), $parsed[$headerIndex]);
        $map = $this->mapColumns($header);

        if (!isset($map['transaction_date'], $map['amount_in'])) {
            return [];
        }

        $lines = [];

        foreach (array_slice($parsed, $headerIndex + 1) as $row) {
            if (count($row) < 2 || $this->isNoiseRow($row)) {
                continue;
            }

            $date = $this->parseDate($row[$map['transaction_date']] ?? '');

            if (!$date) {
                continue;
            }

            $lines[] = [
                'transaction_date' => $date->toDateString(),
                'description' => mb_substr($row[$map['description'] ?? 0] ?? '', 0, 255),
                'amount_in' => $this->parseAmount($row[$map['amount_in']] ?? '0'),
                'amount_out' => isset($map['amount_out']) ? $this->parseAmount($row[$map['amount_out']] ?? '0') : 0,
                'reference' => isset($map['reference']) ? ($row[$map['reference']] ?: null) : null,
            ];
        }

        return $lines;
    }

    public function import(?int $bankAccountId, string $content, string $fileName, ?int $userId): BankStatementImport
    {
        $lines = $this->parseCsv($content);

        abort_if($lines === [], 422, 'CSV tidak dikenali. Pastikan ada kolom tanggal & mutasi masuk.');

        return DB::transaction(function () use ($bankAccountId, $lines, $fileName, $userId) {
            $import = BankStatementImport::create([
                'bank_account_id' => $bankAccountId,
                'file_name' => $fileName,
                'period_start' => min(array_column($lines, 'transaction_date')),
                'period_end' => max(array_column($lines, 'transaction_date')),
                'total_lines' => count($lines),
                'status' => 'ready',
                'imported_by' => $userId,
            ]);

            foreach ($lines as $line) {
                $import->lines()->create($line);
            }

            return $import;
        });
    }

    /**
     * Cocokkan baris mutasi dengan payment pending:
     * - Referensi sama persis dengan nomor referensi pembayaran -> keyakinan 1.0
     * - Nominal sama & tanggal dekat (±3 hari) -> keyakinan 0.7
     */
    public function autoMatch(BankStatementImport $import): int
    {
        $candidates = Payment::where('status', 'pending')->get()->keyBy('id');
        $used = Payment::whereIn('id', $import->lines()->whereNotNull('matched_payment_id')->pluck('matched_payment_id'))
            ->pluck('id')->all();

        $matched = 0;

        foreach ($import->lines()->where('match_status', 'unmatched')->get() as $line) {
            $amount = $line->lineAmount();

            if ($amount <= 0) {
                continue;
            }

            $best = null;
            $bestConfidence = 0;

            foreach ($candidates as $payment) {
                if (in_array($payment->id, $used)) {
                    continue;
                }

                if (abs((float) $payment->amount - $amount) > 0.01) {
                    continue;
                }

                $confidence = 0;

                if ($line->reference && str_contains(str_replace(' ', '', strtoupper((string) $payment->reference_number)), str_replace(' ', '', strtoupper($line->reference)))) {
                    $confidence = 1.0;
                } else {
                    $days = abs(Carbon::parse($payment->payment_date)->diffInDays($line->transaction_date));

                    if ($days <= 3) {
                        $confidence = $days == 0 ? 0.85 : 0.7;
                    }
                }

                if ($confidence > $bestConfidence) {
                    $bestConfidence = $confidence;
                    $best = $payment;
                }
            }

            if ($best && $bestConfidence >= 0.7) {
                $line->update([
                    'match_status' => 'matched',
                    'matched_payment_id' => $best->id,
                    'match_confidence' => $bestConfidence,
                    'match_note' => $bestConfidence >= 1 ? 'Cocok referensi persis' : 'Cocok nominal + tanggal',
                ]);

                $used[] = $best->id;
                $matched++;
            }
        }

        $import->update(['matched_count' => $import->lines()->where('match_status', 'matched')->count()]);

        return $matched;
    }

    /** Verifikasi semua pembayaran yang cocok pada import ini. */
    public function verifyMatched(BankStatementImport $import, ?int $userId): int
    {
        $verified = 0;

        foreach ($import->lines()->where('match_status', 'matched')->with('matchedPayment')->get() as $line) {
            $payment = $line->matchedPayment;

            if ($payment && $payment->status === 'pending') {
                app(PaymentService::class)->verifyPayment($payment, $userId);
                $verified++;
            }
        }

        $import->update(['status' => 'posted']);

        return $verified;
    }

    private function detectDelimiter(string $headerLine): string
    {
        foreach ([";", ",", "\t"] as $d) {
            if (substr_count($headerLine, $d) >= 1) {
                return $d;
            }
        }

        return ',';
    }

    private function findHeaderRow(array $parsed): ?int
    {
        foreach ($parsed as $i => $row) {
            $joined = mb_strtolower(implode(' ', $row));

            if (str_contains($joined, 'tanggal') || str_contains($joined, 'date')) {
                return $i;
            }
        }

        return null;
    }

    private function mapColumns(array $header): array
    {
        $map = [];

        foreach ($this->columnKeywords as $field => $keywords) {
            foreach ($header as $index => $title) {
                foreach ($keywords as $keyword) {
                    if (str_contains($title, $keyword)) {
                        $map[$field] ??= $index;
                        break 2;
                    }
                }
            }
        }

        // Kolom masuk/keluar kadang tertukar karena "debit" ambigu di beberapa bank.
        if (isset($map['amount_in'], $map['amount_out']) && $map['amount_in'] === $map['amount_out']) {
            unset($map['amount_out']);
        }

        return $map;
    }

    private function isNoiseRow(array $row): bool
    {
        $first = mb_strtolower(trim($row[0]));

        return in_array($first, ['saldo awal', 'saldo akhir', 'beginning balance', 'ending balance'], true)
            || str_starts_with($first, 'saldo');
    }

    private function parseDate(string $value): ?Carbon
    {
        $value = trim($value);

        foreach (['!Y-m-d', '!Y/m/d', '!d/m/Y', '!d-m-Y', '!d.m.Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);

                if ($date !== false) {
                    return $date->startOfDay();
                }
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseAmount(string $value): float
    {
        $value = str_replace('Rp', '', trim($value));
        $negative = str_contains($value, '-');
        $clean = preg_replace('/[^\d,.]/', '', $value) ?? '0';

        // Format Indonesia: 1.500.000,50 (titik ribuan, koma desimal)
        if (str_contains($clean, ',')) {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        }

        $float = (float) $clean;

        return $negative ? -abs($float) : $float;
    }
}
