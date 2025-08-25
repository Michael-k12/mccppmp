<x-layouts.app.sidebar :title="$title ?? null">

    <flux:main>
        {{ $slot }}
    </flux:main>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</x-layouts.app.sidebar>
