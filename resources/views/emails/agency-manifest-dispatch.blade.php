<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbound Flight Manifest Dispatch Notice</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed; background-color: #f1f5f9; padding: 30px 10px;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table border="0" cellpadding="0" cellspacing="0" width="650" style="max-width: 650px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
                    
                    <!-- Top Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); padding: 32px 36px; text-align: left;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td>
                                        <div style="color: #ffffff; font-size: 22px; font-weight: 800;">
                                            NETPACK <span style="color: #38bdf8; font-weight: 300;">CARGO DISPATCH</span>
                                        </div>
                                        <div style="color: #94a3b8; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px; margin-top: 4px;">
                                            Inbound Air Cargo Flight Manifest &middot; Agency Operations
                                        </div>
                                    </td>
                                    <td align="right" valign="top">
                                        <span style="display: inline-block; background-color: rgba(56, 189, 248, 0.2); border: 1px solid rgba(56, 189, 248, 0.4); color: #38bdf8; font-size: 11px; font-weight: 700; padding: 4px 12px; border-radius: 20px; text-transform: uppercase;">
                                            Official Dispatch
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Agency Greeting & Manifest Meta -->
                    <tr>
                        <td style="padding: 28px 36px 20px 36px; background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <div style="color: #0f172a; font-size: 18px; font-weight: 800;">
                                Attention: Operations & Customs Clearance Team &mdash; {{ $agency->name }}
                            </div>
                            <div style="color: #475569; font-size: 13px; margin-top: 6px; line-height: 1.5;">
                                Please find below the outbound flight manifest dispatched from Kathmandu Central Gateway (TIA, KTM) destined for your hub facility at <strong>{{ $manifest->destination_city }}</strong>.
                            </div>

                            <!-- Flight & MAWB Highlight Card -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 18px; background-color: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 14px 18px;">
                                <tr>
                                    <td width="33%" style="border-right: 1px solid #f1f5f9; padding-right: 12px;">
                                        <div style="color: #94a3b8; font-size: 10px; font-weight: 700; text-transform: uppercase;">MAWB Reference</div>
                                        <div style="color: #1e3a8a; font-size: 15px; font-weight: 800; font-family: monospace; margin-top: 3px;">
                                            {{ $manifest->mawb_number ?: ($manifest->mawb?->mawb_number ?: 'Assigned on departure') }}
                                        </div>
                                    </td>
                                    <td width="33%" style="border-right: 1px solid #f1f5f9; padding-left: 12px; padding-right: 12px;">
                                        <div style="color: #94a3b8; font-size: 10px; font-weight: 700; text-transform: uppercase;">Flight Details</div>
                                        <div style="color: #0f172a; font-size: 14px; font-weight: 700; margin-top: 3px;">
                                            ✈️ {{ $flightNumber }}
                                        </div>
                                        <div style="color: #64748b; font-size: 11px;">Dep: {{ $flightDate }}</div>
                                    </td>
                                    <td width="33%" style="padding-left: 12px;">
                                        <div style="color: #94a3b8; font-size: 10px; font-weight: 700; text-transform: uppercase;">Manifest Ref #</div>
                                        <div style="color: #0f172a; font-size: 14px; font-weight: 700; font-family: monospace; margin-top: 3px;">
                                            {{ $manifest->manifest_number }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Key Statistics Cards -->
                    <tr>
                        <td style="padding: 20px 36px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td width="32%" style="background-color: #f1f5f9; border-radius: 10px; padding: 12px 16px; text-align: center;">
                                        <div style="color: #64748b; font-size: 10px; font-weight: 700; text-transform: uppercase;">Consignments</div>
                                        <div style="color: #0f172a; font-size: 20px; font-weight: 800; margin-top: 2px;">
                                            {{ $manifest->total_shipments }}
                                        </div>
                                    </td>
                                    <td width="2%"></td>
                                    <td width="32%" style="background-color: #f1f5f9; border-radius: 10px; padding: 12px 16px; text-align: center;">
                                        <div style="color: #64748b; font-size: 10px; font-weight: 700; text-transform: uppercase;">Bags / ULDs</div>
                                        <div style="color: #0f172a; font-size: 20px; font-weight: 800; margin-top: 2px;">
                                            {{ $manifest->total_bags ?: 1 }}
                                        </div>
                                    </td>
                                    <td width="2%"></td>
                                    <td width="32%" style="background-color: #f1f5f9; border-radius: 10px; padding: 12px 16px; text-align: center;">
                                        <div style="color: #64748b; font-size: 10px; font-weight: 700; text-transform: uppercase;">Gross Weight</div>
                                        <div style="color: #0f172a; font-size: 20px; font-weight: 800; margin-top: 2px;">
                                            {{ number_format($manifest->total_weight, 2) }} KG
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Consignments Sample Table -->
                    <tr>
                        <td style="padding: 0 36px 20px 36px;">
                            <div style="color: #0f172a; font-size: 14px; font-weight: 800; margin-bottom: 10px;">Manifested Consignments Breakdown</div>
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; font-size: 11px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                                <thead>
                                    <tr style="background-color: #0f172a; color: #ffffff; text-align: left;">
                                        <th style="padding: 10px 12px;">HAWB #</th>
                                        <th style="padding: 10px 12px;">Consignee & City</th>
                                        <th style="padding: 10px 12px;">Weight</th>
                                        <th style="padding: 10px 12px;">Description</th>
                                        <th style="padding: 10px 12px;">Mode</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($manifest->shipments->take(15) as $line)
                                    @php $s = $line->shipment; @endphp
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 9px 12px; font-family: monospace; font-weight: 700; color: #0d9488;">
                                            {{ $s?->hawb_number ?: $s?->tracking_number }}
                                        </td>
                                        <td style="padding: 9px 12px; color: #1e293b;">
                                            <strong>{{ $s?->receiver_name }}</strong><br>
                                            <span style="color: #64748b;">{{ $s?->receiver_city }}, {{ $s?->receiver_country }}</span>
                                        </td>
                                        <td style="padding: 9px 12px; font-weight: 700; color: #0f172a;">
                                            {{ number_format($s?->chargeable_weight ?? $s?->actual_weight ?? 0, 2) }} kg
                                        </td>
                                        <td style="padding: 9px 12px; color: #475569; max-width: 140px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                            {{ $s?->description ?: 'Export Goods' }}
                                        </td>
                                        <td style="padding: 9px 12px; font-weight: 700;">
                                            <span style="padding: 2px 6px; border-radius: 4px; background-color: #e0f2fe; color: #0369a1;">
                                                {{ $s?->customs_mode ?: 'DDP' }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" style="padding: 16px; text-align: center; color: #94a3b8;">No line items found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            @if($manifest->shipments->count() > 15)
                            <div style="text-align: right; margin-top: 6px; font-size: 11px; color: #64748b;">
                                + {{ $manifest->shipments->count() - 15 }} more consignments included in attached Data Sheet
                            </div>
                            @endif
                        </td>
                    </tr>

                    <!-- Action Callout for Agency Staff -->
                    <tr>
                        <td style="padding: 10px 36px 30px 36px; text-align: center;">
                            <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 20px;">
                                <div style="color: #166534; font-size: 14px; font-weight: 800;">
                                    Inbound Flight Arrival Notice Action Required
                                </div>
                                <p style="color: #15803d; font-size: 12px; margin: 6px 0 16px 0;">
                                    Upon arrival at your airport facility, agency staff can confirm the arrival of the whole manifest or record partial arrivals with condition remarks.
                                </p>
                                <a href="{{ route('agency.manifests.arrival-notice', $manifest->id) }}" target="_blank" style="display: inline-block; background-color: #059669; color: #ffffff; text-decoration: none; font-size: 13px; font-weight: 800; padding: 12px 28px; border-radius: 10px; box-shadow: 0 4px 10px rgba(5, 150, 105, 0.2);">
                                    Open Inbound Arrival Notice Desk &rarr;
                                </a>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 36px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 11px; color: #64748b; line-height: 1.6; text-align: center;">
                            <div>
                                <strong>COURIER with NETPACK International Freight Operations</strong> &middot; Kathmandu Central Hub, Nepal
                            </div>
                            <div style="margin-top: 4px;">
                                Pre-defined Agency Recipient List: {{ implode(', ', $agency->getAllNotificationEmails()) }}
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
