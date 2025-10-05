<x-layouts.app :title="'Security Monitoring'">
    <div class="container mx-auto px-6 py-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            <x-icon name="shield-check" class="w-6 h-6 text-green-600" />
            System Security Monitoring
        </h2>

        <div id="report-container">
            @include('principal._security_reports', ['reports' => $reports])
        </div>
    </div>

    <script>
        // Poll every 30 seconds
        setInterval(() => {
            fetch("{{ route('security.fetch') }}")
                .then(response => response.text())
                .then(html => {
                    document.getElementById('report-container').innerHTML = html;
                })
                .catch(err => console.error('Error fetching reports:', err));
        }, 30000); // 30,000ms = 30 seconds
    </script>
</x-layouts.app>
