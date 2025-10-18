<?= $this->extend('layouts/super') ?>

<?= $this->section('styles') ?>
<style>
    .members-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .role-info-banner {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .role-info-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .role-info-description {
        opacity: 0.9;
        margin-bottom: 0;
    }

    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
    }

    .member-table {
        margin-bottom: 0;
    }

    .member-table thead {
        background: #f8f9fa;
    }

    .member-table th {
        font-weight: 600;
        color: #2c3e50;
        border-bottom: 2px solid #dee2e6;
        padding: 1rem;
    }

    .member-table td {
        padding: 1rem;
        vertical-align: middle;
    }

    .member-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 1rem;
    }

    .member-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .member-details {
        flex: 1;
    }

    .member-name {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.25rem;
    }

    .member-email {
        font-size: 0.875rem;
        color: #6c757d;
        margin: 0;
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-active {
        background: #d4edda;
        color: #155724;
    }

    .status-inactive {
        background: #f8d7da;
        color: #721c24;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-state i {
        font-size: 4rem;
        color: #e9ecef;
        margin-bottom: 1rem;
    }

    .search-box {
        position: relative;
        max-width: 400px;
    }

    .search-box input {
        padding-left: 2.5rem;
    }

    .search-box i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-item {
        background: white;
        padding: 1rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #667eea;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #6c757d;
        margin: 0;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">
                <i class="material-icons-outlined align-middle">people</i>
                Members - <?= esc($role->title) ?>
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('super/dashboard') ?>">Super Admin</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('super/roles') ?>">Roles</a></li>
                    <li class="breadcrumb-item active"><?= esc($role->title) ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Role Info Banner -->
    <div class="role-info-banner">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="role-info-title">
                    <i class="material-icons-outlined align-middle">shield</i>
                    <?= esc($role->title) ?>
                </h2>
                <p class="role-info-description"><?= esc($role->description) ?></p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="<?= base_url('super/roles/' . $role->id . '/edit') ?>" class="btn btn-light">
                    <i class="material-icons-outlined align-middle">edit</i>
                    Edit Role
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-value"><?= number_format(count($members)) ?></div>
            <p class="stat-label">Total Members</p>
        </div>
        <div class="stat-item">
            <div class="stat-value">
                <?php
                $activeCount = 0;
                foreach ($members as $member) {
                    if ($member->active == 1) $activeCount++;
                }
                echo number_format($activeCount);
                ?>
            </div>
            <p class="stat-label">Active</p>
        </div>
        <div class="stat-item">
            <div class="stat-value">
                <?= number_format(count($members) - $activeCount) ?>
            </div>
            <p class="stat-label">Inactive</p>
        </div>
    </div>

    <!-- Members Table -->
    <div class="members-card">
        <div class="row mb-4">
            <div class="col-md-6">
                <h5 class="mb-0">
                    <i class="material-icons-outlined align-middle">list</i>
                    Daftar Members
                </h5>
            </div>
            <div class="col-md-6">
                <div class="search-box ms-auto">
                    <i class="material-icons-outlined">search</i>
                    <input type="text"
                        class="form-control"
                        id="searchMember"
                        placeholder="Cari nama atau email...">
                </div>
            </div>
        </div>

        <?php if (!empty($members)): ?>
            <div class="table-responsive">
                <table class="table member-table" id="membersTable">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Bergabung</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $member): ?>
                            <tr>
                                <td>
                                    <div class="member-info">
                                        <div class="member-avatar">
                                            <?= strtoupper(substr($member->username, 0, 1)) ?>
                                        </div>
                                        <div class="member-details">
                                            <div class="member-name"><?= esc($member->username) ?></div>
                                            <small class="text-muted">ID: <?= $member->id ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="member-email"><?= esc($member->email) ?></p>
                                </td>
                                <td>
                                    <span class="status-badge <?= $member->active ? 'status-active' : 'status-inactive' ?>">
                                        <i class="material-icons-outlined" style="font-size: 14px;">
                                            <?= $member->active ? 'check_circle' : 'cancel' ?>
                                        </i>
                                        <?= $member->active ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d M Y', strtotime($member->created_at)) ?>
                                    </small>
                                </td>
                                <td>
                                    <a href="<?= base_url('super/users/' . $member->id) ?>"
                                        class="btn btn-sm btn-outline-primary"
                                        title="View Details">
                                        <i class="material-icons-outlined">visibility</i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="material-icons-outlined">person_off</i>
                <h4>Belum Ada Member</h4>
                <p class="text-muted">Role ini belum memiliki member yang terdaftar</p>
                <a href="<?= base_url('super/roles') ?>" class="btn btn-outline-primary mt-3">
                    <i class="material-icons-outlined align-middle">arrow_back</i>
                    Kembali ke Roles
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Back Button -->
    <div class="row mt-4">
        <div class="col-12">
            <a href="<?= base_url('super/roles') ?>" class="btn btn-outline-secondary">
                <i class="material-icons-outlined align-middle">arrow_back</i>
                Kembali ke Roles
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Search functionality
    document.getElementById('searchMember')?.addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('#membersTable tbody tr');

        rows.forEach(row => {
            const username = row.querySelector('.member-name')?.textContent.toLowerCase() || '';
            const email = row.querySelector('.member-email')?.textContent.toLowerCase() || '';

            if (username.includes(searchValue) || email.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>
<?= $this->endSection() ?>