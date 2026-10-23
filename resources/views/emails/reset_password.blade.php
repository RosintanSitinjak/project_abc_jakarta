<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Atur Ulang Kata Sandi</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #334155; margin: 0; padding: 20px; }
        .card { max-width: 550px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; }
        .header { background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%); padding: 25px; text-align: center; color: #ffffff; }
        .header h1 { margin: 0; font-size: 18px; font-weight: 800; }
        .content { padding: 30px 25px; }
        .btn-wrapper { text-align: center; margin: 25px 0; }
        .btn { display: inline-block; background-color: #00A9C3; color: #ffffff !important; text-decoration: none; padding: 12px 28px; border-radius: 10px; font-weight: bold; font-size: 13px; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>ADVENTIST BOOK CENTER (ABC) JAKARTA</h1>
        </div>
        <div class="content">
            <h3 style="color: #0f172a; margin-top: 0;">Permintaan Atur Ulang Kata Sandi 🔑</h3>
            <p style="font-size: 13px; line-height: 1.6; color: #475569;">
                Halo <b>{{ $user->name }}</b>,<br>
                Kami menerima permintaan untuk mengatur ulang kata sandi akun portal ABC Jakarta Anda. Silakan klik tombol di bawah ini untuk membuat kata sandi baru:
            </p>
            <div class="btn-wrapper">
                <a href="{{ $resetUrl }}" class="btn">Atur Ulang Kata Sandi Saya</a>
            </div>
            <p style="font-size: 12px; line-height: 1.5; color: #64748b; background: #f1f5f9; padding: 12px; border-radius: 8px;">
                ⚠️ <i>Link ini berlaku selama 60 menit. Jika Anda tidak merasa meminta perubahan kata sandi ini, abaikan email ini dan akun Anda tetap aman.</i>
            </p>
        </div>
        <div class="footer">
            <p style="margin: 0;">Adventist Book Center (ABC) Jakarta • Layanan WhatsApp: 0813-7699-0897</p>
        </div>
    </div>
</body>
</html>