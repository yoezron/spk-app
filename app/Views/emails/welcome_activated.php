<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat! Akun Anda Sudah Aktif</title>
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
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            padding: 40px 30px;
            text-align: center;
            color: #ffffff;
        }

        .email-header .icon {
            font-size: 64px;
            margin-bottom: 10px;
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

        .member-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            color: #ffffff;
            margin: 30px 0;
        }

        .member-card .label {
            font-size: 13px;
            opacity: 0.9;
            margin: 0 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .member-card .number {
            font-size: 32px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 2px;
        }

        .member-card .status {
            display: inline-block;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 15px;
            text-transform: uppercase;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 30px 0;
        }

        .feature-item {
            background-color: #f7fafc;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border: 2px solid #e2e8f0;
        }

        .feature-item .icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .feature-item h3 {
            margin: 0 0 8px 0;
            font-size: 16px;
            color: #2d3748;
        }

        .feature-item p {
            margin: 0;
            font-size: 13px;
            color: #718096;
            line-height: 1.5;
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
            margin: 5px;
        }

        .button.secondary {
            background: #ffffff;
            color: #667eea;
            border: 2px solid #667eea;
            box-shadow: none;
        }

        .info-box {
            background-color: #ebf8ff;
            border-left: 4px solid #4299e1;
            padding: 20px;
            margin: 30px 0;
            border-radius: 4px;
        }

        .info-box p {
            margin: 0 0 10px 0;
            font-size: 14px;
            line-height: 1.6;
            color: #2c5282;
        }

        .info-box p:last-child {
            margin: 0;
        }

        .info-box strong {
            color: #2d3748;
        }

        .quick-links {
            background-color: #f7fafc;
            padding: 30px;
            border-radius: 8px;
            margin: 30px 0;
        }

        .quick-links h3 {
            margin: 0 0 20px 0;
            font-size: 18px;
            color: #2d3748;
            text-align: center;
        }

        .quick-links ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .quick-links li {
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .quick-links li:last-child {
            border-bottom: none;
        }

        .quick-links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .quick-links a:hover {
            color: #764ba2;
        }

        .divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 30px 0;
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

            .feature-grid {
                grid-template-columns: 1fr;
            }

            .button {
                display: block;
                margin: 10px 0;
            }

            .member-card .number {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        <div class="email-content">

            <!-- Header -->
            <div class="email-header">
                <div class="icon">🎉</div>
                <h1>Akun Anda Sudah Aktif!</h1>
                <p>Selamat Bergabung di Keluarga SPK</p>
            </div>

            <!-- Body -->
            <div class="email-body">
                <p class="greeting">Selamat, <?= esc($name) ?>!</p>

                <p class="message">
                    Akun SPK Anda telah berhasil diaktivasi. Anda sekarang adalah anggota resmi
                    Serikat Pekerja Kampus dan dapat mengakses semua fitur dan layanan yang kami sediakan.
                </p>

                <!-- Member Card -->
                <div class="member-card">
                    <p class="label">Nomor Anggota Anda</p>
                    <h2 class="number"><?= esc($member_number) ?></h2>
                    <span class="status">✓ Anggota Aktif</span>
                </div>

                <p class="message" style="text-align: center;">
                    Simpan nomor anggota Anda dengan baik. Nomor ini akan digunakan untuk berbagai keperluan administrasi keanggotaan.
                </p>

                <!-- Feature Grid -->
                <div class="feature-grid">
                    <div class="feature-item">
                        <div class="icon">👤</div>
                        <h3>Profil Member</h3>
                        <p>Kelola data dan informasi pribadi Anda</p>
                    </div>
                    <div class="feature-item">
                        <div class="icon">🎫</div>
                        <h3>Kartu Anggota</h3>
                        <p>Download kartu anggota digital Anda</p>
                    </div>
                    <div class="feature-item">
                        <div class="icon">💬</div>
                        <h3>Forum Diskusi</h3>
                        <p>Berdiskusi dengan sesama anggota</p>
                    </div>
                    <div class="feature-item">
                        <div class="icon">📊</div>
                        <h3>Survey & Voting</h3>
                        <p>Partisipasi dalam keputusan organisasi</p>
                    </div>
                </div>

                <!-- CTA Buttons -->
                <div class="button-container">
                    <a href="<?= esc($login_url) ?>" class="button">
                        Login ke Portal
                    </a>
                    <a href="<?= esc($card_url) ?>" class="button secondary">
                        Download Kartu Anggota
                    </a>
                </div>

                <div class="divider"></div>

                <!-- Info Box -->
                <div class="info-box">
                    <p><strong>📌 Langkah Selanjutnya:</strong></p>
                    <p>✓ Login menggunakan email dan password yang sudah Anda set</p>
                    <p>✓ Lengkapi profil Anda jika ada informasi yang masih kurang</p>
                    <p>✓ Download dan simpan kartu anggota digital Anda</p>
                    <p>✓ Bergabung dengan grup WhatsApp wilayah Anda</p>
                    <p>✓ Mulai aktif di forum dan kegiatan SPK</p>
                </div>

                <!-- Quick Links -->
                <div class="quick-links">
                    <h3>Link Berguna</h3>
                    <ul>
                        <li>
                            <a href="<?= esc($profile_url) ?>">
                                <span>📋 Profil Saya</span>
                                <span>→</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= esc($card_url) ?>">
                                <span>🎫 Kartu Anggota</span>
                                <span>→</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('member/forum') ?>">
                                <span>💬 Forum Diskusi</span>
                                <span>→</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('member/payment') ?>">
                                <span>💳 Iuran & Pembayaran</span>
                                <span>→</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <p class="message">
                    Jika Anda memiliki pertanyaan atau membutuhkan bantuan, jangan ragu untuk menghubungi kami di
                    <a href="mailto:admin@spk.or.id" style="color: #667eea;">admin@spk.or.id</a>
                    atau melalui WhatsApp di <strong>+62 812-3456-7890</strong>.
                </p>

                <p class="message" style="text-align: center; font-weight: 600; color: #2d3748;">
                    Terima kasih telah bergabung bersama kami! 🙏
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