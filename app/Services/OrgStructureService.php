<?php

namespace App\Services;

use App\Models\OrgUnitModel;
use App\Models\OrgPositionModel;
use App\Models\OrgAssignmentModel;
use App\Models\UserModel;
use App\Models\MemberProfileModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * OrgStructureService
 * 
 * Service layer untuk mengelola struktur organisasi SPK
 * Menangani business logic untuk units, positions, dan assignments
 * Menyediakan operasi CRUD dengan validasi dan error handling
 * 
 * Features:
 * - Unit management (CRUD, hierarchy)
 * - Position management (CRUD, job descriptions)
 * - Assignment operations (assign, transfer, end)
 * - Statistics & reporting
 * - Data validation & business rules
 * - Transaction management
 * 
 * @package App\Services
 * @author  SPK Development Team
 * @version 1.0.0
 */
class OrgStructureService
{
    /**
     * @var OrgUnitModel
     */
    protected $unitModel;

    /**
     * @var OrgPositionModel
     */
    protected $positionModel;

    /**
     * @var OrgAssignmentModel
     */
    protected $assignmentModel;

    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * @var MemberProfileModel
     */
    protected $memberModel;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->unitModel = new OrgUnitModel();
        $this->positionModel = new OrgPositionModel();
        $this->assignmentModel = new OrgAssignmentModel();
        $this->userModel = new UserModel();
        $this->memberModel = new MemberProfileModel();
    }

    // =====================================================
    // UNIT MANAGEMENT
    // =====================================================

    /**
     * Get organizational hierarchy
     * 
     * @param array $filters Optional filters
     * @return array
     */
    public function getHierarchy(array $filters = []): array
    {
        try {
            $hierarchy = $this->unitModel->getHierarchy($filters);

            return [
                'success' => true,
                'data' => $hierarchy,
                'message' => 'Struktur organisasi berhasil dimuat'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error getting hierarchy: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal memuat struktur organisasi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get full unit with all relations
     * 
     * @param int $unitId Unit ID
     * @return array
     */
    public function getUnitDetail(int $unitId): array
    {
        try {
            $unit = $this->unitModel->getFullUnit($unitId);

            if (!$unit) {
                return [
                    'success' => false,
                    'message' => 'Unit tidak ditemukan'
                ];
            }

            // Get positions with assignments
            $positions = $this->positionModel->getUnitPositionsWithAssignments($unitId);
            $unit['positions_detail'] = $positions;

            return [
                'success' => true,
                'data' => $unit,
                'message' => 'Detail unit berhasil dimuat'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error getting unit detail: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal memuat detail unit: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create new organizational unit
     * 
     * @param array $data Unit data
     * @return array
     */
    public function createUnit(array $data): array
    {
        try {
            // Auto-generate display order if not provided
            if (!isset($data['display_order'])) {
                $data['display_order'] = $this->unitModel->getNextDisplayOrder($data['parent_id'] ?? null);
            }

            // Set default values
            $data['is_active'] = $data['is_active'] ?? 1;

            $unitId = $this->unitModel->insert($data);

            if (!$unitId) {
                return [
                    'success' => false,
                    'errors' => $this->unitModel->errors(),
                    'message' => 'Gagal membuat unit organisasi'
                ];
            }

            return [
                'success' => true,
                'data' => ['id' => $unitId],
                'message' => 'Unit organisasi berhasil dibuat'
            ];
        } catch (DatabaseException $e) {
            log_message('error', 'Database error creating unit: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan database: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error creating unit: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal membuat unit: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update organizational unit
     * 
     * @param int $unitId Unit ID
     * @param array $data Update data
     * @return array
     */
    public function updateUnit(int $unitId, array $data): array
    {
        try {
            $unit = $this->unitModel->find($unitId);

            if (!$unit) {
                return [
                    'success' => false,
                    'message' => 'Unit tidak ditemukan'
                ];
            }

            // Prevent circular parent relationship
            if (isset($data['parent_id']) && $data['parent_id']) {
                if ($this->wouldCreateCircularReference($unitId, $data['parent_id'])) {
                    return [
                        'success' => false,
                        'message' => 'Tidak dapat mengatur parent: akan membuat referensi melingkar'
                    ];
                }
            }

            $updated = $this->unitModel->update($unitId, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'errors' => $this->unitModel->errors(),
                    'message' => 'Gagal mengupdate unit organisasi'
                ];
            }

            return [
                'success' => true,
                'message' => 'Unit organisasi berhasil diupdate'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error updating unit: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengupdate unit: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete organizational unit
     * 
     * @param int $unitId Unit ID
     * @return array
     */
    public function deleteUnit(int $unitId): array
    {
        try {
            $unit = $this->unitModel->find($unitId);

            if (!$unit) {
                return [
                    'success' => false,
                    'message' => 'Unit tidak ditemukan'
                ];
            }

            // Check if unit has children
            if ($this->unitModel->hasChildren($unitId)) {
                return [
                    'success' => false,
                    'message' => 'Unit memiliki sub-unit. Hapus sub-unit terlebih dahulu.'
                ];
            }

            // Check if unit has positions
            if ($this->unitModel->hasPositions($unitId)) {
                return [
                    'success' => false,
                    'message' => 'Unit memiliki posisi/jabatan. Hapus posisi terlebih dahulu.'
                ];
            }

            $deleted = $this->unitModel->delete($unitId);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Gagal menghapus unit organisasi'
                ];
            }

            return [
                'success' => true,
                'message' => 'Unit organisasi berhasil dihapus'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error deleting unit: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menghapus unit: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check if setting parent would create circular reference
     * 
     * @param int $unitId Unit ID
     * @param int $parentId Proposed parent ID
     * @return bool
     */
    protected function wouldCreateCircularReference(int $unitId, int $parentId): bool
    {
        // Get all descendants of current unit
        $descendants = $this->unitModel->getDescendantIds($unitId);

        // Check if proposed parent is among descendants
        return in_array($parentId, $descendants);
    }

    // =====================================================
    // POSITION MANAGEMENT
    // =====================================================

    /**
     * Get position detail with all relations
     * 
     * @param int $positionId Position ID
     * @return array
     */
    public function getPositionDetail(int $positionId): array
    {
        try {
            $position = $this->positionModel->getFullPosition($positionId);

            if (!$position) {
                return [
                    'success' => false,
                    'message' => 'Posisi tidak ditemukan'
                ];
            }

            return [
                'success' => true,
                'data' => $position,
                'message' => 'Detail posisi berhasil dimuat'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error getting position detail: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal memuat detail posisi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create new position
     * 
     * @param array $data Position data
     * @return array
     */
    public function createPosition(array $data): array
    {
        try {
            // Validate unit exists
            $unit = $this->unitModel->find($data['unit_id']);
            if (!$unit) {
                return [
                    'success' => false,
                    'message' => 'Unit tidak ditemukan'
                ];
            }

            // Auto-generate display order if not provided
            if (!isset($data['display_order'])) {
                $data['display_order'] = $this->positionModel->getNextDisplayOrder($data['unit_id']);
            }

            // Set defaults
            $data['is_active'] = $data['is_active'] ?? 1;
            $data['max_holders'] = $data['max_holders'] ?? 1;

            $positionId = $this->positionModel->insert($data);

            if (!$positionId) {
                return [
                    'success' => false,
                    'errors' => $this->positionModel->errors(),
                    'message' => 'Gagal membuat posisi'
                ];
            }

            return [
                'success' => true,
                'data' => ['id' => $positionId],
                'message' => 'Posisi berhasil dibuat'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error creating position: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal membuat posisi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update position
     * 
     * @param int $positionId Position ID
     * @param array $data Update data
     * @return array
     */
    public function updatePosition(int $positionId, array $data): array
    {
        try {
            $position = $this->positionModel->find($positionId);

            if (!$position) {
                return [
                    'success' => false,
                    'message' => 'Posisi tidak ditemukan'
                ];
            }

            // Prevent circular reporting relationship
            if (isset($data['reports_to']) && $data['reports_to']) {
                if ($this->wouldCreateCircularReporting($positionId, $data['reports_to'])) {
                    return [
                        'success' => false,
                        'message' => 'Tidak dapat mengatur atasan: akan membuat referensi melingkar'
                    ];
                }
            }

            // Check if reducing max_holders would violate current assignments
            if (isset($data['max_holders']) && $data['max_holders'] < $position['max_holders']) {
                $currentHolders = $this->assignmentModel
                    ->where('position_id', $positionId)
                    ->where('status', 'active')
                    ->countAllResults();

                if ($data['max_holders'] < $currentHolders) {
                    return [
                        'success' => false,
                        'message' => "Tidak dapat mengurangi kapasitas. Saat ini ada {$currentHolders} pemegang jabatan aktif."
                    ];
                }
            }

            $updated = $this->positionModel->update($positionId, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'errors' => $this->positionModel->errors(),
                    'message' => 'Gagal mengupdate posisi'
                ];
            }

            return [
                'success' => true,
                'message' => 'Posisi berhasil diupdate'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error updating position: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengupdate posisi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete position
     * 
     * @param int $positionId Position ID
     * @return array
     */
    public function deletePosition(int $positionId): array
    {
        try {
            $position = $this->positionModel->find($positionId);

            if (!$position) {
                return [
                    'success' => false,
                    'message' => 'Posisi tidak ditemukan'
                ];
            }

            // Check if position has active assignments
            if ($this->positionModel->hasActiveAssignments($positionId)) {
                return [
                    'success' => false,
                    'message' => 'Posisi memiliki penugasan aktif. Akhiri penugasan terlebih dahulu.'
                ];
            }

            $deleted = $this->positionModel->delete($positionId);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Gagal menghapus posisi'
                ];
            }

            return [
                'success' => true,
                'message' => 'Posisi berhasil dihapus'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error deleting position: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menghapus posisi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check if setting reports_to would create circular reference
     * 
     * @param int $positionId Position ID
     * @param int $reportsTo Proposed superior position ID
     * @return bool
     */
    protected function wouldCreateCircularReporting(int $positionId, int $reportsTo): bool
    {
        $reportingChain = $this->positionModel->getReportingChain($reportsTo);

        foreach ($reportingChain as $position) {
            if ($position['id'] == $positionId) {
                return true;
            }
        }

        return false;
    }

    // =====================================================
    // ASSIGNMENT MANAGEMENT
    // =====================================================

    /**
     * Assign member to position
     * 
     * @param int $positionId Position ID
     * @param int $userId User ID
     * @param array $assignmentData Additional assignment data
     * @return array
     */
    public function assignMember(int $positionId, int $userId, array $assignmentData = []): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Validate position exists and is active
            $position = $this->positionModel->find($positionId);
            if (!$position) {
                return [
                    'success' => false,
                    'message' => 'Posisi tidak ditemukan'
                ];
            }

            if (!$position['is_active']) {
                return [
                    'success' => false,
                    'message' => 'Posisi tidak aktif'
                ];
            }

            // Validate user exists and is member
            $user = $this->userModel->find($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ];
            }

            $member = $this->memberModel->where('user_id', $userId)->first();
            if (!$member) {
                return [
                    'success' => false,
                    'message' => 'User bukan anggota SPK'
                ];
            }

            // Check if position has vacant slots
            if (!$this->positionModel->isVacant($positionId)) {
                return [
                    'success' => false,
                    'message' => 'Posisi sudah penuh'
                ];
            }

            // Check for duplicate active assignment
            if ($this->assignmentModel->hasActiveAssignment($userId, $positionId)) {
                return [
                    'success' => false,
                    'message' => 'User sudah memiliki penugasan aktif di posisi ini'
                ];
            }

            // Perform assignment
            $assignmentId = $this->assignmentModel->assign($positionId, $userId, $assignmentData);

            if (!$assignmentId) {
                $db->transRollback();
                return [
                    'success' => false,
                    'message' => 'Gagal melakukan penugasan'
                ];
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return [
                    'success' => false,
                    'message' => 'Transaksi gagal'
                ];
            }

            // Send notification (optional)
            $this->sendAssignmentNotification($userId, $positionId);

            return [
                'success' => true,
                'data' => ['assignment_id' => $assignmentId],
                'message' => 'Anggota berhasil ditugaskan ke posisi'
            ];
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error assigning member: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menugaskan anggota: ' . $e->getMessage()
            ];
        }
    }

    /**
     * End assignment
     * 
     * @param int $assignmentId Assignment ID
     * @param string $reason End reason
     * @param string|null $endDate End date
     * @return array
     */
    public function endAssignment(int $assignmentId, string $reason = '', ?string $endDate = null): array
    {
        try {
            $assignment = $this->assignmentModel->find($assignmentId);

            if (!$assignment) {
                return [
                    'success' => false,
                    'message' => 'Penugasan tidak ditemukan'
                ];
            }

            if ($assignment['status'] !== 'active') {
                return [
                    'success' => false,
                    'message' => 'Penugasan tidak aktif'
                ];
            }

            $ended = $this->assignmentModel->endAssignment($assignmentId, $reason, $endDate);

            if (!$ended) {
                return [
                    'success' => false,
                    'message' => 'Gagal mengakhiri penugasan'
                ];
            }

            // Send notification (optional)
            $this->sendEndAssignmentNotification($assignment['user_id'], $assignment['position_id']);

            return [
                'success' => true,
                'message' => 'Penugasan berhasil diakhiri'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error ending assignment: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengakhiri penugasan: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Transfer member to another position
     * 
     * @param int $assignmentId Current assignment ID
     * @param int $newPositionId New position ID
     * @param array $transferData Additional transfer data
     * @return array
     */
    public function transferMember(int $assignmentId, int $newPositionId, array $transferData = []): array
    {
        try {
            $assignment = $this->assignmentModel->find($assignmentId);

            if (!$assignment) {
                return [
                    'success' => false,
                    'message' => 'Penugasan tidak ditemukan'
                ];
            }

            // Validate new position
            $newPosition = $this->positionModel->find($newPositionId);
            if (!$newPosition) {
                return [
                    'success' => false,
                    'message' => 'Posisi tujuan tidak ditemukan'
                ];
            }

            if (!$newPosition['is_active']) {
                return [
                    'success' => false,
                    'message' => 'Posisi tujuan tidak aktif'
                ];
            }

            // Check if new position has vacant slots
            if (!$this->positionModel->isVacant($newPositionId)) {
                return [
                    'success' => false,
                    'message' => 'Posisi tujuan sudah penuh'
                ];
            }

            $newAssignmentId = $this->assignmentModel->transfer($assignmentId, $newPositionId, $transferData);

            if (!$newAssignmentId) {
                return [
                    'success' => false,
                    'message' => 'Gagal melakukan transfer'
                ];
            }

            // Send notification (optional)
            $this->sendTransferNotification($assignment['user_id'], $assignment['position_id'], $newPositionId);

            return [
                'success' => true,
                'data' => ['new_assignment_id' => $newAssignmentId],
                'message' => 'Transfer berhasil dilakukan'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error transferring member: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal melakukan transfer: ' . $e->getMessage()
            ];
        }
    }

    // =====================================================
    // STATISTICS & REPORTING
    // =====================================================

    /**
     * Get organizational statistics
     * 
     * @param array $filters Optional filters
     * @return array
     */
    public function getStatistics(array $filters = []): array
    {
        try {
            $stats = [
                'units' => [
                    'total' => $this->unitModel->getTotalUnits(['is_active' => 1]),
                    'by_scope' => $this->unitModel->getByScope(),
                    'by_level' => $this->unitModel->getByLevel()
                ],
                'positions' => [
                    'total' => $this->positionModel->getTotalPositions(['is_active' => 1]),
                    'by_type' => $this->positionModel->getByType(),
                    'by_level' => $this->positionModel->getByLevel(),
                    'vacancy' => $this->positionModel->getVacancyStats()
                ],
                'assignments' => [
                    'total' => $this->assignmentModel->getTotalAssignments(),
                    'by_status' => $this->assignmentModel->getByStatus(),
                    'by_type' => $this->assignmentModel->getByType(),
                    'turnover' => $this->assignmentModel->getTurnoverRate()
                ]
            ];

            return [
                'success' => true,
                'data' => $stats,
                'message' => 'Statistik berhasil dimuat'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error getting statistics: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal memuat statistik: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get member assignment history
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getMemberHistory(int $userId): array
    {
        try {
            $history = $this->assignmentModel->getUserHistory($userId, true);

            return [
                'success' => true,
                'data' => $history,
                'message' => 'Riwayat penugasan berhasil dimuat'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error getting member history: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal memuat riwayat: ' . $e->getMessage()
            ];
        }
    }

    // =====================================================
    // NOTIFICATION HELPERS (Optional)
    // =====================================================

    /**
     * Send assignment notification to user
     * 
     * @param int $userId User ID
     * @param int $positionId Position ID
     * @return void
     */
    protected function sendAssignmentNotification(int $userId, int $positionId): void
    {
        // TODO: Implement notification logic
        // Can integrate with NotificationService
    }

    /**
     * Send end assignment notification
     * 
     * @param int $userId User ID
     * @param int $positionId Position ID
     * @return void
     */
    protected function sendEndAssignmentNotification(int $userId, int $positionId): void
    {
        // TODO: Implement notification logic
    }

    /**
     * Send transfer notification
     * 
     * @param int $userId User ID
     * @param int $oldPositionId Old position ID
     * @param int $newPositionId New position ID
     * @return void
     */
    protected function sendTransferNotification(int $userId, int $oldPositionId, int $newPositionId): void
    {
        // TODO: Implement notification logic
    }
}
