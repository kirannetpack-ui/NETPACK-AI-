<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domestic Waybill - {{ $shipment->tracking_number }} - COURIER with NETPACK</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 6mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            font-family: 'Helvetica Neue', Arial, 'Segoe UI', sans-serif;
            margin: 0;
            padding: 4mm;
            background: #e2e8f0;
            color: #0f172a;
            font-size: 11px;
        }
        .hawb-sheet {
            max-width: 200mm;
            margin: 0 auto;
            background: #ffffff;
            padding: 5mm;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .hawb-unit {
            border: 2px solid #0f172a;
            border-radius: 4px;
            padding: 4mm;
            margin-bottom: 5mm;
            page-break-inside: avoid;
            background: #ffffff;
            position: relative;
        }
        .hawb-unit:last-child {
            margin-bottom: 0;
        }

        /* Header Bar */
        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #0d9488;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .brand-title {
            font-size: 18px;
            font-weight: 800;
            color: #0d9488;
            text-transform: uppercase;
        }
        .brand-title span {
            color: #0f172a;
        }
        .badge-pill {
            background: #0d9488;
            color: #ffffff;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .copy-tag {
            font-size: 9px;
            font-weight: 700;
            color: #475569;
            background: #f1f5f9;
            padding: 2px 8px;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
        }

        /* Waybill & COD Bar */
        .waybill-row {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 8px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 6px 8px;
            margin-bottom: 6px;
        }
        .id-label {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.5px;
        }
        .waybill-val {
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: 1.5px;
        }
        .cod-badge {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-end;
        }
        .cod-amount {
            font-size: 15px;
            font-weight: 800;
            color: #b91c1c;
        }
        .prepaid-amount {
            font-size: 14px;
            font-weight: 800;
            color: #047857;
        }

        /* Routing Grid */
        .routing-strip {
            display: grid;
            grid-template-columns: 1.2fr 1.2fr 1fr 1fr;
            gap: 6px;
            margin-bottom: 6px;
        }
        .route-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 4px 6px;
        }
        .route-card .card-val {
            font-size: 11px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 1px;
        }

        /* QR + Specs */
        .mid-section {
            display: grid;
            grid-template-columns: 96px 1fr;
            gap: 8px;
            margin-bottom: 6px;
        }
        .qr-box {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            background: #fafafa;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4px;
            text-align: center;
        }
        .qr-box img {
            width: 84px;
            height: 84px;
            display: block;
        }
        .qr-sub {
            font-size: 7px;
            font-weight: 600;
            color: #64748b;
            margin-top: 2px;
            text-transform: uppercase;
        }

        .specs-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 5px;
        }
        .spec-item {
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 4px 6px;
            background: #f8fafc;
        }
        .spec-item .val {
            font-size: 11px;
            font-weight: 700;
            color: #0f172a;
        }

        /* Parties */
        .parties-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 6px;
        }
        .party-pane {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 5px 7px;
            background: #ffffff;
        }
        .party-pane.shipper {
            border-left: 3px solid #0d9488;
        }
        .party-pane.consignee {
            border-left: 3px solid #2563eb;
            background: #f8fafc;
        }
        .party-tag {
            font-size: 8px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .party-pane.shipper .party-tag { color: #0d9488; }
        .party-pane.consignee .party-tag { color: #2563eb; }
        .party-name {
            font-size: 11px;
            font-weight: 700;
            color: #0f172a;
        }
        .party-desc {
            font-size: 9px;
            color: #334155;
            line-height: 1.35;
            margin-top: 2px;
        }
        .party-phone {
            font-size: 9px;
            font-weight: 600;
            color: #475569;
            margin-top: 3px;
        }

        /* POD Signature Area */
        .pod-row {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1fr;
            gap: 6px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 4px 6px;
            margin-bottom: 4px;
            background: #ffffff;
        }
        .pod-field {
            font-size: 8px;
            color: #475569;
        }
        .pod-line {
            border-bottom: 1px dotted #94a3b8;
            height: 16px;
            margin-top: 2px;
        }

        .unit-footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 3px;
            font-size: 7.5px;
            color: #64748b;
            display: flex;
            justify-content: space-between;
        }

        .cut-divider {
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            font-family: monospace;
            padding: 3px 0;
            margin-bottom: 5mm;
            border-bottom: 1px dashed #cbd5e1;
        }

        /* Print Controls */
        .print-actions {
            max-width: 200mm;
            margin: 12px auto;
            text-align: center;
            display: flex;
            justify-content: center;
            gap: 12px;
        }
        .btn-print {
            background: #0d9488;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
        }
        .btn-print:hover { background: #0f766e; }
        .btn-back {
            background: #1e293b;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .hawb-sheet {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .print-actions {
                display: none !important;
            }
            .hawb-unit {
                border-color: #000000;
                margin-bottom: 4mm;
            }
        }
    </style>
</head>
<body>

@php
    $weightVal = number_format($shipment->actual_weight ?? $shipment->chargeable_weight ?? $shipment->weight ?? 0, 2);
    $serviceName = strtoupper($shipment->service_name ?? $shipment->service_type ?? 'Standard Express');
@endphp

<div class="print-actions">
    <button onclick="window.print()" class="btn-print">🖨 Print Domestic Consignment Note (A4 · 2 Copies)</button>
    <a href="{{ route('tracking.show', $shipment->tracking_number) }}" class="btn-back">📦 Public Tracking</a>
</div>

<div class="hawb-sheet">

    @foreach(['COPY 1 · CONSIGNEE DELIVERY RUNSHEET COPY', 'COPY 2 · PROOF OF DELIVERY (POD) CARRIER COPY'] as $copyTitle)
    <div class="hawb-unit">
        <!-- Header -->
        <div class="header-bar">
            <div class="brand-logo">
                <span class="brand-title">COURIER with <span>NETPACK</span></span>
                <span class="badge-pill">{{ ($shipment->shipment_type ?? '') === 'ecommerce' ? 'E-Commerce Logistics' : 'Domestic Express Logistics' }}</span>
            </div>
            <div class="copy-tag">{{ $copyTitle }}</div>
        </div>

        <!-- Waybill Identifier & Classification -->
        <div class="waybill-row">
            <div>
                <span class="id-label">{{ ($shipment->shipment_type ?? '') === 'ecommerce' ? 'E-Commerce HAWB / Dispatch Number' : 'Domestic Consignment HAWB Number' }}</span>
                <span class="waybill-val">{{ $shipment->hawb_number ?: $shipment->tracking_number }}</span>
                <div style="font-size: 8px; color: #64748b; font-family: monospace; margin-top: 2px;">
                    Track Ref: {{ $shipment->tracking_number }}
                </div>
            </div>
            <div class="cod-badge">
                <span class="id-label">Consignment Classification</span>
                @if(!empty($shipment->is_cod) && (float)($shipment->cod_amount ?? 0) > 0)
                    <span class="cod-amount">C.O.D. CASH ON DELIVERY: {{ number_format((float)$shipment->cod_amount, 2) }}</span>
                @else
                    <span class="prepaid-amount">OFFICIAL FREIGHT MANIFEST</span>
                @endif
            </div>
        </div>

        <!-- Routing -->
        <div class="routing-strip">
            <div class="route-card">
                <div class="id-label">Origin Zone / City</div>
                <div class="card-val">{{ $shipment->sender_city ?? 'Kathmandu' }}</div>
            </div>
            <div class="route-card">
                <div class="id-label">Destination City</div>
                <div class="card-val">{{ $shipment->receiver_city ?? 'Nepal Destination' }}</div>
            </div>
            <div class="route-card">
                <div class="id-label">Service Tier</div>
                <div class="card-val">{{ $serviceName }}</div>
            </div>
            <div class="route-card">
                <div class="id-label">Assigned Partner / Hub</div>
                <div class="card-val">{{ $shipment->partner->name ?? 'NETPACK Central Hub' }}</div>
            </div>
        </div>

        <!-- QR + Specs -->
        <div class="mid-section">
            <div class="qr-box">
                {!! $qrCode !!}
                <span class="qr-sub">Scan to Track</span>
            </div>
            <div class="specs-grid">
                <div class="spec-item">
                    <div class="id-label">Actual Weight</div>
                    <div class="val">{{ $weightVal }} KG</div>
                </div>
                <div class="spec-item">
                    <div class="id-label">Package Type</div>
                    <div class="val">{{ ucfirst($shipment->package_type ?? 'Parcel') }}</div>
                </div>
                <div class="spec-item">
                    <div class="id-label">Delivery Target</div>
                    <div class="val">{{ $shipment->estimated_delivery_at ? $shipment->estimated_delivery_at->format('M d · h:i A') : 'Same Day / 24H' }}</div>
                </div>
                <div class="spec-item">
                    <div class="id-label">Delivery Ward / Zone</div>
                    <div class="val">Ward: {{ $shipment->receiver_ward ?? 'N/A' }}</div>
                </div>
                <div class="spec-item">
                    <div class="id-label">Booking Date</div>
                    <div class="val">{{ $shipment->created_at->format('d M Y') }}</div>
                </div>
                <div class="spec-item">
                    <div class="id-label">Special Handling</div>
                    <div class="val">{{ $shipment->special_instructions ? substr($shipment->special_instructions, 0, 18) . '...' : 'Standard Fragile' }}</div>
                </div>
            </div>
        </div>

        <!-- Parties: Shipper & Consignee -->
        <div class="parties-row">
            <!-- Shipper -->
            <div class="party-pane shipper">
                <div class="party-tag">Sender / Consignor (Nepal)</div>
                <div class="party-name">{{ $shipment->sender_name ?? 'N/A' }}</div>
                <div class="party-desc">
                    {{ $shipment->sender_address ?? '' }}<br>
                    {{ $shipment->sender_city ?? 'Kathmandu' }}, Nepal
                </div>
                <div class="party-phone">📞 {{ $shipment->sender_phone ?? 'N/A' }}</div>
            </div>

            <!-- Consignee -->
            <div class="party-pane consignee">
                <div class="party-tag">Receiver / Consignee</div>
                <div class="party-name">{{ $shipment->receiver_name ?? 'N/A' }}</div>
                <div class="party-desc">
                    {{ $shipment->receiver_address ?? '' }}<br>
                    {{ $shipment->receiver_city ?? '' }} @if($shipment->receiver_zone)· {{ $shipment->receiver_zone }} @endif
                </div>
                <div class="party-phone">📞 {{ $shipment->receiver_phone ?? 'N/A' }}</div>
            </div>
        </div>

        <!-- POD Signature Bar -->
        <div class="pod-row">
            <div>
                <span class="pod-field">Recipient Received By (Name):</span>
                <div class="pod-line"></div>
            </div>
            <div>
                <span class="pod-field">Receiver Signature / Stamp:</span>
                <div class="pod-line"></div>
            </div>
            <div>
                <span class="pod-field">Date & Time of Delivery:</span>
                <div class="pod-line"></div>
            </div>
        </div>

        <div class="unit-footer">
            <span>COURIER with NETPACK Ltd. · Domestic Delivery Express · All 7 Provinces of Nepal</span>
            <span>Customer Service: +977-1-5970123 · support@couriernetpack.com</span>
        </div>
    </div>

    @if(!$loop->last)
        <div class="cut-divider">✂ — — — — — — — — CUT HERE FOR PROOF OF DELIVERY (POD) COPY — — — — — — — — ✂</div>
    @endif
    @endforeach

</div>

</body>
</html>
