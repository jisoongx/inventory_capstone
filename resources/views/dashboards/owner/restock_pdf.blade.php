<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Restock List</title>
<style>
@page {
    size: 58mm auto;
    margin: 0;
}
body {
    font-family: monospace;
    width: 58mm;
    margin: 0;
    font-size: 12px;
    line-height: 1.2;
}
h2 {
    text-align: center;
    font-size: 13px;
    margin: 5px 0;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    padding: 2px 0;
    text-align: left;
}
.total-row td {
    font-weight: bold;
    border-top: 1px dashed #000;
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
            <th>Qty</th>
            <th>Cost</th>
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
            <td>{{ number_format($item['cost_price'], 2) }}</td>
            <td>{{ number_format($item['subtotal'], 2) }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="3">TOTAL</td>
            <td>{{ number_format($grandTotal, 2) }}</td>
        </tr>
    </tbody>
</table>
</body>
</html>
