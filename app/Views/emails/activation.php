<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivasi Akun SPK</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f4f4f7;
            color: #51545e;
        }

        .email-wrapper {
            width: 100%;
            background-color: #f4f4f7;
            padding: 20px 0;
        }

        .email-content {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: #ffffff;
        }

        .email-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }

        .email-header p {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }

        .email-body {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            margin: 0 0 20px 0;
        }

        .message {
            font-size: 15px;
            line-height: 1.8;
            color: #51545e;
            margin: 0 0 30px 0;
        }

        .info-box {
            background-color: #f7fafc;
            border-left: 4px solid #4299e1;
            padding: 20px;
            margin: 30px 0;
            border-radius: 4px;
        }

        .info-box p {
            margin: 0 0 10px 0;
            font-size: 14px;
            line-height: 1.6;
        }

        .info-box p:last-child {
            margin: 0;
        }

        .info-box strong {
            color: #2d3748;
        }

        .button-container {
            text-align: center;
            margin: 30px 0;
        }

        .button {
            display: inline-block;
            padding: 16px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .button:hover {
            opacity: 0.9;
        }

        .expiry-notice {
            text-align: center;
            font-size: 13px;
            color: #718096;
            margin: 20px 0;
        }

        .divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 30px 0;
        }

        .alternative-link {
            background-color: #f7fafc;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
        }

        .alternative-link p {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #4a5568;
        }

        .alternative-link a {
            color: #667eea;
            word-break: break-all;
            font-size: 13px;
        }

        .email-footer {
            background-color: #2d3748;
            padding: 30px;
            text-align: center;
            color: #a0aec0;
        }

        .email-footer p {
            margin: 0 0 10px 0;
            font-size: 14px;
        }

        .email-footer p:last-child {
            margin: 0;
        }

        .email-footer a {
            color: #667eea;
            text-decoration: none;
        }

        .social-links {
            margin: 20px 0;
        }

        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #a0aec0;
            text-decoration: none;
            font-size: 14px;
        }

        @media only screen and (max-width: 600px) {
            .email-content {
                border-radius: 0;
            }

            .email-header,
            .email-body,
            .email-footer {
                padding: 30px 20px;
            }

            .email-header h1 {
                font-size: 24px;
            }

            .button {
                display: block;
                padding: 14px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        <div class="email-content">

            <!-- Header -->
            <div class="email-header">
                <h1>🎉 Selamat Datang di SPK!</h1>
                <p>Aktivasi Akun Anda</p>
            </div>

            <!-- Body -->
            <div class="email-body">
                <p class="greeting">Halo, <?= esc($name) ?>!</p>

                <p class="message">
                    Data Anda telah berhasil diimport ke sistem Serikat Pekerja Kampus (SPK).
                    Untuk mengaktifkan akun Anda dan mulai mengakses semua fitur member,
                    silakan klik tombol aktivasi di bawah ini.
                </p>

                <!-- CTA Button -->
                <div class="button-container">
                    <a href="<?= esc($activation_link) ?>" class="button">
                        Aktivasi Akun Saya
                    </a>
                </div>

                <p class="expiry-notice">
                    ⏰ Link aktivasi berlaku hingga: <strong><?= esc($expires_at) ?></strong>
                </p>

                <!-- Info Box -->
                <div class="info-box">
                    <p><strong>Yang perlu Anda lakukan:</strong></p>
                    <p>1️⃣ Klik tombol "Aktivasi Akun Saya"</p>
                    <p>2️⃣ Set password baru untuk akun Anda</p>
                    <p>3️⃣ Review dan update profil Anda</p>
                    <p>4️⃣ Download kartu anggota digital</p>
                </div>

                <div class="divider"></div>

                <!-- Alternative Link -->
                <div class="alternative-link">
                    <p><strong>Atau copy link berikut ke browser Anda:</strong></p>
                    <a href="<?= esc($activation_link) ?>"><?= esc($activation_link) ?></a>
                </div>

                <p class="message">
                    Jika Anda tidak merasa mendaftar atau ada pertanyaan, silakan hubungi kami di
                    <a href="mailto:admin@spk.or.id" style="color: #667eea;">admin@spk.or.id</a>
                </p>
            </div>

            <!-- Footer -->
            <div class="email-footer">
                <p><strong>Serikat Pekerja Kampus (SPK)</strong></p>
                <p>Bersatu Untuk Kesejahteraan Pekerja Kampus</p>

                <div class="social-links">
                    <a href="#">Facebook</a> |
                    <a href="#">Twitter</a> |
                    <a href="#">Instagram</a> |
                    <a href="#">LinkedIn</a>
                </div>

                <p style="margin-top: 20px; font-size: 12px;">
                    Email ini dikirim otomatis oleh sistem. Mohon tidak membalas email ini.
                </p>
                <p style="font-size: 12px;">
                    &copy; <?= date('Y') ?> SPK. All rights reserved.
                </p>
            </div>

        </div>
    </div>
</body>

</html>