<x-layouts.app :title="'Security Monitoring'">
    <div class="p-6">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-800">Monitoring Logs</h2>
        </div>

        {{-- Logs Table --}}
        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full border text-sm">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="border px-4 py-2 text-left whitespace-nowrap">Timestamp</th>
                            <th class="border px-4 py-2 text-left whitespace-nowrap">Type</th>
                            <th class="border px-4 py-2 text-left whitespace-nowrap">Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr class="hover:bg-gray-50 cursor-pointer transition-all duration-150"
                                onclick="showLocationModal('{{ $log['ip'] ?? '' }}', '{{ $log['timestamp'] }}', '{{ $log['message'] }}')">
                                <td class="border px-4 py-2 text-gray-800">{{ $log['timestamp'] ?? 'N/A' }}</td>
                                <td class="border px-4 py-2 font-semibold text-blue-600">{{ $log['type'] }}</td>
                                <td class="border px-4 py-2 text-gray-700">{{ $log['message'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-gray-500">No logs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Map Modal --}}
    <div id="mapModal" class="fixed inset-0 hidden items-center justify-center z-50 bg-black bg-opacity-30">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl p-6 relative animate-fadeIn">
            <button onclick="closeMapModal()" class="absolute top-4 right-4 text-gray-600 hover:text-black text-2xl">&times;</button>
            <h3 class="text-xl font-semibold mb-4 text-gray-800 text-center">Login Location Details</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div id="map" style="height: 400px;" class="rounded-lg border border-gray-200 shadow-sm"></div>
                </div>
                <div class="flex flex-col justify-center p-2 bg-gray-50 rounded-lg border border-gray-200">
                    <p id="mapInfo" class="text-gray-700 text-sm leading-relaxed"></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Styles --}}
    <style>
        .animate-fadeIn {
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 640px) {
            table th, table td { padding: 0.5rem 0.6rem; font-size: 13px; }
            h2 { font-size: 1.25rem; }
        }
    </style>

    {{-- Scripts --}}
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY"></script>
    <script>
        let map, ipMarker, gpsMarker;

        async function showLocationModal(ip, timestamp, message) {
            const modal = document.getElementById('mapModal');
            const mapInfo = document.getElementById('mapInfo');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            mapInfo.innerHTML = "Fetching location...";

            // Initialize map centered at Philippines
            map = new google.maps.Map(document.getElementById('map'), {
                center: { lat: 12.8797, lng: 121.7740 },
                zoom: 6
            });

            let ipData = null;
            try {
                const res = await fetch(`https://ipapi.co/${ip}/json/`);
                ipData = await res.json();
            } catch (err) {
                console.error("IP fetch failed:", err);
            }

            // Plot IP location
            if (ipData?.latitude && ipData?.longitude) {
                const ipPos = { lat: ipData.latitude, lng: ipData.longitude };
                ipMarker = new google.maps.Marker({
                    position: ipPos,
                    map,
                    title: `Approximate IP Location: ${ipData.city}`,
                    icon: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png"
                });
                map.setCenter(ipPos);
                map.setZoom(10);
            }

            // Attempt GPS (exact) location
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        const gpsPos = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                        gpsMarker = new google.maps.Marker({
                            position: gpsPos,
                            map,
                            title: "Exact GPS Location",
                            icon: "http://maps.google.com/mapfiles/ms/icons/red-dot.png"
                        });

                        // Adjust map to show both markers
                        const bounds = new google.maps.LatLngBounds();
                        if (ipMarker) bounds.extend(ipMarker.getPosition());
                        bounds.extend(gpsMarker.getPosition());
                        map.fitBounds(bounds);

                        mapInfo.innerHTML = `
                            <b>Timestamp:</b> ${timestamp}<br>
                            <b>Message:</b> ${message}<br><br>
                            <b style="color:red;">Exact GPS:</b> ${gpsPos.lat.toFixed(5)}, ${gpsPos.lng.toFixed(5)}<br>
                            <b style="color:blue;">Approx. IP Location:</b> ${ipData?.city || 'Unknown'}, ${ipData?.region || ''}, ${ipData?.country_name || ''}
                        `;
                    },
                    (error) => {
                        mapInfo.innerHTML = `
                            <b>Timestamp:</b> ${timestamp}<br>
                            <b>Message:</b> ${message}<br><br>
                            <b style="color:blue;">Approx. IP Location:</b> ${ipData?.city || 'Unknown'}, ${ipData?.region || ''}, ${ipData?.country_name || ''}<br>
                            (Exact GPS unavailable)
                        `;
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            } else {
                mapInfo.innerHTML = `
                    <b>Timestamp:</b> ${timestamp}<br>
                    <b>Message:</b> ${message}<br>
                    Geolocation not supported in this browser.
                `;
            }
        }

        function closeMapModal() {
            const modal = document.getElementById('mapModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');

            // Remove markers
            if(ipMarker) ipMarker.setMap(null);
            if(gpsMarker) gpsMarker.setMap(null);
        }
    </script>
</x-layouts.app>
