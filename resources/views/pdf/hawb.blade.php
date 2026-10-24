<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>HAWB - {{ $shipment->hawb_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            line-height: 1.3;
            color: #1a1a1a;
            padding: 8px;
        }
        
        .container {
            max-width: 105mm;
            margin: 0 auto;
        }
        
        /* Header */
        .header {
            text-align: center;
            border-bottom: 2px solid #0A2540;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        
        .company-name {
            font-size: 14pt;
            font-weight: bold;
            color: #0A2540;
        }
        
        .company-tagline {
            font-size: 6pt;
            color: #666;
        }
        
        .hawb-title {
            background: #0A2540;
            color: white;
            padding: 4px;
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            margin: 6px 0;
            border-radius: 2px;
        }
        
        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            margin-bottom: 8px;
        }
        
        .info-box {
            border: 1px solid #ddd;
            padding: 5px;
            border-radius: 4px;
            background: #f9f9f9;
        }
        
        .info-label {
            font-weight: bold;
            color: #0A2540;
            font-size: 6pt;
            text-transform: uppercase;
        }
        
        .info-value {
            font-size: 9pt;
            font-weight: bold;
        }
        
        /* Address Section */
        .address-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            margin-bottom: 8px;
        }
        
        .address-box {
            border: 1px solid #ddd;
            padding: 5px;
            border-radius: 4px;
        }
        
        .address-title {
            font-weight: bold;
            background: #f0f0f0;
            padding: 3px;
            margin: -5px -5px 5px -5px;
            text-align: center;
            font-size: 7pt;
        }
        
        /* Boxes */
        .boxes-section {
            margin-bottom: 8px;
        }
        
        .boxes-title {
            font-weight: bold;
            background: #00D2B6;
            color: white;
            padding: 3px;
            text-align: center;
            font-size: 7pt;
        }
        
        .box-item {
            border: 1px solid #eee;
            padding: 4px;
            margin-bottom: 4px;
            font-size: 7pt;
        }
        
        .box-header {
            font-weight: bold;
            background: #f5f5f5;
            padding: 2px 4px;
            margin-bottom: 3px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6pt;
        }
        
        .items-table th, .items-table td {
            border: 1px solid #ddd;
            padding: 2px;
            text-align: left;
        }
        
        .items-table th {
            background: #f5f5f5;
        }
        
        /* Codes */
        .codes-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            margin: 8px 0;
            text-align: center;
        }
        
        .code-box {
            border: 1px solid #ddd;
            padding: 5px;
            border-radius: 4px;
        }
        
        .code-label {
            font-size: 6pt;
            color: #666;
            margin-top: 3px;
        }
        
        .qr-code img, .barcode img {
            max-width: 100%;
            height: auto;
        }
        
        /* Footer */
        .footer {
            border-top: 1px solid #ddd;
            margin-top: 6px;
            padding-top: 5px;
            font-size: 5pt;
            text-align: center;
            color: #999;
        }
        
        .tracking-status {
            background: #f0f7ff;
            padding: 5px;
            border-radius: 4px;
            margin: 6px 0;
            text-align: center;
            font-size: 7pt;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">COURIER with NETPACK</div>
            <div class="company-tagline">International Logistics & Grocery Express</div>
            <div class="hawb-title">HOUSE AIR WAYBILL (HAWB)</div>
        </div>
        
        <!-- Main Info -->
        <div class="info-grid">
            <div class="info-box">
                <div class="info-label">HAWB NUMBER</div>
                <div class="info-value">{{ $shipment->hawb_number }}</div>
            </div>
            <div class="info-box">
                <div class="info-label">TRACKING NUMBER</div>
                <div class="info-value">{{ $shipment->tracking_number }}</div>
            </div>
            <div class="info-box">
                <div class="info-label">DATE</div>
                <div class="info-value">{{ $shipment->created_at->format('Y-m-d') }}</div>
            </div>
            <div class="info-box">
                <div class="info-label">SERVICE</div>
                <div class="info-value">{{ strtoupper($shipment->service_type) }}</div>
            </div>
        </div>
        
        <!-- Addresses -->
        <div class="address-section">
            <div class="address-box">
                <div class="address-title">SHIPPER (NEPAL)</div>
                <div><strong>{{ $shipment->sender_name }}</strong></div>
                <div>{{ $shipment->sender_address }}</div>
                <div>{{ $shipment->sender_city }}, Nepal</div>
                <div>Tel: {{ $shipment->sender_phone }}</div>
            </div>
            <div class="address-box">
                <div class="address-title">CONSIGNEE</div>
                <div><strong>{{ $shipment->receiver_name }}</strong></div>
                <div>{{ $shipment->receiver_address }}</div>
                <div>{{ $shipment->receiver_city }}, {{ $shipment->receiver_country }}</div>
                <div>Tel: {{ $shipment->receiver_phone }}</div>
            </div>
        </div>
        
        <!-- Weight & Package Specifications -->
        <div class="info-grid">
            <div class="info-box">
                <div class="info-label">WEIGHT SPECIFICATION</div>
                <div>Actual: {{ $shipment->actual_weight }} kg</div>
                <div>Chargeable: {{ $shipment->chargeable_weight }} kg</div>
            </div>
            <div class="info-box">
                <div class="info-label">PACKAGE SPECIFICATION</div>
                <div>Type: {{ strtoupper($shipment->package_type ?? 'PARCEL') }}</div>
                <div><strong>Pieces: {{ $shipment->boxes ?? 1 }} PKG</strong></div>
            </div>
        </div>
        
        <!-- Box Contents -->
        @if(!empty($boxes))
        <div class="boxes-section">
            <div class="boxes-title">📦 BOX CONTENTS</div>
            @foreach($boxes as $index => $box)
            @if(!empty($box))
            <div class="box-item">
                <div class="box-header">Box {{ $index + 1 }}</div>
                <table class="items-table">
                    <thead>
                        <tr><th>Product</th><th>Weight</th><th>Quantity</th></tr>
                    </thead>
                    <tbody>
                        @foreach($box as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td>{{ $item['weight'] }} kg</td>
                            <td>{{ $item['quantity'] ?? '1 Unit' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
            @endforeach
        </div>
        @endif
        
        <!-- Tracking -->
        <div class="tracking-status">
            Track at: track.couriernetpack.com/{{ $shipment->tracking_number }}
        </div>
        
        <!-- QR & Barcode -->
        <div class="codes-section">
            <div class="code-box">
                <div class="qr-code"><img src="{{ $qrUrl }}" style="width: 70px;"></div>
                <div class="code-label">SCAN TO TRACK</div>
            </div>
            <div class="code-box">
                <div class="barcode"><img src="{{ $barcodeUrl }}" style="width: 100%;"></div>
                <div class="code-label">{{ $shipment->hawb_number }}</div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            Generated: {{ $generated_at }} | Support: support@couriernetpack.com
        </div>
    </div>
</body>
</html>
