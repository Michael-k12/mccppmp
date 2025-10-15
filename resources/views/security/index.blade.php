<x-layouts.app :title="'Monitoring Logs'">
    <div class="container mx-auto px-6 py-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Security & Monitoring Logs</h2>

        @if(empty($logs))
            <div class="bg-yellow-50 text-yellow-700 p-4 rounded-lg">
                No logs yet — your system is quiet.
            </div>
        @else
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="min-w-full table-auto border-collapse">
                    <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Timestamp</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $line)
                            @php
                                $isSuspicious = str_contains($line, 'Unauthorized') || str_contains($line, 'Failed');
                            @endphp
                            <tr class="{{ $isSuspicious ? 'bg-red-50' : 'bg-white' }} border-b hover:bg-gray-50">
                                <td class="px-4 py-2 text-sm text-gray-600">
                                    {{ strtok($line, ']') . ']' }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-800">
                                    {{ substr($line, strpos($line, '] ') + 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="mt-4 flex justify-end">
            <form method="POST" action="{{ route('security.clear') }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                    Clear Logs
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>
