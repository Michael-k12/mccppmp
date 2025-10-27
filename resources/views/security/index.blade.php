<x-layouts.app :title="'Security Monitoring'">

    <div class="p-6">
        <h2 class="text-2xl font-bold mb-4">Monitoring Logs</h2>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <!-- Responsive table wrapper -->
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
                                // Extract IP address from message if not stored directly
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

            <div id="map" class="w-full h-64 rounded-md border border-gray-300"></div>

            <!-- Loading spinner -->
            <div id="loadingSpinner"
                class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 hidden">
                <div class="w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
            </div>
        </div>
    </div>

    <!-- 🌍 Google Maps JS (Replace with your own API Key) -->
    <script async
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDLyHOIoQ384JE_xD7KFf9ujJZp1O7Dkmw&callback=initMap"></script>

    <style>
        /* Fade animation for modal */
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

        /* Improve mobile view */
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
        // Placeholder required for Google Maps async loading
    }

    async function showMap(ip, timestamp) {
        const modal = document.getElementById('mapModal');
        const spinner = document.getElementById('loadingSpinner');
        const mapInfo = document.getElementById('mapInfo');
        const mapDiv = document.getElementById('map');

        // Show modal and spinner
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        spinner.classList.remove('hidden');
        mapInfo.textContent = `Fetching location for IP: ${ip} (${timestamp})`;

        try {
            // Fetch IP location data
            const res = await fetch(`https://ipapi.co/${ip}/json/`);
            const data = await res.json();

            const lat = data.latitude;
            const lon = data.longitude;
            const city = data.city || 'Unknown';
            const region = data.region || '';
            const country = data.country_name || '';

            spinner.classList.add('hidden');

            if (!lat || !lon) {
                mapInfo.textContent = `No location data available for IP: ${ip}`;
                return;
            }

            mapInfo.textContent = `Approximate Location: ${city}, ${region}, ${country}`;

            // ✅ Wait for modal to become visible before initializing map
            setTimeout(() => {
                mapDiv.innerHTML = ''; // reset

                // Initialize map properly
                map = new google.maps.Map(mapDiv, {
                    center: { lat: lat, lng: lon },
                    zoom: 13,
                    mapTypeId: 'roadmap'
                });

                const marker = new google.maps.Marker({
                    position: { lat: lat, lng: lon },
                    map: map,
                    title: `${city}, ${region}, ${country}`
                });

                const infoWindow = new google.maps.InfoWindow({
                    content: `<b>${ip}</b><br>${city}, ${region}, ${country}`
                });
                infoWindow.open(map, marker);

                // ✅ Important fix: trigger resize event so tiles appear
                google.maps.event.trigger(map, 'resize');
                map.setCenter({ lat: lat, lng: lon });
            }, 300); // wait for modal animation
        } catch (err) {
            console.error(err);
            spinner.classList.add('hidden');
            mapInfo.textContent = "Unable to fetch location for this IP.";
        }
    }

    // Close modal
    document.getElementById('closeMap').addEventListener('click', () => {
        const modal = document.getElementById('mapModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    });
    navigator.geolocation.getCurrentPosition(pos => {
    console.log("User actual location:", pos.coords.latitude, pos.coords.longitude);
});
</script>


</x-layouts.app>
