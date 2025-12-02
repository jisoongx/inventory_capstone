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
            padding: 5mm;
            font-size: 10px;
            line-height: 1.4;
        }

        h2 {
            text-align: center;
            font-size: 14px;
            margin: 0 0 8px 0;
            text-transform: uppercase;
        }

        .header-info {
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px dashed #000;
        }

        .header-info p {
            margin: 2px 0;
        }

        .item-row {
            border-bottom: 1px dashed #000;
            padding: 5px 0;
            margin-bottom: 3px;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .item-details {
            font-size: 9px;
            color: #333;
            margin: 2px 0;
        }

        .item-price-row {
            margin-top: 3px;
            width: 100%;
        }

        .item-price-row table {
            width: 100%;
            border: none;
        }

        .item-price-row td {
            padding: 0;
            border: none;
        }

        .item-price-row td:first-child {
            text-align: left;
        }

        .item-price-row td:last-child {
            text-align: right;
            font-weight: bold;
        }

        .total-section {
            margin-top: 8px;
            padding-top: 5px;
            border-top: 2px solid #000;
        }

        .total-row {
            width: 100%;
        }

        .total-row table {
            width: 100%;
            border: none;
        }

        .total-row td {
            padding: 3px 0;
            font-weight: bold;
            font-size: 12px;
            border: none;
        }

        .total-row td:first-child {
            text-align: left;
        }

        .total-row td:last-child {
            text-align: right;
        }
    </style>
</head>

<body>
    <h2>Restock List</h2>

    <div class="header-info">
        <p><strong>Date:</strong> {{ $restock_created }}</p>
    </div>

    <div class="items-section">
        @php $grandTotal = 0; @endphp
        @foreach($items as $item)
        @php $grandTotal += (float) $item['subtotal']; @endphp

        <div class="item-row">
            <div class="item-name">{{ $item['name'] }}</div>
            <div class="item-details">
                Qty: {{ $item['quantity'] }}
            </div>
            <div class="item-details">
                Cost: {{ number_format($item['cost_price'], 2) }}
            </div>
            <div class="item-details">
                Status: {{ $item['item_status'] }}
            </div>
            <div class="item-details">
                Restock Date: {{ $item['item_restock_date'] }}
            </div>
            <div class="item-price-row">
                <table>
                    <tr>
                        <td>Subtotal</td>
                        <td>{{ number_format($item['subtotal'], 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        @endforeach
    </div>

    <div class="total-section">
        <div class="total-row">
            <table>
                <tr>
                    <td>GRAND TOTAL</td>
                    <td>{{ number_format($grandTotal, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>