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
                            <th class="border px-4 py-2 text-center whitespace-nowrap">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            @php
                                $ip = $log['ip'] ?? null;
                                if (!$ip && preg_match('/IP:\s*([\d\.]+)/', $log['message'], $m)) {
                                    $ip = $m[1];
                                }
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="border px-4 py-2 text-gray-800 text-sm">
                                    {{ $log['timestamp'] ?? 'N/A' }}
                                </td>
                                <td class="border px-4 py-2 font-semibold text-blue-600 text-sm">
                                    {{ $log['type'] }}
                                </td>
                                <td class="border px-4 py-2 text-gray-700 text-sm">
                                    {{ $log['message'] }}
                                </td>
                                <td class="border px-4 py-2 text-center">
                                    @if ($ip)
                                        <button onclick="showMap('{{ $ip }}', '{{ $log['timestamp'] ?? 'Unknown' }}')"
                                            class="bg-blue-500 text-white px-3 py-1 rounded-md text-xs hover:bg-blue-600">
                                            View on Map
                                        </button>
                                    @else
                                        <span class="text-gray-400 text-xs">No IP</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-gray-500">
                                    No logs found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 🗺️ Map Modal -->
    <div id="mapModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 backdrop-blur-sm">
        <div class="bg-white rounded-lg shadow-lg w-11/12 md:w-2/3 lg:w-1/2 p-5 relative animate-fadeIn">
            <button id="closeMap" class="absolute top-2 right-3 text-gray-600 hover:text-red-600 text-xl font-bold">
                ✕
            </button>
            <h3 class="text-lg font-bold mb-2 text-gray-800">Login Location</h3>
            <p id="mapInfo" class="text-sm text-gray-600 mb-3"></p>

            <div id="map" style="height: 400px; width: 100%;"></div>

            <div id="loadingSpinner"
                class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 hidden">
                <div class="w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
            </div>
        </div>
    </div>

    <!-- 🌍 Google Maps JS -->
    <script async
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDLyHOIoQ384JE_xD7KFf9ujJZp1O7Dkmw&callback=initMap">
    </script>

    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        .animate-fadeIn {
            animation: fadeIn 0.2s ease-in-out;
        }
        @media (max-width: 640px) {
            table th,
            table td {
                padding: 0.5rem 0.6rem;
                font-size: 13px;
            }
            h2 {
                font-size: 1.25rem;
            }
        }
    </style>

    <script>
        let map;

        function initMap() {
            console.log("Google Maps API loaded");
        }

        async function showMap(ip, timestamp) {
            const modal = document.getElementById('mapModal');
            const spinner = document.getElementById('loadingSpinner');
            const mapInfo = document.getElementById('mapInfo');
            const mapDiv = document.getElementById('map');

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            spinner.classList.remove('hidden');
            mapInfo.textContent = `Fetching location for IP: ${ip} (${timestamp})`;

            // ✅ Try GPS first
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        spinner.classList.add('hidden');
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;

                        mapInfo.textContent = `Exact GPS Location (via Browser): ${lat.toFixed(5)}, ${lon.toFixed(5)}`;

                        renderMap(lat, lon, "Your Actual Location");
                    },
                    async (error) => {
                        console.warn("GPS failed or denied, falling back to IP:", error);
                        await fallbackToIP(ip, spinner, mapInfo);
                    },
                    { enableHighAccuracy: true, timeout: 7000 }
                );
            } else {
                await fallbackToIP(ip, spinner, mapInfo);
            }
        }

        // ✅ Fallback to IP location if GPS denied
        async function fallbackToIP(ip, spinner, mapInfo) {
            try {
                const res = await fetch(`https://ipapi.co/${ip}/json/`);
                const data = await res.json();
                spinner.classList.add('hidden');

                const lat = data.latitude;
                const lon = data.longitude;
                const city = data.city || 'Unknown';
                const region = data.region || '';
                const country = data.country_name || '';

                if (!lat || !lon) {
                    mapInfo.textContent = `No location data available for IP: ${ip}`;
                    return;
                }

                mapInfo.textContent = `Approximate Location (via IP): ${city}, ${region}, ${country}`;
                renderMap(lat, lon, `${city}, ${region}, ${country}`);
            } catch (err) {
                spinner.classList.add('hidden');
                mapInfo.textContent = "Unable to fetch location for this IP.";
                console.error(err);
            }
        }

        // ✅ Render Google Map
        function renderMap(lat, lon, title) {
            const mapDiv = document.getElementById('map');
            mapDiv.innerHTML = '';

            map = new google.maps.Map(mapDiv, {
                center: { lat: lat, lng: lon },
                zoom: 14,
                mapTypeId: 'roadmap'
            });

            const marker = new google.maps.Marker({
                position: { lat: lat, lng: lon },
                map: map,
                title: title
            });

            const infoWindow = new google.maps.InfoWindow({
                content: `<b>${title}</b><br>Latitude: ${lat.toFixed(5)}<br>Longitude: ${lon.toFixed(5)}`
            });
            infoWindow.open(map, marker);

            google.maps.event.trigger(map, 'resize');
            map.setCenter({ lat: lat, lng: lon });
        }

        // Close modal
        document.getElementById('closeMap').addEventListener('click', () => {
            const modal = document.getElementById('mapModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });
    </script>

</x-layouts.app>
