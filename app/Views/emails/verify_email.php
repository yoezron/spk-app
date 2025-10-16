<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Verifikasi Email - SPK</title>
    <style>
        /* Reset styles */
        body,
        table,
        td,
        a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table,
        td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }

        /* Body styles */
        body {
            margin: 0;
            padding: 0;
            width: 100% !important;
            height: 100% !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
        }

        /* Container */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }

        /* Header */
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

        /* Body */
        .email-body {
            padding: 40px 30px;
            color: #333333;
            line-height: 1.6;
        }

        .email-body h2 {
            color: #667eea;
            font-size: 22px;
            margin: 0 0 20px 0;
        }

        .email-body p {
            margin: 0 0 15px 0;
            font-size: 15px;
        }

        /* Greeting box */
        .greeting-box {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }

        .greeting-box p {
            margin: 0;
            font-size: 16px;
            color: #333333;
        }

        .greeting-box strong {
            color: #667eea;
            font-size: 18px;
        }

        /* Button */
        .btn-container {
            text-align: center;
            margin: 35px 0;
        }

        .btn-verify {
            display: inline-block;
            padding: 16px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-align: center;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-verify:hover {
            opacity: 0.9;
        }

        /* Info box */
        .info-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            margin: 25px 0;
            border-radius: 4px;
        }

        .info-box p {
            margin: 0;
            color: #856404;
            font-size: 14px;
        }

        .info-box strong {
            color: #856404;
        }

        /* Alternative link */
        .alt-link {
            background-color: #f8f9fa;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
            border: 1px dashed #dee2e6;
        }

        .alt-link p {
            margin: 0 0 10px 0;
            font-size: 13px;
            color: #666666;
        }

        .alt-link a {
            color: #667eea;
            word-break: break-all;
            font-size: 12px;
        }

        /* Instructions */
        .instructions {
            margin: 25px 0;
        }

        .instructions ol {
            margin: 0;
            padding-left: 20px;
        }

        .instructions li {
            margin-bottom: 10px;
            font-size: 15px;
            color: #333333;
        }

        /* Footer */
        .email-footer {
            background-color: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }

        .email-footer p {
            margin: 0 0 10px 0;
            font-size: 13px;
            color: #666666;
        }

        .email-footer a {
            color: #667eea;
            text-decoration: none;
        }

        .social-links {
            margin: 20px 0 0 0;
        }

        .social-links a {
            display: inline-block;
            margin: 0 8px;
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }

        /* Responsive */
        @media screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
            }

            .email-header,
            .email-body,
            .email-footer {
                padding: 20px !important;
            }

            .btn-verify {
                padding: 14px 30px !important;
                font-size: 15px !important;
            }
        }
    </style>
</head>

