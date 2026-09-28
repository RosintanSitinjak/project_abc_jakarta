<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Stok Buku - ABC Jakarta</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #00a9c3;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #1b293c;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header h2 {
            margin: 3px 0 0 0;
            font-size: 13px;
            color: #00a9c3;
        }
        .header p {
            margin: 4px 0 0 0;
            font-size: 10px;
            color: #64748b;
        }
        .meta-info {
            margin-bottom: 12px;
            font-size: 10px;
            color: #475569;
            display: flex;
            justify-content: space-between;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        th {
            background-color: #00a9c3;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            padding: 8px 6px;
            font-size: 10px;
            border: 1px solid #008ba0;
        }
        td {
            padding: 6px;
            border: 1px solid #cbd5e1;
            font-size: 10px;
            vertical-align: middle;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .badge-kritis {
            background-color: #fee2e2;
            color: #dc2626;
            padding: 2px 5px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 9px;
            display: inline-block;
        }
        .badge-aman {
            background-color: #dcfce7;
            color: #16a34a;
            padding: 2px 5px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 9px;
            display: inline-block;
        }
        .footer {
            margin-top: 25px;
            font-size: 9px;
            color: #94a3b8;
            text-align: right;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>ADVENTIST BOOK CENTER (ABC) JAKARTA</h1>
        <h2>LAPORAN DAFTAR INVENTARIS & STOK BUKU</h2>
        <p>Waktu Cetak: {{ $tanggalCetak ?? date('d F Y H:i') }} | Dicetak oleh: {{ auth()->user()->name ?? 'Admin Staff' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="30%">Judul Buku</th>
                <th width="15%">Kategori</th>
                <th width="15%">Penulis</th>
                <th width="12%">Harga Umum</th>
                <th width="12%">Harga PL</th>
                <th width="6%">Stok</th>
                <th width="6%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($books as $index => $book)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $book->title }}</td>
                    <td>{{ $book->category->name ?? '-' }}</td>
                    <td>{{ $book->author ?? '-' }}</td>
                    <td class="text-right">Rp{{ number_format($book->price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp{{ number_format($book->member_price ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center font-bold">{{ $book->stock }}</td>
                    <td class="text-center">
                        @if($book->stock <= ($book->rop_point ?? 10))
                            <span class="badge-kritis">Kritis</span>
                        @else
                            <span class="badge-aman">Aman</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 15px; color: #94a3b8;">
                        Tidak ada data buku di inventaris.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Sistem Inventaris Adventist Book Center (ABC) Jakarta
    </div>

</body>
</html>