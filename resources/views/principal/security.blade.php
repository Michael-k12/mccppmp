<x-layouts.app :title="'Security Monitoring'">
    <div class="container mx-auto px-6 py-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            <x-icon name="shield-check" class="w-6 h-6 text-green-600" />
            System Security Monitoring
        </h2>

        @if($reports->isEmpty())
            <div class="p-6 bg-yellow-50 border border-yellow-300 rounded-xl">
                <p class="text-yellow-700">⚠️ No Wapiti reports found. Automated scans may not have been set up yet.</p>
            </div>
        @else
            <div class="bg-white shadow rounded-xl overflow-hidden">
                <table class="min-w-full border-collapse">
                    <thead class="bg-gray-100 text-gray-700 uppercase text-sm font-semibold">
                        <tr>
                            <th class="px-6 py-3 text-left">Report File</th>
                            <th class="px-6 py-3 text-left">Last Modified</th>
                            <th class="px-6 py-3 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($reports as $file)
    <tr>
        <td>{{ $file->getFilename() }}</td>
        <td>{{ date('Y-m-d H:i:s', $file->getMTime()) }}</td>
        <td>
            <a href="{{ asset('wapiti-reports/'.$file->getFilename()) }}" target="_blank">
                View Report
            </a>
        </td>
    </tr>
@endforeach

                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-layouts.app>
