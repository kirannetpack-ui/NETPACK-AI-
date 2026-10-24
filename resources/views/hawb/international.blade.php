<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HAWB - {{ $shipment->hawb_number ?? $shipment->tracking_number }} - COURIER with NETPACK</title>
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
            letter-spacing: 0.5px;
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

        /* Primary HAWB & Tracking Bar */
        .identifier-row {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 8px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 6px 8px;
            margin-bottom: 6px;
        }
        .hawb-number-box {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .id-label {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.5px;
        }
        .hawb-number-val {
            font-family: 'Courier New', monospace;
            font-size: 19px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: 2px;
        }
        .tracking-number-val {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            font-weight: 700;
            color: #0d9488;
            letter-spacing: 1px;
        }

        /* Routing Grid */
        .routing-strip {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
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

        /* QR + Package Specs */
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

        /* Parties: Shipper & Consignee */
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

        /* Footer & Signatures */
        .unit-footer {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 8px;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
            font-size: 7.5px;
            color: #64748b;
        }
        .sig-block {
            border: 1px dashed #cbd5e1;
            border-radius: 3px;
            padding: 3px 6px;
            text-align: right;
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
        .btn-print:hover {
            background: #0f766e;
        }
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
    $hawbNumber = $shipment->hawb_number ?: ('INNP-' . date('Y') . '-' . str_pad($shipment->id, 3, '0', STR_PAD_LEFT));
    $chargeableWeight = number_format($shipment->chargeable_weight ?? $shipment->actual_weight ?? $shipment->weight ?? 0, 2);
    $packageType = strtoupper($shipment->package_type ?? 'Parcel');
    $serviceType = strtoupper($shipment->service_type ?? 'Express');
@endphp

<div class="print-actions">
    <button onclick="window.print()" class="btn-print">🖨 Print House Air Waybill (A4 · 2 Copies)</button>
    <a href="{{ route('tracking.show', $shipment->tracking_number) }}" class="btn-back">📦 Public Tracking</a>
</div>

<div class="hawb-sheet">

    @foreach(['COPY 1 · CONSIGNEE / CARGO ATTACHMENT', 'COPY 2 · CUSTOMS & AIRLINE OPERATIONS'] as $copyTitle)
    <div class="hawb-unit">
        <!-- Header -->
        <div class="header-bar">
            <div class="brand-logo">
                <span class="brand-title">COURIER with <span>NETPACK</span></span>
                <span class="badge-pill">HOUSE AIR WAYBILL</span>
            </div>
            <div class="copy-tag">{{ $copyTitle }}</div>
        </div>

        <!-- HAWB & Tracking Identifiers -->
        <div class="identifier-row">
            <div class="hawb-number-box">
                <span class="id-label">House Air Waybill Number (HAWB)</span>
                <span class="hawb-number-val">{{ $hawbNumber }}</span>
            </div>
            <div>
                <span class="id-label">Master Carrier Tracking Number</span>
                <span class="tracking-number-val">{{ $shipment->tracking_number }}</span>
            </div>
        </div>

        <!-- Routing -->
        <div class="routing-strip">
            <div class="route-card">
                <div class="id-label">Origin Gateway</div>
                <div class="card-val">KTM · Kathmandu, Nepal</div>
            </div>
            <div class="route-card">
                <div class="id-label">Destination Country</div>
                <div class="card-val">{{ strtoupper($shipment->receiver_country) }}</div>
            </div>
            <div class="route-card">
                <div class="id-label">Destination City</div>
                <div class="card-val">{{ strtoupper($shipment->receiver_city) }}</div>
            </div>
            <div class="route-card">
                <div class="id-label">Service Level</div>
                <div class="card-val">{{ $serviceType }}</div>
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
                    <div class="id-label">Weight (Chg.)</div>
                    <div class="val">{{ $chargeableWeight }} KG</div>
                </div>
                <div class="spec-item">
                    <div class="id-label">Package Type</div>
                    <div class="val">{{ $packageType }}</div>
                </div>
                <div class="spec-item">
                    <div class="id-label">Pieces / Box</div>
                    <div class="val">{{ $shipment->boxes ?? 1 }} PKG</div>
                </div>
                <div class="spec-item">
                    <div class="id-label">Dimensions (L×W×H)</div>
                    <div class="val">
                        {{ $shipment->length ?? '-' }}×{{ $shipment->width ?? '-' }}×{{ $shipment->height ?? '-' }} cm
                    </div>
                </div>
                <div class="spec-item">
                    <div class="id-label">Booking Date</div>
                    <div class="val">{{ $shipment->created_at->format('d M Y') }}</div>
                </div>
                <div class="spec-item">
                    <div class="id-label">Overseas Hub</div>
                    <div class="val">{{ $shipment->overseasPartner->name ?? 'NETPACK Global Hub' }}</div>
                </div>
            </div>
        </div>

        <!-- Parties: Shipper & Consignee -->
        <div class="parties-row">
            <!-- Shipper -->
            <div class="party-pane shipper">
                <div class="party-tag">Shipper / Sender (Nepal)</div>
                <div class="party-name">{{ $shipment->sender_name ?? 'N/A' }}</div>
                <div class="party-desc">
                    {{ $shipment->sender_address ?? '' }}<br>
                    {{ $shipment->sender_city ?? 'Kathmandu' }}, {{ $shipment->sender_country ?? 'Nepal' }}
                </div>
                <div class="party-phone">📞 {{ $shipment->sender_phone ?? 'N/A' }}</div>
            </div>

            <!-- Consignee -->
            <div class="party-pane consignee">
                <div class="party-tag">Consignee / Destination Receiver</div>
                <div class="party-name">{{ $shipment->receiver_name ?? 'N/A' }}</div>
                <div class="party-desc">
                    {{ $shipment->receiver_address ?? '' }}<br>
                    {{ $shipment->receiver_city }}, {{ $shipment->receiver_state ?? '' }} {{ $shipment->receiver_postal_code ?? '' }}<br>
                    <strong>{{ strtoupper($shipment->receiver_country) }}</strong>
                </div>
                <div class="party-phone">📞 {{ $shipment->receiver_phone ?? 'N/A' }}</div>
            </div>
        </div>

        <!-- Unit Footer -->
        <div class="unit-footer">
            <div>
                <strong>COURIER with NETPACK Ltd.</strong> · Global Air Cargo Operations · Kathmandu, Nepal<br>
                Carriage subject to standard international air cargo liability and IATA regulations.
            </div>
            <div class="sig-block">
                Authorized Signature / Dispatch Stamp
            </div>
        </div>
    </div>

    @if(!$loop->last)
        <div class="cut-divider">✂ — — — — — — — — CUT HERE FOR DUPLICATE OPERATIONS COPY — — — — — — — — ✂</div>
    @endif
    @endforeach

</div>

</body>
</html>
