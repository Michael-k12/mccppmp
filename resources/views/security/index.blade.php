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
<tr class="hover:bg-gray-50 cursor-pointer"
    onclick="showMap('{{ $log['ip'] ?? '' }}')">
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
            <!-- Map Modal -->
<div id="mapModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-11/12 md:w-2/3 lg:w-1/2 p-4 relative">
        <h3 class="text-lg font-bold mb-2">Login Location</h3>
        <div id="map" class="w-full h-64 rounded-md"></div>

        <button id="closeMap" class="absolute top-2 right-2 text-gray-600 hover:text-red-600">
            ✕
        </button>
    </div>
</div>

        </div>
    </div>

    <style>
        /* Improve mobile view */
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
    <!-- Leaflet.js CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

</x-layouts.app>
