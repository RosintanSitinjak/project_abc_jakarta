<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Inventaris ABC Jakarta</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #00A9C3; padding-bottom: 10px; }
        .header h2 { margin: 0; color: #1B293C; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #555; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #ccc; }
        th { background-color: #00A9C3; color: white; padding: 8px; text-align: center; }
        td { padding: 6px; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        /* Status Colors */
        .status-aman { color: #27ae60; font-weight: bold; }
        .status-kritis { color: #e74c3c; font-weight: bold; }
        
        .footer { margin-top: 20px; text-align: right; font-size: 9px; color: #777; font-style: italic; }
        .summary { margin-top: 15px; padding: 10px; background-color: #f9f9f9; border: 1px solid #eee; width: 300px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Adventist Book Center (ABC) Jakarta</h2>
        <p>Laporan Inventaris Stok Buku Lengkap</p>
        <p style="font-size: 10px;">Dicetak pada: {{ $tanggalCetak }} WIB</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="20%">Judul Buku</th>
                <th width="12%">Kategori</th>
                <th width="12%">Penulis</th>
                <th width="10%">ISBN</th>
                <th width="5%">Stok</th>
                <th width="12%">Harga Umum</th>
                <th width="12%">Harga PL</th>
                <th width="14%">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $totalStok = 0; @endphp
            @foreach($books as $index => $book)
            @php $totalStok += $book->stock; @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-bold">{{ $book->title }}</td>
                <td class="text-center">{{ $book->category->name ?? '-' }}</td>
                <td>{{ $book->author ?? '-' }}</td>
                <td class="text-center">{{ $book->isbn ?? '-' }}</td>
                <td class="text-center">{{ $book->stock }}</td>
                <td class="text-right">Rp{{ number_format($book->price, 0, ',', '.') }}</td>
                <td class="text-right">Rp{{ number_format($book->member_price, 0, ',', '.') }}</td>
                <td class="text-center">
                    @if($book->stock <= $book->rop_point)
                        <span class="status-kritis">PERLU RESTOCK</span>
                    @else
                        <span class="status-aman">STOK AMAN</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <p><strong>Ringkasan Laporan:</strong></p>
        <p>Total Judul Buku: {{ count($books) }} Judul</p>
        <p>Total Unit di Gudang: {{ number_format($totalStok, 0, ',', '.') }} Unit</p>
    </div>

    <div class="footer">
        Dokumen ini dihasilkan secara otomatis oleh Sistem Informasi Penjualan ABC Jakarta.
    </div>
</body>
</html>