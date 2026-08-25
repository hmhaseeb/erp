<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>TAX INVOICE #{{ $sale->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #333; line-height: 1.4; }
        .invoice-box { max-width: 800px; margin: auto; padding: 20px; }
        table { width: 100%; line-height: inherit; text-align: left; border-collapse: collapse; }
        table td { padding: 8px; vertical-align: top; }
        table th { background: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 8px; font-size: 12px; font-weight: bold; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .border-top { border-top: 1px solid #dee2e6; }
        .header-title { font-size: 24px; font-weight: bold; color: #1e293b; text-transform: uppercase; }
        .company-name { font-size: 18px; font-weight: bold; color: #0f172a; }
        .footer-note { font-size: 11px; color: #64748b; margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table>
            <tr>
                <td style="width: 60%;">
                    @if($company && $company->invoice_logo_src)
                        <div style="margin-bottom: 12px;">
                            <img src="{{ $company->invoice_logo_src }}" alt="Company Logo" style="max-height: 70px; max-width: 220px; width: auto; height: auto; object-fit: contain;">
                        </div>
                    @endif
                    <div class="company-name">{{ $company->company_name ?? 'Apex General Trading LLC' }}</div>
                    <div>{{ $company->address ?? '' }}</div>
                    <div>{{ $company->city ?? '' }}, {{ $company->country ?? '' }}</div>
                    <div>Mobile: {{ $company->mobile ?? '-' }} | Email: {{ $company->email ?? '-' }}</div>
                    <div><strong>TRN / VAT #:</strong> {{ $company->trn_number ?? '-' }}</div>
                </td>
                <td class="text-end" style="width: 40%;">
                    <div class="header-title">TAX INVOICE</div>
                    <div style="margin-top: 10px;">
                        <strong>Invoice #:</strong> {{ $sale->invoice_number }}<br>
                        <strong>Date:</strong> {{ $sale->sale_date }}<br>
                        <strong>Payment Type:</strong> {{ $sale->payment_type }}
                    </div>
                </td>
            </tr>
        </table>

        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 15px 0;">

        <table>
            <tr>
                <td>
                    <strong>BILLED TO:</strong><br>
                    <span style="font-size: 14px;" class="fw-bold">{{ $sale->customer->name ?? 'Walk-in Customer' }}</span><br>
                    @if($sale->customer->company_name) {{ $sale->customer->company_name }}<br> @endif
                    @if($sale->customer->address) {{ $sale->customer->address }}<br> @endif
                    @if($sale->customer->trn_number) <strong>TRN #:</strong> {{ $sale->customer->trn_number }}<br> @endif
                    @if($sale->customer->mobile) Mobile: {{ $sale->customer->mobile }} @endif
                </td>
            </tr>
        </table>

        <table style="margin-top: 20px; border: 1px solid #dee2e6;">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 45%;">Item Description</th>
                    <th class="text-end" style="width: 12%;">Qty</th>
                    <th class="text-end" style="width: 15%;">Unit Price</th>
                    <th class="text-end" style="width: 10%;">VAT %</th>
                    <th class="text-end" style="width: 13%;">Total (AED)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $idx => $item)
                    <tr style="border-bottom: 1px solid #edf2f7;">
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            <strong class="fw-bold">{{ $item->product->name ?? 'Product' }}</strong><br>
                            <small style="color: #64748b;">SKU: {{ $item->product->product_code ?? '-' }}</small>
                        </td>
                        <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                        <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end">{{ number_format($item->vat_percent, 1) }}%</td>
                        <td class="text-end fw-bold">{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table style="margin-top: 15px;">
            <tr>
                <td style="width: 50%;">
                    @if($invoiceSetting->bank_details)
                        <div style="background: #f8fafc; padding: 10px; border-radius: 4px; border: 1px solid #e2e8f0;">
                            <strong style="font-size: 11px; text-transform: uppercase; color: #475569;">Bank Transfer Details:</strong>
                            <div style="font-size: 11px; white-space: pre-line; margin-top: 4px;">{{ $invoiceSetting->bank_details }}</div>
                        </div>
                    @endif
                </td>
                <td style="width: 50%;">
                    <table>
                        <tr>
                            <td>Subtotal:</td>
                            <td class="text-end">AED {{ number_format($sale->subtotal, 2) }}</td>
                        </tr>
                        @if($sale->discount_amount > 0)
                            <tr>
                                <td>Discount:</td>
                                <td class="text-end">- AED {{ number_format($sale->discount_amount, 2) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td>VAT Tax Amount:</td>
                            <td class="text-end">AED {{ number_format($sale->vat_amount, 2) }}</td>
                        </tr>
                        <tr style="border-top: 2px solid #0f172a; font-size: 15px;" class="fw-bold">
                            <td>Grand Total:</td>
                            <td class="text-end">AED {{ number_format($sale->grand_total, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        @if($invoiceSetting->terms_conditions)
            <div style="margin-top: 20px; font-size: 11px; color: #64748b;">
                <strong>Terms & Conditions:</strong>
                <div>{{ $invoiceSetting->terms_conditions }}</div>
            </div>
        @endif

        <div class="footer-note text-center">
            {{ $invoiceSetting->invoice_footer ?? 'Thank you for your business!' }}
        </div>
    </div>
</body>
</html>
