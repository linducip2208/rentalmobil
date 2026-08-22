<div class="space-y-2">
    @php $findings = $getState() ?? []; @endphp
    @if (empty($findings))
        <p class="text-sm text-gray-400">Belum ada analisis AI. Jalankan aksi "Analisis AI" dari daftar inspeksi.</p>
    @else
        @foreach ($findings as $finding)
            @if (isset($finding['raw_text']))
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs text-gray-600 whitespace-pre-wrap">{{ $finding['raw_text'] }}</div>
            @else
                <div class="rounded-lg border border-gray-200 bg-white p-3">
                    <div class="flex items-center justify-between gap-2">
                        <strong class="text-sm">{{ $finding['location_on_vehicle'] ?? '?' }}</strong>
                        <span class="rounded px-2 py-0.5 text-[11px] font-semibold
                            @if(($finding['severity'] ?? '') === 'critical') bg-red-100 text-red-700
                            @elseif(($finding['severity'] ?? '') === 'severe') bg-orange-100 text-orange-700
                            @elseif(($finding['severity'] ?? '') === 'moderate') bg-amber-100 text-amber-700
                            @else bg-stone-100 text-stone-600 @endif">
                            {{ $finding['severity'] ?? 'minor' }}
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-gray-600">{{ $finding['description'] ?? '' }}</p>
                    <div class="mt-1 flex gap-3 text-[11px] text-gray-400">
                        <span>{{ $finding['damage_type'] ?? '-' }}</span>
                        @if (isset($finding['estimated_cost_idr']))<span>Est. Rp {{ number_format((float) $finding['estimated_cost_idr'], 0, ',', '.') }}</span>@endif
                        @if (isset($finding['confidence']))<span>Keyakinan {{ (int) round($finding['confidence'] * 100) }}%</span>@endif
                    </div>
                </div>
            @endif
        @endforeach
    @endif
</div>
