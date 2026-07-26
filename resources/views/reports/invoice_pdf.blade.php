<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; line-height: 1.5; }
        .header { text-align: center; border-bottom: 2px solid #00A9C3; padding-bottom: 10px; margin-bottom: 20px; }
        .info-table { width: 100%; margin-bottom: 20px; border: none; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { background: #00A9C3; color: white; padding: 8px; text-align: left; }
        .items-table td { padding: 8px; border: 1px solid #eee; }
        .total-section { float: right; width: 250px; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .font-bold { font-weight: bold; }
        .text-red { color: #e74c3c; }
        .footer { margin-top: 50px; text-align: center; font-size: 9px; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin:0; color:#1B293C;">ADVENTIST BOOK CENTER (ABC) JAKARTA</h2>
        <p style="margin:5px 0;">Official Sales Invoice</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="60%">
                <p><strong>DITAGIHKAN KEPADA:</strong><br>
                {{ $order->customer->name }} ({{ strtoupper($order->customer->type) }})<br>
                {{ $order->customer->address ?? 'Alamat tidak tersedia' }}</p>
            </td>
            <td width="40%" style="text-align: right; vertical-align: top;">
                <p><strong>NOMOR INVOICE:</strong> {{ $order->order_number }}<br>
                <strong>TANGGAL:</strong> {{ $order->created_at->format('d F Y') }}<br>
                <strong>METODE:</strong> {{ strtoupper($order->payment_method) }}</p>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Judul Buku</th>
                <th width="10%" style="text-align: center;">Qty</th>
                <th width="20%" style="text-align: right;">Harga Satuan</th>
                <th width="20%" style="text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->book->title }}</td>
                <td style="text-align: center;">{{ $item->qty }}</td>
                <td style="text-align: right;">Rp{{ number_format($item->price_at_purchase, 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp{{ number_format($item->price_at_purchase * $item->qty, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <table style="width: 100%; border: none;">
            <tr>
                <td>Total Belanja:</td>
                <td style="text-align: right;"><strong>Rp{{ number_format($order->total_amount, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td style="color: #e74c3c;">Sisa Tagihan:</td>
                <td style="text-align: right; color: #e74c3c;"><strong>Rp{{ number_format($order->remaining_amount, 0, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    <div class="footer">
        <p>Terima kasih atas kepercayaan Anda berbelanja di ABC Jakarta.<br>
        <em>Tuhan Memberkati.</em></p>
    </div>
</body>
</html>