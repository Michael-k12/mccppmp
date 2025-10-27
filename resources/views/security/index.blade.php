<x-layouts.app :title="'Security Monitoring'">
    <div class="p-6">
        <h2 class="text-2xl font-bold mb-4">Monitoring Logs</h2>

        <div class="bg-white shadow rounded-lg overflow-hidden">
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
                            <tr class="hover:bg-gray-50 cursor-pointer"
                                onclick="showLocationModal('{{ $log['ip'] ?? '' }}', '{{ $log['timestamp'] }}', '{{ $log['message'] }}')">
                                <td class="border px-4 py-2 text-gray-800 text-sm">{{ $log['timestamp'] ?? 'N/A' }}</td>
                                <td class="border px-4 py-2 font-semibold text-blue-600 text-sm">{{ $log['type'] }}</td>
                                <td class="border px-4 py-2 text-gray-700 text-sm">{{ $log['message'] }}</td>
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

    <!-- 🌍 Modal -->
    <div id="mapModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-11/12 md:w-3/4 lg:w-1/2 p-4 relative">
            <h3 class="text-lg font-semibold mb-2">Login Location</h3>
            <div id="map" style="height: 400px;" class="rounded-lg mb-3"></div>
            <p id="mapInfo" class="text-sm text-gray-700 text-center"></p>
            <button onclick="closeMapModal()" class="absolute top-2 right-2 text-gray-600 hover:text-black">&times;</button>
        </div>
    </div>

    <style>
        @media (max-width: 640px) {
            table th, table td {
                padding: 0.5rem 0.6rem;
                font-size: 13px;
            }
            h2 {
                font-size: 1.25rem;
            }
        }
    </style>

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDLyHOIoQ384JE_xD7KFf9ujJZp1O7Dkmw"></script>
    <script>
        let map, gpsMarker, ipMarker;

        async function showLocationModal(ip, timestamp, message) {
            const modal = document.getElementById('mapModal');
            const mapInfo = document.getElementById('mapInfo');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            mapInfo.innerHTML = "Fetching location...";

            // Initialize the map centered at Philippines
            map = new google.maps.Map(document.getElementById('map'), {
                center: { lat: 12.8797, lng: 121.7740 },
                zoom: 6
            });

            let ipData = null;
            try {
                const res = await fetch(`https://ipapi.co/${ip}/json/`);
                ipData = await res.json();
            } catch (error) {
                console.error("IP location fetch failed:", error);
            }

            // Plot IP location (approximate)
            if (ipData && ipData.latitude && ipData.longitude) {
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

            // Try GPS location (exact)
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        const gpsPos = {
                            lat: pos.coords.latitude,
                            lng: pos.coords.longitude
                        };
                        gpsMarker = new google.maps.Marker({
                            position: gpsPos,
                            map,
                            title: "Exact GPS Location",
                            icon: "http://maps.google.com/mapfiles/ms/icons/red-dot.png"
                        });
                        map.setCenter(gpsPos);
                        map.setZoom(13);

                        mapInfo.innerHTML = `
                            <b>Timestamp:</b> ${timestamp}<br>
                            <b>Message:</b> ${message}<br>
                            <b style="color:red;">Exact GPS (You):</b> ${gpsPos.lat.toFixed(5)}, ${gpsPos.lng.toFixed(5)}<br>
                            <b style="color:blue;">IP Approx:</b> ${ipData?.city || 'Unknown'}, ${ipData?.region || ''}, ${ipData?.country_name || ''}
                        `;
                    },
                    (error) => {
                        mapInfo.innerHTML = `
                            <b>Timestamp:</b> ${timestamp}<br>
                            <b>Message:</b> ${message}<br>
                            <b style="color:blue;">Approximate IP:</b> ${ipData?.city || 'Unknown'}, ${ipData?.region || ''}, ${ipData?.country_name || ''}<br>
                            (Exact GPS unavailable — user denied or not supported)
                        `;
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            } else {
                mapInfo.innerHTML = "Geolocation not supported in this browser.";
            }
        }

        function closeMapModal() {
            const modal = document.getElementById('mapModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
</x-layouts.app>
