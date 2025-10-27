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
                                            class="bg-blue-500 text-white px-3 py-1 rounded-md text-xs hover:bg-blue-600 transition">
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

    <!-- 🗺️ Improved Map Modal -->
    <div id="mapModal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity duration-300">
        <div
            class="relative bg-white rounded-2xl shadow-2xl w-11/12 sm:w-4/5 md:w-2/3 lg:w-1/2 overflow-hidden animate-fadeIn border border-gray-200">

            <!-- Modal Header -->
            <div class="flex justify-between items-center bg-gradient-to-r from-blue-600 to-blue-500 text-white px-5 py-3">
                <h3 class="text-lg font-semibold flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 11c0 .552-.448 1-1 1s-1-.448-1-1 .448-1 1-1 1 .448 1 1zm0 0v7m0-7a4 4 0 114 4h-1a3 3 0 10-3-3v-1zm-4 8a9 9 0 1118 0 9 9 0 01-18 0z" />
                    </svg>
                    Login Location
                </h3>
                <button id="closeMap"
                    class="text-white hover:text-gray-200 transition transform hover:scale-110 font-bold text-xl">
                    ✕
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-5 relative">
                <p id="mapInfo" class="text-sm text-gray-600 mb-4 border-l-4 border-blue-500 pl-3 leading-relaxed">
                    Fetching location details...
                </p>

                <div id="map" class="rounded-lg overflow-hidden border border-gray-200 shadow-inner"
                    style="height: 400px; width: 100%;"></div>

                <div id="loadingSpinner"
                    class="absolute inset-0 flex items-center justify-center bg-white/80 backdrop-blur-sm hidden">
                    <div
                        class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin drop-shadow-md">
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end bg-gray-50 px-5 py-3 border-t rounded-b-2xl">
                <button id="closeMapFooter"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-lg transition">
                    Close
                </button>
            </div>
        </div>
    </div>

    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.25s ease-out;
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

            #map {
                height: 300px !important;
            }
        }
    </style>

    <!-- 🌍 Google Maps JS -->
    <script async
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDLyHOIoQ384JE_xD7KFf9ujJZp1O7Dkmw&callback=initMap">
    </script>

    <script>
        let map;

        function initMap() {
            console.log("Google Maps API loaded");
        }

        async function showMap(ip, timestamp) {
            const modal = document.getElementById('mapModal');
            const spinner = document.getElementById('loadingSpinner');
            const mapInfo = document.getElementById('mapInfo');

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            spinner.classList.remove('hidden');
            mapInfo.textContent = `Fetching location for IP: ${ip} (${timestamp})`;

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

        document.getElementById('closeMap').addEventListener('click', closeMapModal);
        document.getElementById('closeMapFooter').addEventListener('click', closeMapModal);

        function closeMapModal() {
            const modal = document.getElementById('mapModal');
            modal.classList.add('opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex', 'opacity-0');
            }, 200);
        }
    </script>

</x-layouts.app>
