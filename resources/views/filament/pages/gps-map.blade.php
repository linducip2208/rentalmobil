@php
    $trackers = \App\Models\GpsTracker::with('vehicle')
        ->where('is_active', true)
        ->whereNotNull('last_latitude')
        ->get()
        ->map(fn($t) => [
            'id' => $t->id,
            'device_name' => $t->device_name ?? $t->device_id,
            'vehicle' => $t->vehicle?->name ?? 'Unassigned',
            'plate' => $t->vehicle?->plate_number ?? '-',
            'lat' => (float) $t->last_latitude,
            'lng' => (float) $t->last_longitude,
            'speed' => (float) ($t->last_speed ?? 0),
            'heading' => (int) ($t->last_heading ?? 0),
            'battery' => $t->last_battery_level,
            'is_online' => $t->isOnline(),
            'last_update' => $t->last_update_at?->diffForHumans() ?? 'N/A',
            'status' => $t->status,
        ]);
@endphp

<x-filament::page>
    <div class="space-y-4">
        {{-- Stats Bar --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
            <div class="fi-wi-stats-overview-stat bg-white rounded-xl p-4 border border-stone-200">
                <div class="text-sm text-stone-500">Total Tracker Aktif</div>
                <div class="text-2xl font-bold text-stone-900">{{ $trackers->count() }}</div>
            </div>
            <div class="fi-wi-stats-overview-stat bg-white rounded-xl p-4 border border-stone-200">
                <div class="text-sm text-stone-500">Online</div>
                <div class="text-2xl font-bold text-emerald-600">{{ $trackers->where('is_online', true)->count() }}</div>
            </div>
            <div class="fi-wi-stats-overview-stat bg-white rounded-xl p-4 border border-stone-200">
                <div class="text-sm text-stone-500">Offline</div>
                <div class="text-2xl font-bold text-stone-400">{{ $trackers->where('is_online', false)->count() }}</div>
            </div>
            <div class="fi-wi-stats-overview-stat bg-white rounded-xl p-4 border border-stone-200">
                <div class="text-sm text-stone-500">Bergerak</div>
                <div class="text-2xl font-bold text-amber-600">{{ $trackers->where('speed', '>', 0)->count() }}</div>
            </div>
        </div>

        {{-- Map --}}
        <div class="bg-white rounded-xl border border-stone-200 overflow-hidden" style="height: 600px;" id="gps-map-container">
            <div id="gps-map" style="width: 100%; height: 100%;"></div>
        </div>

        {{-- Tracker List --}}
        <div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
            <div class="p-4 border-b border-stone-200">
                <h3 class="font-semibold text-stone-900">Daftar GPS Tracker</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-stone-200 bg-stone-50">
                            <th class="px-4 py-3 text-left font-semibold text-xs uppercase text-stone-500">Perangkat</th>
                            <th class="px-4 py-3 text-left font-semibold text-xs uppercase text-stone-500">Kendaraan</th>
                            <th class="px-4 py-3 text-left font-semibold text-xs uppercase text-stone-500">Plat</th>
                            <th class="px-4 py-3 text-left font-semibold text-xs uppercase text-stone-500">Speed</th>
                            <th class="px-4 py-3 text-left font-semibold text-xs uppercase text-stone-500">Battery</th>
                            <th class="px-4 py-3 text-left font-semibold text-xs uppercase text-stone-500">Status</th>
                            <th class="px-4 py-3 text-left font-semibold text-xs uppercase text-stone-500">Terakhir Update</th>
                            <th class="px-4 py-3 text-left font-semibold text-xs uppercase text-stone-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse($trackers as $tracker)
                        <tr class="hover:bg-stone-50">
                            <td class="px-4 py-3 font-medium text-stone-900">{{ $tracker['device_name'] }}</td>
                            <td class="px-4 py-3 text-stone-600">{{ $tracker['vehicle'] }}</td>
                            <td class="px-4 py-3 font-mono text-stone-600">{{ $tracker['plate'] }}</td>
                            <td class="px-4 py-3">
                                <span class="font-mono text-stone-600">{{ number_format($tracker['speed'], 1) }} km/h</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($tracker['battery'] !== null)
                                    <span class="inline-flex items-center gap-1
                                        {{ $tracker['battery'] < 20 ? 'text-red-500' : ($tracker['battery'] < 50 ? 'text-amber-500' : 'text-emerald-500') }}">
                                        Batt {{ $tracker['battery'] }}%
                                    </span>
                                @else
                                    <span class="text-stone-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($tracker['is_online'])
                                    <span class="inline-flex items-center gap-1.5 text-emerald-600 font-medium text-xs">
                                        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span> Online
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-stone-400 font-medium text-xs">
                                        <span class="w-2 h-2 bg-stone-300 rounded-full"></span> Offline
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-stone-500 text-xs">{{ $tracker['last_update'] }}</td>
                            <td class="px-4 py-3">
                                <a href="https://www.google.com/maps?q={{ $tracker['lat'] }},{{ $tracker['lng'] }}"
                                   target="_blank"
                                   class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">
                                    Buka di Maps →
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-stone-400">
                                Belum ada GPS tracker yang terdaftar atau belum ada data lokasi.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var map = L.map('gps-map').setView([-6.2088, 106.8456], 11);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(map);

            var trackers = @json($trackers);
            var markers = [];

            trackers.forEach(function(t) {
                var color = t.is_online ? '#10b981' : '#9ca3af';
                var speed = t.speed;

                var marker = L.circleMarker([t.lat, t.lng], {
                    radius: 10,
                    fillColor: color,
                    color: '#fff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.85,
                }).addTo(map);

                var popup = '<div style="min-width:200px">' +
                    '<strong style="font-size:14px">' + t.vehicle + '</strong><br>' +
                    '<span style="color:#6b7280;font-size:12px">' + t.device_name + ' · ' + t.plate + '</span><br><br>' +
                    '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px">' +
                    '<div>Speed: <strong>' + speed.toFixed(1) + ' km/h</strong></div>' +
                    '<div>Battery: <strong>' + (t.battery !== null ? t.battery + '%' : 'N/A') + '</strong></div>' +
                    '<div>Status: <strong style="color:' + color + '">' + (t.is_online ? 'Online' : 'Offline') + '</strong></div>' +
                    '<div>Update: <strong>' + t.last_update + '</strong></div>' +
                    '</div><br>' +
                    '<a href="https://www.google.com/maps?q=' + t.lat + ',' + t.lng + '" target="_blank" ' +
                    'style="color:#6366f1;font-size:12px;font-weight:600">Buka di Google Maps →</a>' +
                    '</div>';

                marker.bindPopup(popup);
                markers.push(marker);
            });

            if (markers.length > 0) {
                var group = L.featureGroup(markers);
                map.fitBounds(group.getBounds().pad(0.1));
            }

            setInterval(function() {
                fetch('{{ route('internal.gps.trackers') }}', { credentials: 'same-origin' })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        data.trackers.forEach(function(t) {
                            var existing = markers.find(function(m) { return m._trackerId === t.id; });
                            if (existing) {
                                existing.setLatLng([t.lat, t.lng]);
                            }
                        });
                    });
            }, 30000);
        });
    </script>
    @endpush
</x-filament::page>
