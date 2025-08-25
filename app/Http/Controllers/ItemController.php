<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;

class ItemController extends Controller
{
    public function index()
{
    $items = Item::all(); // assuming your model is App\Models\Item
    return view('items.index', compact('items'));
}
   public function storeToPPMP(Request $request)
{
    $validated = $request->validate([
        'classification' => 'required|string|max:255',
        'description' => 'required|string|max:255',
        'unit' => 'required|string|max:50',
        'price' => 'required|numeric|min:0',
        'quantity' => 'required|integer|min:1',
        'estimated_budget' => 'required|numeric|min:0',
        'mode_of_procurement' => 'required|string|max:255',
        'department' => 'required|string|max:255',
    ]);

    \DB::table('ppmp_items')->insert([
        'classification' => $validated['classification'],
        'description' => $validated['description'],
        'unit' => $validated['unit'],
        'price' => $validated['price'],
        'quantity' => $validated['quantity'],
        'estimated_budget' => $validated['estimated_budget'],
        'mode_of_procurement' => $validated['mode_of_procurement'],
        'department' => $validated['department'],
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return back()->with('success', 'Item added to PPMP successfully!');
}
public function store(Request $request)
{
    $validated = $request->validate([
        'classification' => 'required|string|max:255',
        'description'    => [
            'required',
            'string',
            'max:255',
            'unique:items,description',
        ],
        'unit'           => 'required|string|max:50',
        'price'          => 'required|numeric|min:0',
    ], [
        'description.unique' => 'Duplicate Item',
    ]);

    Item::create($validated);

    return redirect()->back()->with('success', 'Item added successfully!');
}


public function edit($id)
{
    $item = Item::findOrFail($id);
    return view('items.edit', compact('item'));
}
public function destroy($id)
{
    $item = Item::findOrFail($id);
    $item->delete();

    return redirect()->route('items.index')->with('success', 'Item deleted successfully.');
}
public function update(Request $request, $id)
{
    // Only validate price
    $request->validate([
        'price' => 'required|numeric|min:0',
    ]);

    $item = Item::findOrFail($id);
    $item->update([
        'price' => $request->price,
    ]);

    return redirect()->route('items.index')->with('success', 'Price updated successfully.');
}

}
