<?= $this->extend('layouts/member') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title"><?= $title ?></h1>
            <p class="text-muted">Riwayat lengkap pembayaran iuran Anda</p>
        </div>
        <div class="col-auto">
            <a href="<?= base_url('member/payment') ?>" class="btn btn-secondary">
                <i class="material-icons-outlined">arrow_back</i> Kembali
            </a>
        </div>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row mb-3">
    <?php if (!empty($summaryByType)): ?>
        <?php
        $typeLabels = [
            'registration' => ['label' => 'Registrasi', 'icon' => 'person_add', 'color' => 'primary'],
            'monthly' => ['label' => 'Bulanan', 'icon' => 'calendar_month', 'color' => 'info'],
            'annual' => ['label' => 'Tahunan', 'icon' => 'event', 'color' => 'success'],
            'donation' => ['label' => 'Donasi', 'icon' => 'volunteer_activism', 'color' => 'warning']
        ];
        ?>
        <?php foreach ($summaryByType as $summary): ?>
            <?php
            $typeInfo = $typeLabels[$summary->payment_type] ?? ['label' => ucfirst($summary->payment_type), 'icon' => 'payment', 'color' => 'secondary'];
            ?>
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1"><?= $typeInfo['label'] ?></h6>
                                <h5 class="mb-0"><?= number_format($summary->count) ?>x</h5>
                                <small class="text-muted">Rp <?= number_format($summary->total, 0, ',', '.') ?></small>
                            </div>
                            <div class="avatar bg-<?= $typeInfo['color'] ?>-bright text-<?= $typeInfo['color'] ?> rounded">
                                <i class="material-icons-outlined"><?= $typeInfo['icon'] ?></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="material-icons-outlined">filter_list</i>
            Filter Riwayat
        </h5>
    </div>
    <div class="card-body">
        <form action="<?= base_url('member/payment/history') ?>" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="pending" <?= $filters['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="verified" <?= $filters['status'] == 'verified' ? 'selected' : '' ?>>Terverifikasi</option>
                    <option value="rejected" <?= $filters['status'] == 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="type" class="form-label">Tipe Pembayaran</label>
                <select name="type" id="type" class="form-select">
                    <option value="">Semua Tipe</option>
                    <option value="registration" <?= $filters['type'] == 'registration' ? 'selected' : '' ?>>Registrasi</option>
                    <option value="monthly" <?= $filters['type'] == 'monthly' ? 'selected' : '' ?>>Bulanan</option>
                    <option value="annual" <?= $filters['type'] == 'annual' ? 'selected' : '' ?>>Tahunan</option>
                    <option value="donation" <?= $filters['type'] == 'donation' ? 'selected' : '' ?>>Donasi</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="year" class="form-label">Tahun</label>
                <select name="year" id="year" class="form-select">
                    <?php foreach ($years as $y): ?>
                        <option value="<?= $y ?>" <?= $filters['year'] == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="month" class="form-label">Bulan</label>
                <select name="month" id="month" class="form-select">
                    <option value="">Semua Bulan</option>
                    <?php
                    $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    for ($m = 1; $m <= 12; $m++):
                    ?>
                        <option value="<?= $m ?>" <?= $filters['month'] == $m ? 'selected' : '' ?>><?= $months[$m] ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label d-none d-md-block">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="material-icons-outlined">search</i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Payment History Table -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="card-title mb-0">
                    <i class="material-icons-outlined">history</i>
                    Daftar Pembayaran
                </h5>
            </div>
            <div class="col-auto">
                <span class="badge bg-secondary"><?= count($payments) ?> Transaksi</span>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($payments)): ?>
            <div class="p-5 text-center text-muted">
                <i class="material-icons-outlined" style="font-size: 64px;">receipt_long</i>
                <p class="mt-3 mb-0">Tidak ada riwayat pembayaran</p>
                <small>Pembayaran Anda akan muncul di sini setelah upload bukti transfer</small>
                <br>
                <a href="<?= base_url('member/payment/create') ?>" class="btn btn-primary mt-3">
                    <i class="material-icons-outlined">upload</i>
                    Upload Bukti Pembayaran
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Tipe</th>
                            <th>Jumlah</th>
                            <th>Periode</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th>Diverifikasi</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td>
                                    <span data-bs-toggle="tooltip" title="<?= date('d F Y, H:i', strtotime($payment->payment_date)) ?>">
                                        <?= date('d M Y', strtotime($payment->payment_date)) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $typeColors = [
                                        'registration' => 'bg-primary',
                                        'monthly' => 'bg-info',
                                        'annual' => 'bg-success',
                                        'donation' => 'bg-warning'
                                    ];
                                    $typeColor = $typeColors[$payment->payment_type] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?= $typeColor ?>"><?= ucfirst($payment->payment_type) ?></span>
                                </td>
                                <td><strong>Rp <?= number_format($payment->amount, 0, ',', '.') ?></strong></td>
                                <td>
                                    <?php if ($payment->period_month && $payment->period_year): ?>
                                        <?php
                                        $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                                        echo $months[$payment->period_month] . ' ' . $payment->period_year;
                                        ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?= $payment->payment_method ? ucfirst(str_replace('_', ' ', $payment->payment_method)) : '-' ?></small>
                                </td>
                                <td>
                                    <?php
                                    $statusBadges = [
                                        'pending' => 'bg-warning',
                                        'verified' => 'bg-success',
                                        'rejected' => 'bg-danger'
                                    ];
                                    $statusLabels = [
                                        'pending' => 'Pending',
                                        'verified' => 'Terverifikasi',
                                        'rejected' => 'Ditolak'
                                    ];
                                    $statusClass = $statusBadges[$payment->status] ?? 'bg-secondary';
                                    $statusLabel = $statusLabels[$payment->status] ?? ucfirst($payment->status);
                                    ?>
                                    <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                                </td>
                                <td>
                                    <?php if ($payment->status == 'verified' && !empty($payment->verifier_full_name)): ?>
                                        <small><?= esc($payment->verifier_full_name) ?></small>
                                    <?php elseif ($payment->status == 'verified'): ?>
                                        <small><?= esc($payment->verifier_name ?? '-') ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?= base_url('member/payment/detail/' . $payment->id) ?>"
                                           class="btn btn-outline-primary"
                                           data-bs-toggle="tooltip"
                                           title="Lihat Detail">
                                            <i class="material-icons-outlined" style="font-size: 16px;">visibility</i>
                                        </a>
                                        <?php if (!empty($payment->proof_file)): ?>
                                            <a href="<?= base_url('member/payment/download/' . $payment->id) ?>"
                                               class="btn btn-outline-secondary"
                                               data-bs-toggle="tooltip"
                                               title="Download Bukti">
                                                <i class="material-icons-outlined" style="font-size: 16px;">download</i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php if (!empty($payments) && $pager): ?>
        <div class="card-footer">
            <?= $pager->links() ?>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
<?= $this->endSection() ?>
