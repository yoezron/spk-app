<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title"><?= $title ?></h1>
            <p class="text-muted">Verifikasi bukti pembayaran iuran anggota yang masuk</p>
        </div>
        <div class="col-auto">
            <div class="btn-group" role="group">
                <a href="<?= base_url('admin/payment') ?>" class="btn btn-secondary">
                    <i class="material-icons-outlined">arrow_back</i> Kembali
                </a>
                <a href="<?= base_url('admin/payment/report') ?>" class="btn btn-outline-primary">
                    <i class="material-icons-outlined">assessment</i> Laporan
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Card -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white mb-1">Menunggu Verifikasi</h6>
                        <h2 class="mb-0"><?= number_format($totalPending ?? 0) ?> Pembayaran</h2>
                    </div>
                    <div class="display-4">
                        <i class="material-icons-outlined">pending_actions</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Payments List -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="card-title mb-0">Daftar Pembayaran Pending</h5>
            </div>
            <div class="col-auto">
                <span class="badge bg-warning"><?= count($payments) ?> dari <?= $totalPending ?></span>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($payments)): ?>
            <div class="p-5 text-center text-muted">
                <i class="material-icons-outlined" style="font-size: 64px;">check_circle</i>
                <p class="mt-3 mb-0">Tidak ada pembayaran yang menunggu verifikasi</p>
                <small>Pembayaran baru yang diunggah anggota akan muncul di sini</small>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal Upload</th>
                            <th>Anggota</th>
                            <th>Kampus</th>
                            <th>Tipe</th>
                            <th>Jumlah</th>
                            <th>Periode</th>
                            <th>Bukti</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td>
                                    <span data-bs-toggle="tooltip" title="<?= date('d F Y, H:i:s', strtotime($payment->created_at)) ?>">
                                        <?= date('d M Y', strtotime($payment->created_at)) ?>
                                    </span>
                                    <br>
                                    <small class="text-muted"><?= date('H:i', strtotime($payment->created_at)) ?></small>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <strong><?= esc($payment->full_name) ?></strong>
                                        <small class="text-muted"><?= esc($payment->username) ?></small>
                                        <?php if (!empty($payment->phone)): ?>
                                            <small class="text-muted">
                                                <i class="material-icons-outlined" style="font-size: 12px;">phone</i>
                                                <?= esc($payment->phone) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <small><?= esc($payment->university_name ?? '-') ?></small>
                                </td>
                                <td>
                                    <?php
                                    $typeLabels = [
                                        'registrasi' => ['label' => 'Registrasi', 'class' => 'bg-primary'],
                                        'iuran' => ['label' => 'Iuran', 'class' => 'bg-info'],
                                        'lainnya' => ['label' => 'Lainnya', 'class' => 'bg-secondary']
                                    ];
                                    $type = $typeLabels[$payment->payment_type] ?? ['label' => 'Unknown', 'class' => 'bg-secondary'];
                                    ?>
                                    <span class="badge <?= $type['class'] ?>"><?= $type['label'] ?></span>
                                </td>
                                <td>
                                    <strong>Rp <?= number_format($payment->amount, 0, ',', '.') ?></strong>
                                </td>
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
                                    <?php if (!empty($payment->proof_path)): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#proofModal<?= $payment->id ?>">
                                            <i class="material-icons-outlined" style="font-size: 16px;">image</i>
                                            Lihat
                                        </button>
                                    <?php else: ?>
                                        <span class="text-danger">Tidak ada</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- Approve -->
                                        <button type="button" class="btn btn-success"
                                                data-bs-toggle="modal"
                                                data-bs-target="#approveModal<?= $payment->id ?>">
                                            <i class="material-icons-outlined" style="font-size: 16px;">check_circle</i>
                                            Setujui
                                        </button>

                                        <!-- Reject -->
                                        <button type="button" class="btn btn-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#rejectModal<?= $payment->id ?>">
                                            <i class="material-icons-outlined" style="font-size: 16px;">cancel</i>
                                            Tolak
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Proof Image Modal -->
                            <?php if (!empty($payment->proof_path)): ?>
                                <div class="modal fade" id="proofModal<?= $payment->id ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Bukti Pembayaran</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <img src="<?= base_url('uploads/payments/' . $payment->proof_path) ?>"
                                                     alt="Bukti Pembayaran"
                                                     class="img-fluid">
                                                <div class="mt-3">
                                                    <p><strong>Catatan:</strong> <?= esc($payment->notes ?? 'Tidak ada catatan') ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Approve Modal -->
                            <div class="modal fade" id="approveModal<?= $payment->id ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="<?= base_url('admin/payment/verify/' . $payment->id) ?>" method="POST">
                                            <?= csrf_field() ?>
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title">Setujui Pembayaran</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Anda akan menyetujui pembayaran dari:</p>
                                                <ul>
                                                    <li><strong>Anggota:</strong> <?= esc($payment->full_name) ?></li>
                                                    <li><strong>Jumlah:</strong> Rp <?= number_format($payment->amount, 0, ',', '.') ?></li>
                                                    <li><strong>Tipe:</strong> <?= ucfirst($payment->payment_type) ?></li>
                                                </ul>
                                                <div class="mb-3">
                                                    <label for="notes<?= $payment->id ?>" class="form-label">Catatan (Opsional)</label>
                                                    <textarea name="notes" id="notes<?= $payment->id ?>" class="form-control" rows="3" placeholder="Tambahkan catatan verifikasi..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-success">
                                                    <i class="material-icons-outlined">check_circle</i>
                                                    Setujui Pembayaran
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal<?= $payment->id ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="<?= base_url('admin/payment/reject/' . $payment->id) ?>" method="POST">
                                            <?= csrf_field() ?>
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">Tolak Pembayaran</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="alert alert-warning">
                                                    <i class="material-icons-outlined">warning</i>
                                                    Anggota akan menerima notifikasi penolakan dan diminta untuk mengunggah bukti pembayaran baru.
                                                </div>
                                                <p>Menolak pembayaran dari:</p>
                                                <ul>
                                                    <li><strong>Anggota:</strong> <?= esc($payment->full_name) ?></li>
                                                    <li><strong>Jumlah:</strong> Rp <?= number_format($payment->amount, 0, ',', '.') ?></li>
                                                </ul>
                                                <div class="mb-3">
                                                    <label for="reason<?= $payment->id ?>" class="form-label">
                                                        Alasan Penolakan <span class="text-danger">*</span>
                                                    </label>
                                                    <textarea name="reason" id="reason<?= $payment->id ?>" class="form-control" rows="4" required placeholder="Contoh: Bukti pembayaran tidak jelas, nomor rekening tidak sesuai, dll..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="material-icons-outlined">cancel</i>
                                                    Tolak Pembayaran
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
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

<!-- Info Box -->
<div class="alert alert-info mt-3">
    <i class="material-icons-outlined">info</i>
    <strong>Tips Verifikasi:</strong>
    <ul class="mb-0 mt-2">
        <li>Periksa bukti pembayaran dengan teliti (nomor rekening, jumlah, tanggal)</li>
        <li>Pastikan jumlah yang dibayarkan sesuai dengan nominal yang tertera</li>
        <li>Hubungi anggota jika ada keraguan sebelum menolak</li>
    </ul>
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
