<?php

namespace App\Services\Member;

use App\Models\UserModel;
use App\Models\MemberProfileModel;
use App\Models\AuditLogModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * ApproveMemberService
 * 
 * Menangani proses approval/verifikasi anggota baru oleh pengurus
 * Termasuk approval, rejection, role assignment, dan notification
 * 
 * @package App\Services\Member
 * @author  SPK Development Team
 * @version 1.0.0
 */
class ApproveMemberService
{
    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * @var MemberProfileModel
     */
    protected $memberModel;

    /**
     * @var AuditLogModel
     */
    protected $auditModel;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->memberModel = new MemberProfileModel();
        $this->auditModel = new AuditLogModel();
    }

    /**
     * Approve pending member
     * Changes role from "Calon Anggota" to "Anggota" and activates account
     * 
     * @param int $userId User ID to approve
     * @param int $approvedBy User ID of approver (pengurus)
     * @param array $options Additional options (notes, send_email, etc)
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function approve(int $userId, int $approvedBy, array $options = []): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 1. Get user and member profile
            $user = $this->userModel->withProfile()->find($userId);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                    'data' => null
                ];
            }

            // 2. Check if user is pending approval
            if (!$user->inGroup('calon_anggota')) {
                return [
                    'success' => false,
                    'message' => 'User bukan calon anggota atau sudah disetujui',
                    'data' => null
                ];
            }

            // 3. Get member profile
            $member = $this->memberModel->where('user_id', $userId)->first();

            if (!$member) {
                return [
                    'success' => false,
                    'message' => 'Profil anggota tidak ditemukan',
                    'data' => null
                ];
            }

            // 4. Update member profile status
            $memberUpdateData = [
                'membership_status' => 'active',
                'verified_at' => date('Y-m-d H:i:s'),
                'verified_by' => $approvedBy,
            ];

            if (isset($options['notes'])) {
                $memberUpdateData['notes'] = $options['notes'];
            }

            $this->memberModel->update($member->id, $memberUpdateData);

            // 5. Activate user account
            $this->userModel->update($userId, ['active' => 1]);

            // 6. Change role from "calon_anggota" to "anggota"
            $roleChanged = $this->changeRole($userId, 'calon_anggota', 'anggota');

            if (!$roleChanged['success']) {
                throw new \Exception($roleChanged['message']);
            }

            // 7. Log audit trail
            $this->logApproval($userId, $approvedBy, 'approved', $options['notes'] ?? null);

            // 8. Send approval notification email
            if (!isset($options['send_email']) || $options['send_email'] === true) {
                $emailResult = $this->sendApprovalNotification($user, 'approved');

                if (!$emailResult['success']) {
                    log_message('warning', 'Failed to send approval email to user ' . $userId);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new DatabaseException('Transaction failed');
            }

            return [
                'success' => true,
                'message' => 'Anggota berhasil disetujui',
                'data' => [
                    'user_id' => $userId,
                    'member_id' => $member->id,
                    'member_number' => $member->member_number,
                    'new_role' => 'anggota',
                    'approved_by' => $approvedBy,
                    'approved_at' => date('Y-m-d H:i:s')
                ]
            ];
        } catch (\Exception $e) {
            $db->transRollback();

            log_message('error', 'Error in ApproveMemberService::approve: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menyetujui anggota: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Reject pending member
     * Sends rejection notification and optionally deletes the account
     * 
     * @param int $userId User ID to reject
     * @param int $rejectedBy User ID of rejector (pengurus)
     * @param string $reason Rejection reason
     * @param bool $deleteAccount Whether to delete the account (default: false)
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function reject(int $userId, int $rejectedBy, string $reason, bool $deleteAccount = false): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 1. Get user and member profile
            $user = $this->userModel->withProfile()->find($userId);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                    'data' => null
                ];
            }

            // 2. Check if user is pending approval
            if (!$user->inGroup('calon_anggota')) {
                return [
                    'success' => false,
                    'message' => 'User bukan calon anggota',
                    'data' => null
                ];
            }

            // 3. Get member profile
            $member = $this->memberModel->where('user_id', $userId)->first();

            // 4. Update member profile status to rejected
            if ($member && !$deleteAccount) {
                $this->memberModel->update($member->id, [
                    'membership_status' => 'rejected',
                    'verified_at' => date('Y-m-d H:i:s'),
                    'verified_by' => $rejectedBy,
                    'notes' => $reason
                ]);
            }

            // 5. Log audit trail
            $this->logApproval($userId, $rejectedBy, 'rejected', $reason);

            // 6. Send rejection notification email
            $emailResult = $this->sendApprovalNotification($user, 'rejected', $reason);

            if (!$emailResult['success']) {
                log_message('warning', 'Failed to send rejection email to user ' . $userId);
            }

            // 7. Delete account if requested
            if ($deleteAccount) {
                // Delete member profile first (foreign key constraint)
                if ($member) {
                    $this->memberModel->delete($member->id);
                }

                // Delete user account
                $this->userModel->delete($userId, true); // Hard delete
            } else {
                // Just deactivate the account
                $this->userModel->update($userId, ['active' => 0]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new DatabaseException('Transaction failed');
            }

            return [
                'success' => true,
                'message' => $deleteAccount ? 'Anggota ditolak dan akun dihapus' : 'Anggota ditolak',
                'data' => [
                    'user_id' => $userId,
                    'rejected_by' => $rejectedBy,
                    'rejected_at' => date('Y-m-d H:i:s'),
                    'reason' => $reason,
                    'account_deleted' => $deleteAccount
                ]
            ];
        } catch (\Exception $e) {
            $db->transRollback();

            log_message('error', 'Error in ApproveMemberService::reject: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menolak anggota: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Bulk approve multiple pending members
     * 
     * @param array $userIds Array of user IDs to approve
     * @param int $approvedBy User ID of approver
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function bulkApprove(array $userIds, int $approvedBy): array
    {
        $results = [
            'total' => count($userIds),
            'success' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($userIds as $userId) {
            $result = $this->approve($userId, $approvedBy, ['send_email' => true]);

            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
            }

            $results['details'][$userId] = $result;
        }

        return [
            'success' => $results['failed'] === 0,
            'message' => sprintf(
                'Berhasil: %d, Gagal: %d dari %d anggota',
                $results['success'],
                $results['failed'],
                $results['total']
            ),
            'data' => $results
        ];
    }

    /**
     * Change user role
     * 
     * @param int $userId User ID
     * @param string $fromRole Current role name
     * @param string $toRole New role name
     * @return array ['success' => bool, 'message' => string]
     */
    public function changeRole(int $userId, string $fromRole, string $toRole): array
    {
        try {
            $user = $this->userModel->find($userId);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ];
            }

            // Remove old role
            if ($user->inGroup($fromRole)) {
                $user->removeGroup($fromRole);
            }

            // Add new role
            $user->addGroup($toRole);

            return [
                'success' => true,
                'message' => "Role berhasil diubah dari {$fromRole} ke {$toRole}"
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error changing role: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengubah role: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send approval/rejection notification email
     * 
     * @param object $user User entity
     * @param string $status 'approved' or 'rejected'
     * @param string|null $reason Rejection reason (for rejected status)
     * @return array ['success' => bool, 'message' => string]
     */
    protected function sendApprovalNotification($user, string $status, ?string $reason = null): array
    {
        try {
            $email = \Config\Services::email();

            $email->setFrom('noreply@spk.or.id', 'Serikat Pekerja Kampus');
            $email->setTo($user->email);

            if ($status === 'approved') {
                $email->setSubject('Selamat! Keanggotaan Anda Disetujui');

                $message = view('emails/member_approved', [
                    'username' => $user->username,
                    'full_name' => $user->full_name ?? $user->username,
                    'member_number' => $user->member_number ?? 'N/A',
                    'login_url' => base_url('login')
                ]);
            } else {
                $email->setSubject('Pemberitahuan Keanggotaan');

                $message = view('emails/member_rejected', [
                    'username' => $user->username,
                    'full_name' => $user->full_name ?? $user->username,
                    'reason' => $reason ?? 'Tidak memenuhi syarat keanggotaan',
                    'contact_url' => base_url('kontak')
                ]);
            }

            $email->setMessage($message);

            if ($email->send()) {
                return [
                    'success' => true,
                    'message' => 'Email notifikasi berhasil dikirim'
                ];
            }

            return [
                'success' => false,
                'message' => 'Gagal mengirim email notifikasi'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error sending approval notification: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error mengirim email: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Log approval/rejection to audit trail
     * 
     * @param int $userId User ID being approved/rejected
     * @param int $actionBy User ID performing the action
     * @param string $action 'approved' or 'rejected'
     * @param string|null $notes Additional notes
     * @return void
     */
    protected function logApproval(int $userId, int $actionBy, string $action, ?string $notes = null): void
    {
        try {
            $this->auditModel->insert([
                'user_id' => $actionBy,
                'action' => 'member.' . $action,
                'table_name' => 'member_profiles',
                'record_id' => $userId,
                'old_values' => json_encode(['status' => 'pending']),
                'new_values' => json_encode(['status' => $action]),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'CLI',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'CLI',
                'description' => $notes ?? "Member {$action} by user {$actionBy}",
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error logging approval audit: ' . $e->getMessage());
        }
    }

    /**
     * Get pending members count
     * 
     * @return int
     */
    public function getPendingCount(): int
    {
        return $this->memberModel
            ->where('membership_status', 'pending')
            ->countAllResults();
    }

    /**
     * Get pending members list
     * 
     * @param int $limit Number of records to return
     * @param int $offset Offset for pagination
     * @return array
     */
    public function getPendingMembers(int $limit = 10, int $offset = 0): array
    {
        return $this->memberModel
            ->select('member_profiles.*, users.username, users.email, users.created_at as registration_date')
            ->join('users', 'users.id = member_profiles.user_id')
            ->where('member_profiles.membership_status', 'pending')
            ->orderBy('users.created_at', 'DESC')
            ->limit($limit, $offset)
            ->findAll();
    }

    /**
     * Get approval statistics
     * 
     * @return array
     */
    public function getApprovalStats(): array
    {
        $today = date('Y-m-d');
        $thisMonth = date('Y-m');
        $thisYear = date('Y');

        return [
            'pending' => $this->memberModel
                ->where('membership_status', 'pending')
                ->countAllResults(),
            'approved_today' => $this->memberModel
                ->where('membership_status', 'active')
                ->where('DATE(verified_at)', $today)
                ->countAllResults(),
            'approved_this_month' => $this->memberModel
                ->where('membership_status', 'active')
                ->like('verified_at', $thisMonth, 'after')
                ->countAllResults(),
            'approved_this_year' => $this->memberModel
                ->where('membership_status', 'active')
                ->like('verified_at', $thisYear, 'after')
                ->countAllResults(),
            'rejected' => $this->memberModel
                ->where('membership_status', 'rejected')
                ->countAllResults(),
        ];
    }

    /**
     * Check if user can approve members
     * 
     * @param int $userId User ID to check
     * @return bool
     */
    public function canApproveMembers(int $userId): bool
    {
        $user = $this->userModel->find($userId);

        if (!$user) {
            return false;
        }

        // Only superadmin and pengurus can approve members
        return $user->inGroup('superadmin') || $user->inGroup('pengurus');
    }

    /**
     * Get member approval history
     * 
     * @param int $memberId Member profile ID
     * @return array
     */
    public function getApprovalHistory(int $memberId): array
    {
        try {
            $member = $this->memberModel->find($memberId);

            if (!$member) {
                return [];
            }

            // Get audit logs related to this member
            $logs = $this->auditModel
                ->where('table_name', 'member_profiles')
                ->where('record_id', $member->user_id)
                ->whereIn('action', ['member.approved', 'member.rejected'])
                ->orderBy('created_at', 'DESC')
                ->findAll();

            return $logs;
        } catch (\Exception $e) {
            log_message('error', 'Error getting approval history: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Reactivate rejected member
     * Allows rejected member to apply again
     * 
     * @param int $userId User ID to reactivate
     * @param int $reactivatedBy User ID performing reactivation
     * @return array ['success' => bool, 'message' => string]
     */
    public function reactivate(int $userId, int $reactivatedBy): array
    {
        try {
            $member = $this->memberModel->where('user_id', $userId)->first();

            if (!$member) {
                return [
                    'success' => false,
                    'message' => 'Profil anggota tidak ditemukan'
                ];
            }

            if ($member->membership_status !== 'rejected') {
                return [
                    'success' => false,
                    'message' => 'Hanya anggota yang ditolak yang dapat direaktivasi'
                ];
            }

            // Update member status back to pending
            $this->memberModel->update($member->id, [
                'membership_status' => 'pending',
                'verified_at' => null,
                'verified_by' => null,
                'notes' => 'Direaktivasi untuk dipertimbangkan kembali'
            ]);

            // Activate user account
            $this->userModel->update($userId, ['active' => 1]);

            // Log audit
            $this->logApproval($userId, $reactivatedBy, 'reactivated', 'Member reactivated for reconsideration');

            return [
                'success' => true,
                'message' => 'Anggota berhasil direaktivasi dan dikembalikan ke status pending'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error reactivating member: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mereaktivasi anggota: ' . $e->getMessage()
            ];
        }
    }
}
