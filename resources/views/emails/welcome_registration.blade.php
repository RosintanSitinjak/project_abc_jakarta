<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Akun ABC Jakarta</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #334155; margin: 0; padding: 20px; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; }
        .header { background: linear-gradient(135deg, #00A9C3 0%, #007d91 100%); padding: 30px 25px; text-align: center; color: #ffffff; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 800; letter-spacing: 0.5px; }
        .header p { margin: 5px 0 0; font-size: 12px; opacity: 0.9; }
        .content { padding: 30px 25px; }
        .greeting { font-size: 15px; font-weight: bold; color: #1e293b; margin-bottom: 12px; }
        .info-box { background: #f1f5f9; border-radius: 12px; padding: 18px; margin: 20px 0; border-left: 4px solid #00A9C3; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px; }
        .info-label { color: #64748b; font-weight: 600; }
        .info-value { color: #0f172a; font-weight: bold; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .badge-active { background: #dcfce7; color: #166534; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .btn-wrapper { text-align: center; margin: 30px 0 10px; }
        .btn { display: inline-block; background-color: #00A9C3; color: #ffffff !important; text-decoration: none; padding: 12px 28px; border-radius: 10px; font-weight: bold; font-size: 13px; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="card">
        <!-- HEADER KOP SURAT RESMI ABC JAKARTA -->
        <div class="header">
            <h1>ADVENTIST BOOK CENTER (ABC) JAKARTA</h1>
            <p>Gereja Masehi Advent Hari Ketujuh Konferens DKI Jakarta & Sekitarnya</p>
        </div>

        <div class="content">
            <div class="greeting">Salam sejahtera, Bpk/Ibu {{ $user->name }} 🙏</div>
            
            <p style="font-size: 13px; line-height: 1.6; color: #475569;">
                Terima kasih telah mendaftarkan akun Anda pada sistem portal resmi penjualan dan distribusi literatur Advent ABC Jakarta. Berikut adalah rincian data pendaftaran Anda:
            </p>

            <!-- KOTAK DATA AKUN -->
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Nama Lengkap:</span>
                    <span class="info-value">{{ $user->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email Terdaftar:</span>
                    <span class="info-value">{{ $user->email }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Nomor WhatsApp:</span>
                    <span class="info-value">{{ $customer->phone }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Kategori Akun:</span>
                    <span class="info-value">{{ strtoupper($customer->type === 'penginjil' ? 'Penginjil Literatur (PL)' : $customer->type) }}</span>
                </div>
                <div class="info-row" style="margin-bottom: 0;">
                    <span class="info-label">Status Akun:</span>
                    <span>
                        @if($customer->type === 'penginjil')
                            <span class="badge badge-pending">⏳ MENUNGGU VERIFIKASI ADMIN</span>
                        @else
                            <span class="badge badge-active">✓ AKTIF</span>
                        @endif
                    </span>
                </div>
            </div>

            <!-- CATATAN KHUSUS PENGINJIL LITERATUR (ANTI-FRAUD) -->
            @if($customer->type === 'penginjil')
                <div style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 14px; font-size: 12px; color: #92400e; line-height: 1.5; margin-bottom: 20px;">
                    📌 <b>Pemberitahuan Khusus Penginjil Literatur:</b><br>
                    Untuk menjaga keaslian data dan mencegah penyalahgunaan tarif khusus, pendaftaran akun Anda sedang ditinjau oleh Staff Admin ABC Jakarta. Setelah diverifikasi oleh Admin, Anda akan mendapatkan akses ke <b>Harga Khusus PL</b> dan <b>Fasilitas Plafon Piutang hingga Rp 5.000.000</b>.
                </div>
            @else
                <p style="font-size: 13px; line-height: 1.6; color: #475569;">
                    Akun Anda telah <b>aktif</b>. Anda dapat langsung masuk ke portal web untuk memesan Buku Pelajaran Sekolah Sabat, Roh Nubuat, buku renungan, maupun perlengkapan ibadah lainnya secara mandiri.
                </p>
            @endif

            <div class="btn-wrapper">
                <a href="http://localhost:3000/login" class="btn">Masuk ke Portal ABC Jakarta</a>
            </div>
        </div>

        <!-- FOOTER KANTOR ABC JAKARTA -->
        <div class="footer">
            <p style="margin: 0 0 4px; font-weight: bold; color: #64748b;">Adventist Book Center (ABC) Jakarta</p>
            <p style="margin: 0 0 4px;">Jl. Dr. Saharjo No.48, Ps. Manggis, Kec. Setiabudi, Jakarta Selatan 12960</p>
            <p style="margin: 0;">Layanan Bantuan WhatsApp: 0813-7699-0897</p>
        </div>
    </div>
</body>
</html>