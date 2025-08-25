<x-layouts.app :title="'Edit Item'">
    <style>
        .edit-container {
            max-width: 700px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            font-family: Arial, sans-serif;
        }

        .edit-container h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 25px;
            color: #333;
        }

        form label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            color: #333;
        }

        form input[type="text"],
        form input[type="number"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .btn-submit {
            background-color: #007bff;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
        }

        .btn-submit:hover {
            background-color: #0056b3;
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            margin-left: 10px;
            cursor: pointer;
        }

        .btn-cancel:hover {
            background-color: #5a6268;
        }

        .form-actions {
            margin-top: 20px;
        }
    </style>

    <div class="edit-container">
        <h1>Edit Item</h1>

        <form action="{{ route('items.update', $item->id) }}" method="POST">
            @csrf
            @method('PUT')

            <label for="classification">Classification</label>
            <input type="text" id="classification" name="classification" value="{{ $item->classification }}" required>

            <label for="description">General Description</label>
            <input type="text" id="description" name="description" value="{{ $item->description }}" required>

            <label for="unit">Unit</label>
            <input type="text" id="unit" name="unit" value="{{ $item->unit }}" required>

            <label for="price">Price</label>
            <input type="number" id="price" name="price" value="{{ $item->price }}" step="0.01" required>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Update</button>
                <a href="{{ route('items.index') }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.app>
