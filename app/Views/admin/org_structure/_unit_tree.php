<?php
/**
 * Partial View: Unit Tree (Recursive)
 * Displays organizational unit with positions and sub-units
 */
$level = $level ?? 0;
$unit = $unit ?? null;

if (!$unit) return;
?>

<div class="unit-item" style="margin-left: <?= $level * 30 ?>px">
    <div class="unit-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="unit-name">
                    <i class="material-icons-outlined" style="font-size: 18px; vertical-align: middle;">corporate_fare</i>
                    <?= esc($unit['name'] ?? 'N/A') ?>
                    <span class="badge bg-secondary badge-scope ms-2"><?= esc(strtoupper($unit['scope'] ?? '')) ?></span>
                </div>
                <?php if (!empty($unit['description'])): ?>
                    <small class="text-muted d-block mt-1"><?= esc($unit['description']) ?></small>
                <?php endif; ?>
            </div>
            <div>
                <?php if (isset($can_manage) && $can_manage): ?>
                    <a href="<?= base_url('admin/org-structure/unit/' . ($unit['id'] ?? 0)) ?>"
                       class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                        <i class="material-icons-outlined">visibility</i>
                    </a>
                    <a href="<?= base_url('admin/org-structure/unit/' . ($unit['id'] ?? 0) . '/edit') ?>"
                       class="btn btn-sm btn-outline-info" title="Edit">
                        <i class="material-icons-outlined">edit</i>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                            data-unit-id="<?= $unit['id'] ?? 0 ?>" title="Hapus">
                        <i class="material-icons-outlined">delete</i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Positions in this unit -->
    <?php if (!empty($unit['positions']) && is_array($unit['positions'])): ?>
        <div class="positions-list" style="display: none;">
            <h6 class="text-muted mb-2">
                <i class="material-icons-outlined" style="font-size: 16px; vertical-align: middle;">work</i>
                Posisi/Jabatan
            </h6>
            <?php foreach ($unit['positions'] as $position): ?>
                <div class="position-item">
                    <div>
                        <div class="position-title">
                            <?= esc($position['title'] ?? 'N/A') ?>
                            <?php if (!empty($position['position_type'])): ?>
                                <span class="badge bg-info ms-2" style="font-size: 10px;">
                                    <?= esc(ucfirst($position['position_type'])) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($position['current_holders'])): ?>
                            <?php foreach ($position['current_holders'] as $holder): ?>
                                <div class="position-holder">
                                    <i class="material-icons-outlined" style="font-size: 14px; vertical-align: middle;">person</i>
                                    <?= esc($holder['full_name'] ?? $holder['email'] ?? 'N/A') ?>
                                    <?php if (!empty($holder['started_at'])): ?>
                                        <small>(sejak <?= date('d/m/Y', strtotime($holder['started_at'])) ?>)</small>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="position-holder text-warning">
                                <i class="material-icons-outlined" style="font-size: 14px; vertical-align: middle;">warning</i>
                                Posisi kosong
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <?php if (isset($can_manage) && $can_manage): ?>
                            <a href="<?= base_url('admin/org-structure/position/' . ($position['id'] ?? 0) . '/edit') ?>"
                               class="btn btn-sm btn-outline-info" title="Edit Posisi">
                                <i class="material-icons-outlined">edit</i>
                            </a>
                        <?php endif; ?>
                        <?php if (isset($can_assign) && $can_assign): ?>
                            <a href="<?= base_url('admin/org-structure/position/' . ($position['id'] ?? 0) . '/assign') ?>"
                               class="btn btn-sm btn-outline-success" title="Assign Anggota">
                                <i class="material-icons-outlined">person_add</i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Sub-units (recursive) -->
    <?php if (!empty($unit['children']) && is_array($unit['children'])): ?>
        <div class="sub-units" style="display: none;">
            <?php foreach ($unit['children'] as $childUnit): ?>
                <?= $this->include('admin/org_structure/_unit_tree', [
                    'unit' => $childUnit,
                    'level' => $level + 1,
                    'can_manage' => $can_manage ?? false,
                    'can_assign' => $can_assign ?? false
                ]) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
