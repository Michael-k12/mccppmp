<x-layouts.app :title="'PPMP List'">
@if(session('success'))
    <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

    <div class="max-w-6xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">PPMP Requests</h1>
        <table class="min-w-full bg-white border">
            <thead>
                <tr>
                    <th class="border px-4 py-2">ID</th>
                    <th class="border px-4 py-2">Classification</th>
                    <th class="border px-4 py-2">Description</th>
                    <th class="border px-4 py-2">Unit</th>
                    <th class="border px-4 py-2">Price</th>
                    <th class="border px-4 py-2">Quantity</th>
                    <th class="border px-4 py-2">Budget</th>
                    <th class="border px-4 py-2">Milestone</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ppmps as $ppmp)
                    <tr>
                        <td class="border px-4 py-2">{{ $ppmp->id }}</td>
                        <td class="border px-4 py-2">{{ $ppmp->classification }}</td>
                        <td class="border px-4 py-2">{{ $ppmp->description }}</td>
                        <td class="border px-4 py-2">{{ $ppmp->unit }}</td>
                        <td class="border px-4 py-2">{{ $ppmp->price }}</td>
                        <td class="border px-4 py-2">{{ $ppmp->quantity }}</td>
                        <td class="border px-4 py-2">{{ $ppmp->estimated_budget }}</td>
                        <td class="border px-4 py-2">{{ $ppmp->milestone_date }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layouts.app>
