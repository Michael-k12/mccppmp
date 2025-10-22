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
</x-layouts.app>
