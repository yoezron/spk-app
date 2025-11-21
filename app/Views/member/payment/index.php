<?= $this->extend('layouts/member') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <h1 class="page-title"><?= $pageTitle ?></h1>
    <p class="text-muted">Kelola pembayaran iuran dan lihat riwayat transaksi Anda</p>
</div>

<!-- Payment Status Summary -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Pembayaran</h6>
                        <h3 class="mb-0"><?= number_format($stats['total_paid'] ?? 0) ?></h3>
                    </div>
                    <div class="avatar bg-primary-bright text-primary rounded">
                        <i class="material-icons-outlined">payment</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Menunggu Verifikasi</h6>
                        <h3 class="mb-0"><?= number_format($stats['pending'] ?? 0) ?></h3>
                    </div>
                    <div class="avatar bg-warning-bright text-warning rounded">
                        <i class="material-icons-outlined">pending</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Ditolak</h6>
                        <h3 class="mb-0"><?= number_format($stats['rejected'] ?? 0) ?></h3>
                    </div>
                    <div class="avatar bg-danger-bright text-danger rounded">
                        <i class="material-icons-outlined">cancel</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Terbayar</h6>
                        <h3 class="mb-0">Rp <?= number_format($stats['total_amount'] ?? 0, 0, ',', '.') ?></h3>
                    </div>
                    <div class="avatar bg-success-bright text-success rounded">
                        <i class="material-icons-outlined">account_balance_wallet</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Current Month Status -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="material-icons-outlined">calendar_today</i>
                    Status Pembayaran Bulan Ini
                </h5>
            </div>
            <div class="card-body">
                <?php
                $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                $currentMonthName = $months[$currentMonth];
                ?>

                <div class="text-center py-4">
                    <?php if ($hasPaidThisMonth): ?>
                        <i class="material-icons-outlined text-success" style="font-size: 64px;">check_circle</i>
                        <h4 class="mt-3 text-success">Sudah Dibayar</h4>
                        <p class="text-muted">Anda sudah membayar iuran untuk bulan <?= $currentMonthName ?> <?= $currentYear ?></p>
                    <?php else: ?>
                        <i class="material-icons-outlined text-warning" style="font-size: 64px;">warning</i>
                        <h4 class="mt-3 text-warning">Belum Dibayar</h4>
                        <p class="text-muted">Anda belum membayar iuran untuk bulan <?= $currentMonthName ?> <?= $currentYear ?></p>
                        <a href="<?= base_url('member/payment/create') ?>" class="btn btn-primary mt-2">
                            <i class="material-icons-outlined">upload</i>
                            Upload Bukti Pembayaran
                        </a>
                    <?php endif; ?>
                </div>

                <hr>

                <div class="row text-center">
                    <div class="col-md-4">
                        <h6 class="text-muted">Iuran Bulanan</h6>
                        <h5>Rp <?= number_format($paymentRates['monthly'] ?? 50000, 0, ',', '.') ?></h5>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Iuran Tahunan</h6>
                        <h5>Rp <?= number_format($paymentRates['annual'] ?? 500000, 0, ',', '.') ?></h5>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Registrasi</h6>
                        <h5>Rp <?= number_format($paymentRates['registration'] ?? 100000, 0, ',', '.') ?></h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Payments -->
        <?php if (!empty($pendingPayments)): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="material-icons-outlined">pending_actions</i>
                        Pembayaran Menunggu Verifikasi
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tanggal Upload</th>
                                    <th>Tipe</th>
                                    <th>Jumlah</th>
                                    <th>Periode</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingPayments as $payment): ?>
                                    <tr>
                                        <td><?= date('d M Y', strtotime($payment->created_at)) ?></td>
                                        <td>
                                            <span class="badge bg-info"><?= ucfirst($payment->payment_type) ?></span>
                                        </td>
                                        <td><strong>Rp <?= number_format($payment->amount, 0, ',', '.') ?></strong></td>
                                        <td>
                                            <?php if ($payment->period_month && $payment->period_year): ?>
                                                <?= $months[$payment->period_month] ?> <?= $payment->period_year ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= base_url('member/payment/detail/' . $payment->id) ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="material-icons-outlined" style="font-size: 16px;">visibility</i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Actions & Latest Payment -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="material-icons-outlined">flash_on</i>
                    Aksi Cepat
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= base_url('member/payment/create') ?>" class="btn btn-primary">
                        <i class="material-icons-outlined">upload</i>
                        Upload Bukti Pembayaran
                    </a>
                    <a href="<?= base_url('member/payment/history') ?>" class="btn btn-outline-secondary">
                        <i class="material-icons-outlined">history</i>
                        Lihat Riwayat Lengkap
                    </a>
                </div>
            </div>
        </div>

        <!-- Latest Payment -->
        <?php if (!empty($latestPayment)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="material-icons-outlined">receipt</i>
                        Pembayaran Terakhir
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Tanggal</small>
                        <p class="mb-0"><strong><?= date('d F Y', strtotime($latestPayment->payment_date)) ?></strong></p>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Tipe</small>
                        <p class="mb-0"><span class="badge bg-info"><?= ucfirst($latestPayment->payment_type) ?></span></p>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Jumlah</small>
                        <p class="mb-0"><strong>Rp <?= number_format($latestPayment->amount, 0, ',', '.') ?></strong></p>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Status</small>
                        <p class="mb-0"><span class="badge bg-success">Terverifikasi</span></p>
                    </div>
                    <?php if (!empty($latestPayment->verifier_name)): ?>
                        <div class="mb-2">
                            <small class="text-muted">Diverifikasi oleh</small>
                            <p class="mb-0"><?= esc($latestPayment->verifier_name) ?></p>
                        </div>
                    <?php endif; ?>
                    <a href="<?= base_url('member/payment/detail/' . $latestPayment->id) ?>" class="btn btn-sm btn-outline-primary mt-2">
                        Lihat Detail
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Payment Info -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="material-icons-outlined">info</i>
                    Informasi Pembayaran
                </h6>
                <ul class="small mb-0">
                    <li>Upload bukti transfer untuk verifikasi</li>
                    <li>Pembayaran diverifikasi dalam 1-3 hari kerja</li>
                    <li>Simpan bukti transfer dengan baik</li>
                    <li>Hubungi pengurus jika ada kendala</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
