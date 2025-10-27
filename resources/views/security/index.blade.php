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
                            <tr class="hover:bg-gray-50 cursor-pointer"
                                onclick="showMap('{{ $ip }}', '{{ $log['timestamp'] ?? 'Unknown time' }}', '{{ addslashes($log['message']) }}')">
                                <td class="border px-4 py-2 text-gray-800 text-sm">
                                    {{ $log['timestamp'] ?? 'N/A' }}
                                </td>
                                <td class="border px-4 py-2 font-semibold text-blue-600 text-sm">
                                    {{ $log['type'] }}
                                </td>
                                <td class="border px-4 py-2 text-gray-700 text-sm">
                                    {{ $log['message'] }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-gray-500">
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

    <!-- 🌍 Leaflet.js -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

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
        async function showMap(ip, timestamp, message) {
            if (!ip) {
                alert("No IP address found for this log.");
                return;
            }

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

                // Reset map
                mapDiv.innerHTML = '';

                const map = L.map(mapDiv).setView([lat, lon], 10);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                L.marker([lat, lon]).addTo(map)
                    .bindPopup(`<b>${ip}</b><br>${city}, ${region}, ${country}`)
                    .openPopup();

                mapInfo.textContent = `Approximate Location: ${city}, ${region}, ${country}`;
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
    </script>

</x-layouts.app>
