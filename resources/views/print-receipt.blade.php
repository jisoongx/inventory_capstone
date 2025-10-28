<!DOCTYPE html>
<html>
<head>
    <title>Print Receipt</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
        
        body {
            font-family: 'Courier New', monospace;
            width: 300px;
            margin: 20px auto;
            padding: 10px;
        }
        
        .receipt {
            border: 1px solid #ddd;
            padding: 20px;
        }
        
        h3 {
            text-align: center;
            margin: 0 0 10px 0;
            font-size: 18px;
        }
        
        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        
        .receipt-info {
            font-size: 12px;
            margin-bottom: 10px;
        }
        
        .item {
            font-size: 12px;
            margin: 5px 0;
        }
        
        .item-name {
            font-weight: bold;
        }
        
        .item-details {
            padding-left: 10px;
        }
        
        .total {
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
            text-align: right;
        }
        
        .footer {
            text-align: center;
            font-size: 12px;
            margin-top: 15px;
        }
        
        .print-button {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin: 20px auto;
            display: block;
        }
        
        .print-button:hover {
            background: #2563eb;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ Choose Printer & Print</button>
    
    <div class="receipt">
        <h3>SHOPLYTIX</h3>
        <div class="divider"></div>
        
        <div class="receipt-info">
            <div>Receipt #: {{ $receipt->receipt_id }}</div>
            <div>Date: {{ $receipt->receipt_date }}</div>
        </div>
        
        <div class="divider"></div>
        
        @foreach($items as $item)
        <div class="item">
            <div class="item-name">{{ $item->prod_name }}</div>
            <div class="item-details">
                {{ $item->item_quantity }} x ₱{{ number_format($item->selling_price, 2) }} = 
                ₱{{ number_format($item->selling_price * $item->item_quantity, 2) }}
            </div>
        </div>
        @endforeach
        
        <div class="divider"></div>
        
        <div class="total">
            TOTAL: ₱{{ number_format($receipt->total_amount, 2) }}
        </div>
        
        <div class="divider"></div>
        
        <div class="footer">
            Thank you for shopping!<br>
            Please come again
        </div>
    </div>
    
    <script>
        // Auto-open print dialog when page loads
        window.onload = function() {
            // Give the page a moment to render
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>