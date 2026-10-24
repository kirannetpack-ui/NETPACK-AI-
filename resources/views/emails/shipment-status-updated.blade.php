<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Air Cargo Telemetry Update</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed; background-color: #f1f5f9; padding: 30px 10px;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
                    
                    <!-- Top Brand Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0f172a 0%, #0d9488 100%); padding: 32px 36px text-align: left;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td>
                                        <div style="color: #ffffff; font-size: 22px; font-weight: 800; letter-spacing: -0.5px;">
                                            COURIER <span style="color: #5eead4; font-weight: 300;">by NETPACK</span>
                                        </div>
                                        <div style="color: #94a3b8; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px; margin-top: 4px;">
                                            Global Air Cargo Telemetry System &middot; IATA Certified
                                        </div>
                                    </td>
                                    <td align="right" valign="top">
                                        <span style="display: inline-block; background-color: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.25); color: #ffffff; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; text-transform: uppercase;">
                                            Live Update
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Status Hero Card -->
                    <tr>
                        <td style="padding: 32px 36px 20px 36px; background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td>
                                        <div style="color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
                                            Operational Milestone
                                        </div>
                                        <div style="color: #0f172a; font-size: 24px; font-weight: 800; margin-top: 4px; line-height: 1.2;">
                                            {{ $statusLabel }}
                                        </div>
                                        <div style="color: #334155; font-size: 14px; margin-top: 6px; line-height: 1.5;">
                                            {{ $eventDescription }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Telemetry Meta Badges -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 20px; background-color: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 14px 18px;">
                                <tr>
                                    <td width="50%" style="border-right: 1px solid #f1f5f9; padding-right: 12px;">
                                        <div style="color: #94a3b8; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Location / Checkpoint</div>
                                        <div style="color: #0f172a; font-size: 13px; font-weight: 700; margin-top: 3px;">
                                            📍 {{ $location }}
                                        </div>
                                    </td>
                                    <td width="50%" style="padding-left: 16px;">
                                        <div style="color: #94a3b8; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Timestamp (Recorded)</div>
                                        <div style="color: #0f172a; font-size: 13px; font-weight: 600; font-family: monospace; margin-top: 3px;">
                                            🕒 {{ $timestamp }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Air Transit Corridor Visual -->
                    <tr>
                        <td style="padding: 24px 36px 20px 36px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #0f172a; border-radius: 14px; padding: 18px 22px; color: #ffffff;">
                                <tr>
                                    <td width="40%">
                                        <div style="color: #5eead4; font-size: 10px; font-weight: 800; text-transform: uppercase;">Origin Gateway</div>
                                        <div style="color: #ffffff; font-size: 15px; font-weight: 800; margin-top: 2px;">
                                            {{ $shipment->sender_city ?: 'Kathmandu' }}, {{ $shipment->sender_country ?: 'Nepal' }}
                                        </div>
                                        <div style="color: #94a3b8; font-size: 11px;">TIA Airport (KTM)</div>
                                    </td>
                                    <td width="20%" align="center">
                                        <div style="color: #5eead4; font-size: 18px;">✈️</div>
                                        <div style="border-top: 1px dashed #334155; margin-top: 4px; width: 60%;"></div>
                                    </td>
                                    <td width="40%" align="right">
                                        <div style="color: #5eead4; font-size: 10px; font-weight: 800; text-transform: uppercase;">Destination Port</div>
                                        <div style="color: #ffffff; font-size: 15px; font-weight: 800; margin-top: 2px;">
                                            {{ $shipment->receiver_city ?: 'Destination' }}, {{ $shipment->receiver_country }}
                                        </div>
                                        <div style="color: #94a3b8; font-size: 11px;">Final Consignee Gateway</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Waybill Details Table -->
                    <tr>
                        <td style="padding: 10px 36px 28px 36px;">
                            <div style="color: #0f172a; font-size: 14px; font-weight: 800; margin-bottom: 12px;">Consignment Air Waybill Credentials</div>
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; font-size: 12px;">
                                <tr>
                                    <td style="padding: 9px 0; border-bottom: 1px solid #f1f5f9; color: #64748b;">Tracking Reference</td>
                                    <td style="padding: 9px 0; border-bottom: 1px solid #f1f5f9; font-weight: 800; font-family: monospace; color: #0f172a; text-align: right;">
                                        {{ $shipment->tracking_number }}
                                    </td>
                                </tr>
                                @if(!empty($shipment->hawb_number))
                                <tr>
                                    <td style="padding: 9px 0; border-bottom: 1px solid #f1f5f9; color: #64748b;">House Air Waybill (HAWB)</td>
                                    <td style="padding: 9px 0; border-bottom: 1px solid #f1f5f9; font-weight: 800; font-family: monospace; color: #0d9488; text-align: right;">
                                        {{ $shipment->hawb_number }}
                                    </td>
                                </tr>
                                @endif
                                @if(!empty($mawbNumber))
                                <tr>
                                    <td style="padding: 9px 0; border-bottom: 1px solid #f1f5f9; color: #64748b;">Master Air Waybill (MAWB)</td>
                                    <td style="padding: 9px 0; border-bottom: 1px solid #f1f5f9; font-weight: 800; font-family: monospace; color: #0369a1; text-align: right;">
                                        {{ $mawbNumber }}
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="padding: 9px 0; border-bottom: 1px solid #f1f5f9; color: #64748b;">Service Category</td>
                                    <td style="padding: 9px 0; border-bottom: 1px solid #f1f5f9; font-weight: 700; color: #0f172a; text-align: right;">
                                        {{ strtoupper($shipment->service_type ?? 'Express') }} &middot; {{ ucfirst($shipment->package_type ?? 'Parcel') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 9px 0; border-bottom: 1px solid #f1f5f9; color: #64748b;">Chargeable Weight</td>
                                    <td style="padding: 9px 0; border-bottom: 1px solid #f1f5f9; font-weight: 700; color: #0f172a; text-align: right;">
                                        {{ number_format($shipment->chargeable_weight ?? $shipment->actual_weight ?? 0, 2) }} KG
                                    </td>
                                </tr>
                                @if(!empty($carrierName))
                                <tr>
                                    <td style="padding: 9px 0; border-bottom: 1px solid #f1f5f9; color: #64748b;">Last-Mile Carrier</td>
                                    <td style="padding: 9px 0; border-bottom: 1px solid #f1f5f9; font-weight: 700; color: #0d9488; text-align: right;">
                                        {{ $carrierName }} {{ $carrierTracking ? "({$carrierTracking})" : '' }}
                                    </td>
                                </tr>
                                @endif
                                @if(!empty($notes))
                                <tr>
                                    <td style="padding: 9px 0; border-bottom: 1px solid #f1f5f9; color: #64748b;">Operational Remarks</td>
                                    <td style="padding: 9px 0; border-bottom: 1px solid #f1f5f9; color: #475569; text-align: right;">
                                        {{ $notes }}
                                    </td>
                                </tr>
                                @endif
                            </table>

                            <!-- Live Telemetry CTA Button -->
                            <div style="margin-top: 26px; text-align: center;">
                                <a href="{{ route('tracking.show', $shipment->tracking_number) }}" target="_blank" style="display: inline-block; background-color: #0d9488; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 800; padding: 14px 36px; border-radius: 12px; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.25);">
                                    Track Consignment Telemetry &rarr;
                                </a>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 36px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 11px; color: #64748b; line-height: 1.6; text-align: center;">
                            <div>
                                <strong>COURIER with NETPACK</strong> &middot; Kathmandu Central Hub: Thamel, Kathmandu, Nepal
                            </div>
                            <div style="margin-top: 4px;">
                                24/7 Operations Hotline: +977-1-5970123 &middot; Support: info@netpackcargo.com
                            </div>
                            <div style="margin-top: 10px; color: #94a3b8;">
                                This is an automated air cargo tracking transmission. Please do not reply directly to this message.
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
