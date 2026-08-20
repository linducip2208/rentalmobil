@php
    $trackers = \App\Models\GpsTracker::with('vehicle')
        ->where('is_active', true)
        ->get();
@endphp

<x-filament::page>
    <div class="space-y-6">
        {{-- Driver Report Location --}}
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <h3 class="font-bold text-lg text-stone-900 mb-4">📱 Kirim Lokasi Driver</h3>
            <p class="text-sm text-stone-500 mb-4">Halaman ini dibuka dari HP driver. GPS otomatis mengirim lokasi ke server.</p>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-stone-700 mb-1">Pilih GPS Tracker</label>
                    <select id="tracker-select" class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">-- Pilih Perangkat --</option>
                        @foreach($trackers as $tracker)
                            <option value="{{ $tracker->id }}" data-name="{{ $tracker->device_name ?? $tracker->device_id }}">
                                {{ $tracker->device_name ?? $tracker->device_id }}
                                @if($tracker->vehicle) — {{ $tracker->vehicle->name }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-stone-700 mb-1">Status</label>
                    <div id="status-display" class="flex items-center gap-2 py-2 text-sm text-stone-500">
                        <span class="w-3 h-3 bg-stone-300 rounded-full" id="status-dot"></span>
                        <span id="status-text">Menunggu pemilihan perangkat...</span>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <button id="start-btn" disabled
                    class="px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                    ▶️ Mulai Tracking
                </button>
                <button id="stop-btn" disabled
                    class="px-6 py-3 bg-red-500 text-white font-semibold rounded-xl hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed transition ml-2">
                    ⏹️ Stop
                </button>
            </div>

            <div id="tracking-info" class="hidden mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-stone-50 rounded-lg p-3">
                    <div class="text-xs text-stone-500">Latitude</div>
                    <div class="font-mono text-sm font-bold text-stone-900" id="info-lat">-</div>
                </div>
                <div class="bg-stone-50 rounded-lg p-3">
                    <div class="text-xs text-stone-500">Longitude</div>
                    <div class="font-mono text-sm font-bold text-stone-900" id="info-lng">-</div>
                </div>
                <div class="bg-stone-50 rounded-lg p-3">
                    <div class="text-xs text-stone-500">Kecepatan</div>
                    <div class="font-mono text-sm font-bold text-stone-900" id="info-speed">- km/h</div>
                </div>
                <div class="bg-stone-50 rounded-lg p-3">
                    <div class="text-xs text-stone-500">Terkirim</div>
                    <div class="font-mono text-sm font-bold text-emerald-600" id="info-sent">0 kali</div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var trackerSelect = document.getElementById('tracker-select');
            var startBtn = document.getElementById('start-btn');
            var stopBtn = document.getElementById('stop-btn');
            var statusDot = document.getElementById('status-dot');
            var statusText = document.getElementById('status-text');
            var trackingInfo = document.getElementById('tracking-info');
            var watchId = null;
            var sendCount = 0;
            var intervalId = null;

            trackerSelect.addEventListener('change', function() {
                startBtn.disabled = !this.value;
            });

            startBtn.addEventListener('click', function() {
                if (!navigator.geolocation) {
                    alert('GPS tidak didukung di browser ini');
                    return;
                }

                var trackerId = trackerSelect.value;
                statusDot.className = 'w-3 h-3 bg-amber-400 rounded-full animate-pulse';
                statusText.textContent = 'Mengaktifkan GPS...';
                trackingInfo.classList.remove('hidden');
                startBtn.disabled = true;
                stopBtn.disabled = false;

                watchId = navigator.geolocation.watchPosition(
                    function(pos) {
                        statusDot.className = 'w-3 h-3 bg-emerald-500 rounded-full';
                        statusText.textContent = 'GPS aktif — sedang mengirim data';

                        document.getElementById('info-lat').textContent = pos.coords.latitude.toFixed(7);
                        document.getElementById('info-lng').textContent = pos.coords.longitude.toFixed(7);
                        document.getElementById('info-speed').textContent = (pos.coords.speed ? (pos.coords.speed * 3.6).toFixed(1) : '0') + ' km/h';

                        fetch('/api/gps/report', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                            },
                            body: JSON.stringify({
                                vehicle_id: trackerId,
                                latitude: pos.coords.latitude,
                                longitude: pos.coords.longitude,
                                speed: pos.coords.speed ? (pos.coords.speed * 3.6) : 0,
                                heading: pos.coords.heading || 0,
                                accuracy: pos.coords.accuracy,
                            })
                        }).then(function() {
                            sendCount++;
                            document.getElementById('info-sent').textContent = sendCount + ' kali';
                        });
                    },
                    function(err) {
                        statusDot.className = 'w-3 h-3 bg-red-500 rounded-full';
                        statusText.textContent = 'Error: ' + err.message;
                    },
                    { enableHighAccuracy: true, maximumAge: 5000, timeout: 15000 }
                );
            });

            stopBtn.addEventListener('click', function() {
                if (watchId !== null) {
                    navigator.geolocation.clearWatch(watchId);
                    watchId = null;
                }
                statusDot.className = 'w-3 h-3 bg-stone-300 rounded-full';
                statusText.textContent = 'Tracking dihentikan';
                startBtn.disabled = false;
                stopBtn.disabled = true;
            });
        });
    </script>
    @endpush
</x-filament::page>
