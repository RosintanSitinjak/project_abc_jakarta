<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penjualan ABC Jakarta</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #00A9C3; padding-bottom: 10px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1B293C; color: white; padding: 8px; }
        td { padding: 8px; border: 1px solid #ddd; }
        .summary { margin-top: 20px; font-weight: bold; font-size: 13px; border: 1px solid #eee; padding: 15px; width: 300px; background: #f9f9f9; }
    </style>
</head>
<body>
    <div class="header">
        <h2>ADVENTIST BOOK CENTER (ABC) JAKARTA</h2>
        <p style="font-size: 14px; font-weight: bold; color: #00A9C3;">LAPORAN PENJUALAN LITERASI</p>
        <p>Periode: {{ $periode }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>No. Invoice</th>
                <th>Nama Pelanggan</th>
                <th>Metode</th>
                <th>Total Nota</th>
                <th>Sisa Tagihan</th>
            </tr>
        </thead>
        <tbody>
            @php $totalOmzet = 0; $totalPiutang = 0; @endphp
            @foreach($orders as $index => $order)
            @php 
                $totalOmzet += $order->total_amount; 
                $totalPiutang += $order->remaining_amount;
            @endphp
            <tr>
                <td style="text-align:center;">{{ $index + 1 }}</td>
                <td>{{ $order->created_at->format('d/m/y') }}</td>
                <td><strong>{{ $order->order_number }}</strong></td>
                <td>{{ $order->customer->name }}</td>
                <td style="text-align:center;">{{ strtoupper($order->payment_method) }}</td>
                <td style="text-align:right;">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</td>
                <td style="text-align:right;">Rp{{ number_format($order->remaining_amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <p>Ringkasan Laporan:</p>
        <table width="100%" style="border:none;">
            <tr><td>Total Omzet (Barang Keluar)</td><td align="right">Rp{{ number_format($totalOmzet, 0, ',', '.') }}</td></tr>
            <tr style="color:red;"><td>Total Piutang (Belum Tertagih)</td><td align="right">Rp{{ number_format($totalPiutang, 0, ',', '.') }}</td></tr>
            <tr style="border-top:1px solid #ccc;"><td><b>Realisasi Kas (Diterima)</b></td><td align="right"><b>Rp{{ number_format($totalOmzet - $totalPiutang, 0, ',', '.') }}</b></td></tr>
        </table>
    </div>
</body>
</html>