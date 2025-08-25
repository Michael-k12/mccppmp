<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProcurementItem; // Import your model

class ProcurementItemController extends Controller
{
    

    public function store(Request $request)
    {
        $validated = $request->validate([
            'classification' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
        ]);

        ProcurementItem::create($validated);

        return redirect()->route('dashboard')->with('success', 'Item added successfully.');
    }
}


