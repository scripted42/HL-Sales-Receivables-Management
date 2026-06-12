<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bon {{ $transaction->nomor_bon }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1d273b;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .invoice-card {
            padding: 10px;
            border-top: 5px solid #206bc4;
        }
        .header-table {
            width: 100%;
            margin-bottom: 30px;
        }
        .header-table td {
            vertical-align: top;
        }
        .brand-title {
            font-size: 20px;
            font-weight: 700;
            color: #206bc4;
            letter-spacing: -0.02em;
            margin: 0;
        }
        .brand-subtitle {
            font-size: 10px;
            color: #626976;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 3px;
        }
        .invoice-title {
            font-size: 22px;
            font-weight: 700;
            color: #1d273b;
            text-align: right;
            margin: 0;
        }
        .invoice-number {
            font-size: 12px;
            font-family: monospace;
            color: #626976;
            text-align: right;
            margin-top: 5px;
        }
        .split-table {
            width: 100%;
            margin-bottom: 30px;
        }
        .split-table td {
            vertical-align: top;
            width: 50%;
        }
        .section-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #626976;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
        }
        .info-value {
            font-size: 12px;
            color: #1d273b;
            line-height: 1.6;
        }
        .info-value strong {
            color: #1c2434;
            font-weight: 600;
        }
        .meta-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .meta-list li {
            margin-bottom: 4px;
            font-size: 12px;
        }
        .meta-label {
            color: #626976;
            display: inline-block;
            width: 110px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #f6f8fb;
            border-bottom: 2px solid #e6e8eb;
            border-top: 1px solid #e6e8eb;
            font-weight: 600;
            padding: 10px 12px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            color: #626976;
            letter-spacing: 0.05em;
        }
        .items-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e6e8eb;
            color: #1d273b;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 9px;
            font-weight: 700;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .badge-lunas {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-piutang {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .summary-container {
            width: 320px;
            float: right;
            margin-top: 10px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 6px 12px;
            font-size: 12px;
        }
        .summary-label {
            color: #626976;
        }
        .summary-value {
            font-weight: 600;
            text-align: right;
            color: #1d273b;
        }
        .total-row td {
            border-top: 2px solid #e6e8eb;
            font-size: 14px;
            padding-top: 10px;
            font-weight: 700;
        }
        .total-value {
            color: #206bc4;
            font-size: 16px;
        }
        .footer {
            margin-top: 60px;
            border-top: 1px solid #e6e8eb;
            padding-top: 20px;
            text-align: center;
            color: #626976;
            font-size: 10px;
            clear: both;
        }
    </style>
</head>
<body>
    <div class="invoice-card">
        <!-- Top Invoice Header -->
        <table class="header-table">
            <tr>
                <td>
                    <h1 class="brand-title">HL SALES & RECEIVABLES</h1>
                    <div class="brand-subtitle">Faktur Penjualan Resmi</div>
                </td>
                <td>
                    <div class="invoice-title">FAKTUR (BON)</div>
                    <div class="invoice-number">No. {{ $transaction->nomor_bon }}</div>
                </td>
            </tr>
        </table>

        <!-- Details split: Client and Metadata -->
        <table class="split-table">
            <tr>
                <td>
                    <div class="section-title">Pelanggan (Penerima)</div>
                    <div class="info-value">
                        <strong>{{ $transaction->customer->name }}</strong><br>
                        Mitra Usaha HL<br>
                        Tipe: {{ $transaction->is_bonus ? 'Transaksi Bonus (Free Items)' : 'Penjualan Reguler' }}
                    </div>
                </td>
                <td>
                    <div class="section-title">Rincian Faktur</div>
                    <ul class="meta-list">
                        <li><span class="meta-label">Tanggal Terbit:</span> {{ $transaction->tanggal->format('d M Y') }}</li>
                        @if($transaction->status === 'Lunas' && $transaction->tanggal_pelunasan)
                            <li><span class="meta-label">Tgl Pelunasan:</span> {{ $transaction->tanggal_pelunasan->format('d M Y') }}</li>
                        @endif
                        <li>
                            <span class="meta-label">Status Bayar:</span>
                            @if ($transaction->status === 'Lunas')
                                <span class="badge badge-lunas">LUNAS</span>
                            @else
                                <span class="badge badge-piutang">PIUTANG</span>
                            @endif
                        </li>
                    </ul>
                </td>
            </tr>
        </table>

        <!-- Itemized List Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;" class="text-center">No</th>
                    <th style="width: 45%;">Nama Produk</th>
                    <th style="width: 10%;" class="text-center">Tipe</th>
                    <th style="width: 10%;" class="text-center">Qty</th>
                    <th style="width: 15%;" class="text-right">Harga Diskon</th>
                    <th style="width: 15%;" class="text-right">Subtotal Omzet</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->items as $idx => $item)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td style="font-weight: 500;">{{ $item->product_name }}</td>
                        <td class="text-center">{{ $item->product_type }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">Rp {{ number_format($item->discounted_unit_price, 2, ',', '.') }}</td>
                        <td class="text-right" style="font-weight: 600;">Rp {{ number_format($item->line_omzet, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals summary block -->
        <div class="summary-container">
            <table class="summary-table">
                <tr>
                    <td class="summary-label">Total Omzet</td>
                    <td class="summary-value">Rp {{ number_format($transaction->omzet, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="summary-label">Ongkos Kirim</td>
                    <td class="summary-value">Rp {{ number_format($transaction->ongkir, 2, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td class="summary-label">
                        {{ $transaction->is_bonus ? 'Total Biaya' : 'Total Tagihan (Piutang)' }}
                    </td>
                    <td class="summary-value total-value">Rp {{ number_format($transaction->total_owed, 2, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <!-- Invoice footer -->
        <div class="footer">
            Terima kasih atas kemitraan dan kepercayaan Anda bersama HL.<br>
            Dokumen ini sah dikeluarkan oleh sistem HL Sales & Receivables Management tanpa tanda tangan fisik.
        </div>
    </div>
</body>
</html>