<body>
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f4f4f4;">
        <tr>
            <td style="padding: 20px 0;">
                <table class="email-container" cellspacing="0" cellpadding="0" border="0" align="center" width="600">

                    <!-- Header -->
                    <tr>
                        <td class="email-header">
                            <!-- Logo placeholder - ganti dengan logo SPK jika ada -->
                            <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.2); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
                                <span style="font-size: 36px; font-weight: bold;">SPK</span>
                            </div>
                            <h1>Verifikasi Email Anda</h1>
                            <p>Serikat Pekerja Kampus</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td class="email-body">
                            <div class="greeting-box">
                                <p>Halo, <strong><?= esc($fullName ?? 'Calon Anggota') ?></strong>!</p>
                            </div>

                            <p>
                                Terima kasih telah mendaftar sebagai anggota <strong>Serikat Pekerja Kampus (SPK)</strong>.
                                Kami sangat senang Anda bergabung dalam perjuangan untuk meningkatkan kesejahteraan
                                pekerja di sektor pendidikan tinggi Indonesia.
                            </p>

                            <p>
                                Untuk melanjutkan proses pendaftaran, silakan verifikasi alamat email Anda dengan
                                mengklik tombol di bawah ini:
                            </p>

                            <!-- Verify Button -->
                            <div class="btn-container">
                                <a href="<?= esc($verificationLink) ?>" class="btn-verify" target="_blank">
                                    ✓ Verifikasi Email Saya
                                </a>
                            </div>

                            <!-- Important Info -->
                            <div class="info-box">
                                <p>
                                    <strong>⏰ Penting:</strong> Link verifikasi ini berlaku selama <strong>60 menit</strong>.
                                    Jika link kedaluwarsa, Anda dapat meminta link verifikasi baru dari halaman login.
                                </p>
                            </div>

                            <!-- Alternative Link -->
                            <div class="alt-link">
                                <p><strong>Tidak bisa klik tombol di atas?</strong></p>
                                <p>Salin dan tempel link berikut ke browser Anda:</p>
                                <a href="<?= esc($verificationLink) ?>" target="_blank"><?= esc($verificationLink) ?></a>
                            </div>

                            <h2>Langkah Selanjutnya:</h2>
                            <div class="instructions">
                                <ol>
                                    <li><strong>Verifikasi email</strong> dengan mengklik link di atas</li>
                                    <li><strong>Tunggu persetujuan</strong> dari pengurus SPK (biasanya 1-3 hari kerja)</li>
                                    <li><strong>Cek email</strong> untuk notifikasi persetujuan</li>
                                    <li><strong>Login</strong> ke portal anggota untuk mengakses layanan</li>
                                </ol>
                            </div>

                            <p style="margin-top: 30px;">
                                Jika Anda <strong>tidak mendaftar</strong> di website SPK, silakan abaikan email ini.
                                Akun Anda tidak akan diaktifkan tanpa verifikasi email.
                            </p>

                            <p style="margin-top: 25px; padding-top: 25px; border-top: 1px solid #dee2e6;">
                                <strong>Butuh bantuan?</strong><br>
                                Jika Anda mengalami kesulitan atau memiliki pertanyaan, jangan ragu untuk menghubungi kami:
                            </p>
                            <p style="margin: 10px 0;">
                                📧 Email: <a href="mailto:<?= esc($contactEmail ?? 'admin@spk.org') ?>" style="color: #667eea; text-decoration: none;"><?= esc($contactEmail ?? 'admin@spk.org') ?></a><br>
                                📱 WhatsApp: <a href="tel:<?= esc($contactPhone ?? '+62812345678') ?>" style="color: #667eea; text-decoration: none;"><?= esc($contactPhone ?? '+62 812-3456-7890') ?></a><br>
                                🌐 Website: <a href="<?= base_url() ?>" style="color: #667eea; text-decoration: none;"><?= base_url() ?></a>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="email-footer">
                            <p style="font-weight: 600; color: #333333; margin-bottom: 15px;">
                                Solidaritas Pekerja Kampus untuk Indonesia yang Lebih Baik!
                            </p>

                            <p>
                                Email ini dikirim secara otomatis oleh sistem SPK.<br>
                                Mohon tidak membalas email ini.
                            </p>

                            <!-- Social Links -->
                            <div class="social-links">
                                <a href="<?= esc($facebookUrl ?? '#') ?>" target="_blank">Facebook</a> |
                                <a href="<?= esc($twitterUrl ?? '#') ?>" target="_blank">Twitter</a> |
                                <a href="<?= esc($instagramUrl ?? '#') ?>" target="_blank">Instagram</a> |
                                <a href="<?= esc($linkedinUrl ?? '#') ?>" target="_blank">LinkedIn</a>
                            </div>

                            <p style="margin-top: 20px; font-size: 12px; color: #999999;">
                                © <?= date('Y') ?> Serikat Pekerja Kampus (SPK). All rights reserved.<br>
                                Jl. Contoh No. 123, Jakarta 12345, Indonesia
                            </p>

                            <p style="margin-top: 15px; font-size: 11px; color: #999999;">
                                Anda menerima email ini karena mendaftar di website SPK.<br>
                                <a href="<?= base_url('pages/privacy') ?>" style="color: #999999;">Kebijakan Privasi</a> |
                                <a href="<?= base_url('pages/terms') ?>" style="color: #999999;">Syarat & Ketentuan</a>
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>