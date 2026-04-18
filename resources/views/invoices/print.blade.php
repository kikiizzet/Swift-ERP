<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $invoice->number }}</title>
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 40px;
            line-height: 1.5;
            background-color: #fff;
        }

        .invoice-box {
            max-width: 850px;
            margin: auto;
            position: relative;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 20px;
        }

        .company-info h1 {
            margin: 0;
            font-size: 28px;
            color: #3b82f6;
            letter-spacing: -0.02em;
        }

        .company-info p {
            margin: 4px 0;
            color: #64748b;
            font-size: 14px;
        }

        .invoice-title {
            text-align: right;
        }

        .invoice-title h2 {
            margin: 0;
            font-size: 32px;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: 0.05em;
        }

        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }

        .details-col h3 {
            font-size: 12px;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 8px;
            letter-spacing: 0.05em;
        }

        .details-col p {
            margin: 0;
            font-size: 15px;
            font-weight: 500;
        }

        .details-col strong {
            color: #0f172a;
            font-size: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        table th {
            background-color: #f8fafc;
            text-align: left;
            padding: 12px;
            font-size: 12px;
            text-transform: uppercase;
            color: #64748b;
            border-bottom: 2px solid #f1f5f9;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }

        .text-right { text-align: right; }

        .summary {
            display: flex;
            justify-content: flex-end;
        }

        .summary-table {
            width: 300px;
        }

        .summary-table tr td {
            border: none;
            padding: 4px 12px;
        }

        .total-row {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            background-color: #f8fafc;
        }

        /* PAID STAMP */
        .paid-stamp {
            position: absolute;
            top: 150px;
            left: 50%;
            transform: translateX(-50%) rotate(-15deg);
            border: 6px solid #10b981;
            color: #10b981;
            font-size: 60px;
            font-weight: 900;
            padding: 10px 40px;
            border-radius: 12px;
            opacity: 0.15;
            text-transform: uppercase;
            pointer-events: none;
            z-index: 10;
        }

        .footer {
            margin-top: 60px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
        }

        @media print {
            body { padding: 0; }
            .no-print { display: none; }
            .invoice-box { width: 100%; max-width: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Cetak / Simpan PDF</button>
    </div>

    <div class="invoice-box">
        @if($invoice->status === 'paid')
            <div class="paid-stamp">LUNAS / PAID</div>
        @endif

        <div class="header">
            <div class="company-info">
                <h1>{{ $company->name ?? 'Swift ERP' }}</h1>
                <p>{{ $company->address ?? 'Alamat Perusahaan belum diatur' }}</p>
                <p>Telp: {{ $company->phone ?? '-' }} | Email: {{ $company->email ?? '-' }}</p>
            </div>
            <div class="invoice-title">
                <h2>INVOICE</h2>
                <p style="font-size: 18px; font-weight: 600; color: #0f172a; margin: 0;">#{{ $invoice->number }}</p>
            </div>
        </div>

        <div class="invoice-details">
            <div class="details-col">
                <h3>Ditagihkan Kepada:</h3>
                <p><strong>{{ $invoice->customer->name }}</strong></p>
                <p>{{ $invoice->customer->address ?? '-' }}</p>
                <p>{{ $invoice->customer->phone ?? '-' }}</p>
            </div>
            <div class="details-col" style="text-align: right;">
                <div style="margin-bottom: 20px;">
                    <h3>Tanggal Invoice:</h3>
                    <p>{{ $invoice->date->format('d M Y') }}</p>
                </div>
                <div>
                    <h3>Jatuh Tempo:</h3>
                    <p>{{ $invoice->due_date->format('d M Y') }}</p>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Deskripsi Produk</th>
                    <th class="text-right">Kuantitas</th>
                    <th class="text-right">Harga Satuan</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->salesOrder->items as $item)
                <tr>
                    <td>
                        <div style="font-weight: 600; color: #0f172a;">{{ $item->product->name }}</div>
                        <div style="font-size: 12px; color: #64748b;">SKU: {{ $item->product->sku }}</div>
                    </td>
                    <td class="text-right">{{ $item->quantity }} {{ $item->product->unit }}</td>
                    <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <table class="summary-table">
                <tr>
                    <td>Subtotal</td>
                    <td class="text-right">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Pajak (PPN)</td>
                    <td class="text-right">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
                </tr>
                @if($invoice->salesOrder->discount_amount > 0)
                <tr>
                    <td>Diskon</td>
                    <td class="text-right">- Rp {{ number_format($invoice->salesOrder->discount_amount, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>Total Tagihan</td>
                    <td class="text-right">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                </tr>
                @if($invoice->paid_amount > 0)
                <tr>
                    <td style="color: #10b981; font-weight: 600;">Sudah Dibayar</td>
                    <td class="text-right" style="color: #10b981; font-weight: 600;">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Sisa Tagihan</td>
                    <td class="text-right" style="font-weight: 600;">Rp {{ number_format($invoice->total_amount - $invoice->paid_amount, 0, ',', '.') }}</td>
                </tr>
                @endif
            </table>
        </div>

        @if($invoice->notes)
        <div style="margin-top: 40px;">
            <h3 style="font-size: 12px; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.05em;">Catatan:</h3>
            <p style="font-size: 14px; color: #64748b;">{{ $invoice->notes }}</p>
        </div>
        @endif

        <div class="footer">
            <p>Terima kasih atas kerja sama Anda.</p>
            <p>{{ $company->name ?? 'Swift ERP' }} - Keunggulan dalam Manajemen Bisnis</p>
        </div>
    </div>

</body>
</html>
