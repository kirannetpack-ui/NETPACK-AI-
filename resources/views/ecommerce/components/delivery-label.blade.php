{{-- resources/views/ecommerce/components/delivery-label.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delivery Label - {{ $order->order_reference }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .label {
            width: 4in;
            height: 6in;
            border: 2px solid #333;
            padding: 10px;
            page-break-after: avoid;
        }
        .header {
            text-align: center;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .company {
            font-size: 18px;
            font-weight: bold;
            color: #0A2540;
        }
        .qr-code {
            text-align: center;
            margin: 10px 0;
        }
        .section {
            margin: 10px 0;
        }
        .section-title {
            font-weight: bold;
            background: #f0f0f0;
            padding: 3px;
            font-size: 10px;
        }
        .address {
            font-size: 10px;
            margin: 5px 0;
        }
        .barcode {
            text-align: center;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="label">
        <div class="header">
            <div class="company">NETPACK COURIER</div>
            <div style="font-size: 8px;">E-commerce Delivery Label</div>
        </div>
        
        <div class="qr-code">
            <img src="{{ $order->qr_code }}" width="100">
        </div>
        
        <div class="section">
            <div class="section-title">ORDER INFORMATION</div>
            <div style="font-size: 9px;">
                <strong>Order #:</strong> {{ $order->order_reference }}<br>
                <strong>Platform:</strong> {{ ucfirst($order->platform) }}<br>
                <strong>Service:</strong> {{ strtoupper($order->service_tier) }}<br>
                <strong>Weight:</strong> {{ $order->estimated_weight_kg }} kg
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">FROM (SELLER)</div>
            <div class="address">
                {{ $order->pickup_address }}<br>
                Ward {{ $order->pickup_ward_no }}, {{ $order->pickup_municipality }}<br>
                {{ $order->pickup_district }}, Nepal
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">TO (CUSTOMER)</div>
            <div class="address">
                {{ $order->customer_name }}<br>
                {{ $order->delivery_address }}<br>
                Ward {{ $order->delivery_ward_no }}, {{ $order->delivery_municipality }}<br>
                {{ $order->delivery_district }}, Nepal<br>
                <strong>Phone:</strong> {{ $order->customer_phone }}
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">PAYMENT</div>
            <div style="font-size: 9px;">
                <strong>Amount:</strong> रू {{ number_format($order->cod_amount, 2) }}<br>
                <strong>Type:</strong> {{ strtoupper($order->payment_status) }}
            </div>
        </div>
        
        <div class="barcode">
            <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($order->id, 'C128', 1, 30) }}" width="200">
        </div>
        
        <div style="font-size: 7px; text-align: center; margin-top: 10px;">
            Scan QR code to track | Handle with care
        </div>
    </div>
</body>
</html>