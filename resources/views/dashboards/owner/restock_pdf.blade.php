<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Restock List</title>
    <style>
        body {
            font-family: sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f1f1f1;
        }

        h2 {
            margin-bottom: 10px;
        }

        .total-row td {
            font-weight: bold;
            background: #f9f9f9;
        }
    </style>
</head>

<body>
    <h2>Restock List</h2>
    <p><strong>Date:</strong> {{ $restock_created }}</p>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Cost Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($items as $item)
            @php $grandTotal += (float) $item['subtotal']; @endphp
            <tr>
                <td>{{ $item['name'] }}</td>
                <td>{{ $item['quantity'] }}</td>
                <td>{{ number_format((float) $item['cost_price'], 2) }}</td>
                <td>{{ number_format((float) $item['subtotal'], 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3">Total</td>
                <td>{{ number_format($grandTotal, 2) }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>