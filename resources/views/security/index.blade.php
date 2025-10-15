<x-layouts.app :title="'Security Monitoring'">
    <div class="p-6">
        <h2 class="text-2xl font-bold mb-4">Monitoring Logs</h2>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-4 py-2 text-left">Timestamp</th>
                        <th class="border px-4 py-2 text-left">Type</th>
                        <th class="border px-4 py-2 text-left">Message</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="border px-4 py-2">{{ $log['timestamp'] ?? 'N/A' }}</td>
                            <td class="border px-4 py-2 font-semibold text-blue-600">{{ $log['type'] }}</td>
                            <td class="border px-4 py-2">{{ $log['message'] }}</td>
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
</x-layouts.app>
