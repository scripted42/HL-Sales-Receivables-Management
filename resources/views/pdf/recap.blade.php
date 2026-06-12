<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Bulanan {{ $customer->name }} - {{ $monthName }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1d273b;
            font-size: 11px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .recap-card {
            padding: 10px;
            border-top: 5px solid #206bc4;
        }
        .header-table {
            width: 100%;
            margin-bottom: 25px;
        }
        .header-table td {
            vertical-align: top;
        }
        .brand-title {
            font-size: 18px;
            font-weight: 700;
            color: #206bc4;
            letter-spacing: -0.02em;
            margin: 0;
        }
        .brand-subtitle {
            font-size: 9px;
            color: #626976;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 3px;
        }
        .recap-title {
            font-size: 18px;
            font-weight: 700;
            color: #1d273b;
            text-align: right;
            margin: 0;
        }
        .recap-period {
            font-size: 11px;
            color: #626976;
            text-align: right;
            margin-top: 5px;
            font-weight: 600;
        }
        .split-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .split-table td {
            vertical-align: top;
            width: 50%;
        }
        .section-title {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            color: #626976;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
        }
        .info-value {
            font-size: 11px;
            color: #1d273b;
            line-height: 1.5;
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
            margin-bottom: 3px;
            font-size: 11px;
        }
        .meta-label {
            color: #626976;
            display: inline-block;
            width: 155px;
        }
        .stats-grid {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin-left: -8px;
            margin-right: -8px;
        }
        .stats-card {
            background-color: #ffffff;
            border: 1px solid #e6e8eb;
            padding: 10px 12px;
            border-radius: 4px;
            vertical-align: top;
            width: 25%;
        }
        .stats-title {
            font-size: 9px;
            text-transform: uppercase;
            color: #626976;
            font-weight: 600;
            margin-bottom: 4px;
            display: block;
            letter-spacing: 0.04em;
        }
        .stats-value {
            font-size: 13px;
            font-weight: 700;
            color: #1d273b;
        }
        .stats-subtext {
            font-size: 8px;
            color: #8c939d;
            margin-top: 3px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .items-table th {
            background-color: #f6f8fb;
            border-bottom: 2px solid #e6e8eb;
            border-top: 1px solid #e6e8eb;
            font-weight: 600;
            padding: 8px 10px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            color: #626976;
            letter-spacing: 0.04em;
        }
        .items-table td {
            padding: 8px 10px;
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
            padding: 2px 6px;
            font-size: 8px;
            font-weight: 700;
            border-radius: 3px;
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
        .footer {
            margin-top: 40px;
            border-top: 1px solid #e6e8eb;
            padding-top: 15px;
            text-align: center;
            color: #626976;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="recap-card">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td>
                    <h1 class="brand-title">HL SALES & RECEIVABLES</h1>
                    <div class="brand-subtitle">Laporan Rekap Bulanan Pelanggan</div>
                </td>
                <td>
                    <div class="recap-title">REKAP TRANSAKSI</div>
                    <div class="recap-period">Periode: {{ $monthName }}</div>
                </td>
            </tr>
        </table>

        <!-- Metadata -->
        <table class="split-table">
            <tr>
                <td>
                    <div class="section-title">Pelanggan</div>
                    <div class="info-value">
                        <strong>{{ $customer->name }}</strong><br>
                        Mitra Usaha HL
                    </div>
                </td>
                <td>
                    <div class="section-title">Informasi Akun</div>
                    <ul class="meta-list">
                        <li><span class="meta-label">Cascading Discount LM:</span> {{ empty($customer->discount_lm) ? '-' : implode(' ➔ ', array_map(fn($v) => "$v%", $customer->discount_lm)) }}</li>
                        <li><span class="meta-label">Cascading Discount BR:</span> {{ empty($customer->discount_br) ? '-' : implode(' ➔ ', array_map(fn($v) => "$v%", $customer->discount_br)) }}</li>
                        <li><span class="meta-label">Bonus Eligibility Threshold:</span> Rp {{ number_format($customer->bonus_threshold, 0, ',', '.') }}</li>
                        <li><span class="meta-label">Tanggal Unduh:</span> {{ now()->format('d M Y H:i') }} WIB</li>
                    </ul>
                </td>
            </tr>
        </table>

        <!-- Statistics Grid Cards -->
        <table class="stats-grid">
            <tr>
                <td class="stats-card">
                    <span class="stats-title" style="color: #b91c1c;">Total Piutang</span>
                    <div class="stats-value" style="color: #b91c1c;">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</div>
                    <div class="stats-subtext">Belum dilunasi</div>
                </td>
                <td class="stats-card">
                    <span class="stats-title" style="color: #047857;">Sudah Dibayar</span>
                    <div class="stats-value" style="color: #047857;">Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
                    <div class="stats-subtext">Berhasil ditagih</div>
                </td>
                <td class="stats-card">
                    <span class="stats-title">Total Omzet Lunas</span>
                    <div class="stats-value">Rp {{ number_format($omzetLM + $omzetBR, 0, ',', '.') }}</div>
                    <div class="stats-subtext">LM: {{ number_format($omzetLM, 0, ',', '.') }} | BR: {{ number_format($omzetBR, 0, ',', '.') }}</div>
                </td>
                <td class="stats-card">
                    <span class="stats-title" style="color: #206bc4;">Laba HL Lunas</span>
                    <div class="stats-value" style="color: #206bc4;">Rp {{ number_format($labaLM + $labaBR, 0, ',', '.') }}</div>
                    <div class="stats-subtext">LM: {{ number_format($labaLM, 0, ',', '.') }} | BR: {{ number_format($labaBR, 0, ',', '.') }}</div>
                </td>
            </tr>
        </table>

        <!-- List Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Tanggal</th>
                    <th style="width: 25%;">Nomor Bon</th>
                    <th style="width: 15%;" class="text-center">Status</th>
                    <th style="width: 15%;" class="text-center">Jenis</th>
                    <th style="width: 15%;" class="text-right">Total Tagihan</th>
                    <th style="width: 15%;" class="text-right">Tgl Lunas</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $t)
                    <tr>
                        <td>{{ $t->tanggal->format('d M Y') }}</td>
                        <td style="font-family: monospace; font-weight: 600;">{{ $t->nomor_bon }}</td>
                        <td class="text-center">
                            @if($t->status === 'Lunas')
                                <span class="badge badge-lunas">LUNAS</span>
                            @else
                                <span class="badge badge-piutang">PIUTANG</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($t->is_bonus)
                                <span style="color: #206bc4; font-weight: 700; font-size: 8px;">🎁 BONUS</span>
                            @else
                                <span style="color: #626976; font-size: 8px;">SALES</span>
                            @endif
                        </td>
                        <td class="text-right" style="font-weight: 600;">Rp {{ number_format($t->total_owed, 2, ',', '.') }}</td>
                        <td class="text-right" style="color: #626976;">
                            {{ $t->tanggal_pelunasan ? $t->tanggal_pelunasan->format('d M Y') : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 20px; color: #8c939d;">
                            Tidak ada transaksi pada periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            Laporan ini dikeluarkan secara sah oleh Sistem Manajemen HL Sales & Receivables.<br>
            Harap hubungi admin jika terdapat ketidaksesuaian data.
        </div>
    </div>
</body>
</html>
