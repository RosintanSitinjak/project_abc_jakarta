<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; line-height: 1.4; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #00A9C3; padding-bottom: 10px; margin-bottom: 20px; }
        .inv-title { font-size: 20px; font-weight: bold; color: #1B293C; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table th { background: #00A9C3; color: white; padding: 8px; text-align: left; }
        .items-table td { padding: 8px; border-bottom: 1px solid #eee; }
        .total-box { margin-top: 20px; text-align: right; font-size: 14px; font-weight: bold; }
        .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <h2>ADVENTIST BOOK CENTER (ABC) JAKARTA</h2>
        <div class="inv-title">INVOICE PENJUALAN</div>
    </div>

    <table class="info-table">
        <tr>
            <td width="50%">
                <strong>Kepada:</strong><br>
                {{ $order->customer->name }}<br>
                {{ $order->customer->address ?? 'Alamat tidak diisi' }}
            </td>
            <td width="50%" style="text-align: right;">
                <strong>No. Invoice:</strong> {{ $order->order_number }}<br>
                <strong>Tanggal:</strong> {{ $order->created_at->format('d/m/Y') }}<br>
                <strong>Metode:</strong> {{ strtoupper($order->payment_method) }}
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Judul Buku</th>
                <th width="10%">Qty</th>
                <th width="20%">Harga Satuan</th>
                <th width="20%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->book->title }}</td>
                <td>{{ $item->qty }}</td>
                <td>Rp{{ number_format($item->price_at_purchase, 0, ',', '.') }}</td>
                <td>Rp{{ number_format($item->price_at_purchase * $item->qty, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-box">
        TOTAL BAYAR: Rp{{ number_format($order->total_amount, 0, ',', '.') }}<br>
        <span style="color: #e74c3c;">SISA TAGIHAN: Rp{{ number_format($order->remaining_amount, 0, ',', '.') }}</span>
    </div>

    <div class="footer">
        Terima kasih telah berbelanja di Balai Buku Advent Jakarta.<br>
        <em>Tuhan Memberkati.</em>
    </div>
</body>
</html>