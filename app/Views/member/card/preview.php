<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <style>
        body {
            background: #f0f2f5;
            padding: 2rem 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        .card-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .digital-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .card-inner {
            aspect-ratio: 1.586;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 3rem;
            position: relative;
            color: white;
        }

        .card-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            opacity: 0.1;
            background-image:
                repeating-linear-gradient(45deg, transparent, transparent 35px, rgba(255,255,255,.1) 35px, rgba(255,255,255,.1) 70px);
        }

        .card-content {
            position: relative;
            z-index: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .card-logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .logo-icon {
            width: 70px;
            height: 70px;
            background: rgba(255,255,255,0.2);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
        }

        .logo-icon i {
            font-size: 40px;
        }

        .org-info h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .org-info p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .card-body-content {
            flex: 1;
            display: grid;
            grid-template-columns: 150px 1fr auto;
            gap: 2rem;
            align-items: center;
        }

        .photo-section {
            width: 150px;
            height: 180px;
            border-radius: 15px;
            overflow: hidden;
            background: rgba(255,255,255,0.1);
            border: 4px solid rgba(255,255,255,0.3);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }

        .photo-section img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.1);
        }

        .photo-placeholder i {
            font-size: 80px;
            opacity: 0.5;
        }

        .info-section h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .member-id {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            opacity: 0.95;
            letter-spacing: 2px;
            font-weight: 500;
        }

        .details-grid {
            display: grid;
            gap: 0.75rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-item i {
            font-size: 18px;
            opacity: 0.8;
        }

        .detail-label {
            min-width: 100px;
            opacity: 0.85;
            font-size: 0.9rem;
        }

        .detail-value {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .qr-section {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }

        .qr-section small {
            display: block;
            margin-top: 0.75rem;
            color: #666;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .card-footer-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1.5rem;
            margin-top: auto;
            border-top: 2px solid rgba(255,255,255,0.2);
        }

        .validity {
            font-size: 0.9rem;
        }

        .validity small {
            display: block;
            opacity: 0.8;
            font-size: 0.75rem;
        }

        .validity strong {
            font-size: 1.1rem;
        }

        .status-badge {
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.3);
            backdrop-filter: blur(10px);
        }

        .action-buttons {
            text-align: center;
            margin-top: 2rem;
        }

        .action-buttons .btn {
            margin: 0 0.5rem;
        }

        .no-print {
            /* Will be hidden in print */
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .digital-card {
                box-shadow: none;
                page-break-inside: avoid;
                margin: 0;
            }
        }

        @media (max-width: 768px) {
            .card-inner {
                padding: 1.5rem;
            }

            .card-body-content {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .photo-section {
                width: 120px;
                height: 150px;
                margin: 0 auto;
            }

            .info-section h2 {
                font-size: 1.5rem;
            }

            .member-id {
                font-size: 1rem;
            }

            .qr-section {
                margin: 0 auto;
            }
        }
    </style>
</head>
<body>
    <div class="card-container">
        <!-- Action Buttons -->
        <div class="action-buttons no-print mb-4">
            <button onclick="window.print()" class="btn btn-primary btn-lg">
                <i class="material-icons-outlined">print</i>
                Cetak Kartu
            </button>
            <a href="<?= base_url('member/card') ?>" class="btn btn-outline-secondary btn-lg">
                <i class="material-icons-outlined">arrow_back</i>
                Kembali
            </a>
        </div>

        <!-- Digital Card -->
        <div class="digital-card">
            <div class="card-inner">
                <div class="card-pattern"></div>

                <div class="card-content">
                    <!-- Logo/Header -->
                    <div class="card-logo">
                        <div class="logo-icon">
                            <i class="material-icons-outlined">groups</i>
                        </div>
                        <div class="org-info">
                            <h3>Serikat Pekerja Kampus</h3>
                            <p>Kartu Anggota Digital</p>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="card-body-content">
                        <!-- Photo -->
                        <div class="photo-section">
                            <?php if (!empty($member->photo_path)): ?>
                                <img src="<?= base_url($member->photo_path) ?>"
                                     alt="<?= esc($member->full_name) ?>">
                            <?php else: ?>
                                <div class="photo-placeholder">
                                    <i class="material-icons-outlined">person</i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Info -->
                        <div class="info-section">
                            <h2><?= esc($member->full_name ?? 'N/A') ?></h2>
                            <div class="member-id"><?= esc($member->member_number ?? 'N/A') ?></div>

                            <div class="details-grid">
                                <div class="detail-item">
                                    <i class="material-icons-outlined">school</i>
                                    <span class="detail-label">Universitas</span>
                                    <span class="detail-value"><?= esc($member->university_name ?? 'N/A') ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="material-icons-outlined">location_on</i>
                                    <span class="detail-label">Provinsi</span>
                                    <span class="detail-value"><?= esc($member->province_name ?? 'N/A') ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="material-icons-outlined">event</i>
                                    <span class="detail-label">Bergabung</span>
                                    <span class="detail-value">
                                        <?= !empty($member->join_date) ? date('d M Y', strtotime($member->join_date)) : 'N/A' ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- QR Code -->
                        <div class="qr-section">
                            <div id="qrcode"></div>
                            <small>SCAN UNTUK VERIFIKASI</small>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="card-footer-info">
                        <div class="validity">
                            <small>Berlaku hingga</small>
                            <strong><?= $cardStatus['expiration_date'] ?? 'N/A' ?></strong>
                        </div>
                        <div class="status-badge">
                            <?= $cardStatus['label'] ?? 'N/A' ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Text -->
        <div class="text-center text-muted no-print">
            <p>Kartu ini adalah kartu anggota digital resmi Serikat Pekerja Kampus</p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        // Generate QR Code
        document.addEventListener('DOMContentLoaded', function() {
            new QRCode(document.getElementById("qrcode"), {
                text: "<?= $qrCodeData ?>",
                width: 120,
                height: 120,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        });
    </script>
</body>
</html>
