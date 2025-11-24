<?php

namespace App\Services;

use App\Models\UserModel;
use App\Models\MemberProfileModel;
use App\Models\ProvinceModel;
use App\Models\RoleModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * RegionScopeService
 * 
 * Menangani regional scope untuk Koordinator Wilayah
 * Mengelola akses regional, validasi member access, dan scope management
 * 
 * @package App\Services
 * @author  SPK Development Team
 * @version 1.0.0
 */
class RegionScopeService
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
     * @var ProvinceModel
     */
    protected $provinceModel;

    /**
     * @var RoleModel
     */
    protected $roleModel;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->memberModel = new MemberProfileModel();
        $this->provinceModel = new ProvinceModel();
        $this->roleModel = new RoleModel();
    }

    /**
     * Get scope data for koordinator
     * Returns complete scope information including assigned provinces and member counts
     * 
     * @param int $userId User ID of koordinator
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function getScopeData(int $userId): array
    {
        try {
            $user = $this->userModel->find($userId);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                    'data' => null
                ];
            }

            // Check if user is Koordinator Wilayah
            if (!$user->inGroup('Koordinator Wilayah')) {
                return [
                    'success' => false,
                    'message' => 'User bukan Koordinator Wilayah',
                    'data' => null
                ];
            }

            // Get member profile to find assigned province
            $member = $this->memberModel->where('user_id', $userId)->first();

            if (!$member || !$member->province_id) {
                return [
                    'success' => false,
                    'message' => 'Provinsi koordinator belum di-set',
                    'data' => null
                ];
            }

            // Get province details
            $province = $this->provinceModel->find($member->province_id);

            // Count members in this province
            $memberCount = $this->memberModel
                ->where('province_id', $member->province_id)
                ->countAllResults();

            // Count active members
            $activeMemberCount = $this->memberModel
                ->join('users', 'users.id = member_profiles.user_id')
                ->where('member_profiles.province_id', $member->province_id)
                ->where('users.active', 1)
                ->countAllResults();

            return [
                'success' => true,
                'message' => 'Scope data berhasil diambil',
                'data' => [
                    'user_id' => $userId,
                    'province_id' => $member->province_id,
                    'province_name' => $province->name ?? '',
                    'total_members' => $memberCount,
                    'active_members' => $activeMemberCount,
                    'pending_members' => $memberCount - $activeMemberCount
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in RegionScopeService::getScopeData: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil data scope: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Check if koordinator can access specific member
     * Validates that member is within koordinator's province scope
     * 
     * @param int $koordinatorId User ID of koordinator
     * @param int $memberId Member profile ID to check
     * @return bool True if koordinator has access, false otherwise
     */
    public function canAccessMember(int $koordinatorId, int $memberId): bool
    {
        try {
            // Get koordinator's member profile
            $koordinator = $this->memberModel->where('user_id', $koordinatorId)->first();

            if (!$koordinator || !$koordinator->province_id) {
                return false;
            }

            // Get target member
            $member = $this->memberModel->find($memberId);

            if (!$member) {
                return false;
            }

            // Check if member is in same province
            return $koordinator->province_id === $member->province_id;
        } catch (\Exception $e) {
            log_message('error', 'Error in RegionScopeService::canAccessMember: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get members by region with filters
     * Returns filtered list of members in specific province
     * 
     * @param int $provinceId Province ID
     * @param array $filters Additional filters (status, university_id, etc)
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function getMembersByRegion(int $provinceId, array $filters = []): array
    {
        try {
            $builder = $this->memberModel
                ->select('member_profiles.*, users.email, users.active, users.created_at as user_created_at')
                ->join('users', 'users.id = member_profiles.user_id')
                ->where('member_profiles.province_id', $provinceId);

            // Apply additional filters
            if (isset($filters['status'])) {
                $builder->where('users.active', $filters['status'] === 'active' ? 1 : 0);
            }

            if (isset($filters['university_id'])) {
                $builder->where('member_profiles.university_id', $filters['university_id']);
            }

            if (isset($filters['membership_status'])) {
                $builder->where('member_profiles.membership_status', $filters['membership_status']);
            }

            if (isset($filters['search'])) {
                $search = $filters['search'];
                $builder->groupStart()
                    ->like('member_profiles.full_name', $search)
                    ->orLike('users.email', $search)
                    ->orLike('member_profiles.member_number', $search)
                    ->groupEnd();
            }

            // Pagination
            $page = $filters['page'] ?? 1;
            $perPage = $filters['per_page'] ?? 20;

            $members = $builder->paginate($perPage, 'default', $page);
            $pager = $this->memberModel->pager;

            return [
                'success' => true,
                'message' => 'Data anggota berhasil diambil',
                'data' => [
                    'members' => $members,
                    'pagination' => [
                        'current_page' => $pager->getCurrentPage(),
                        'total_pages' => $pager->getPageCount(),
                        'per_page' => $perPage,
                        'total_records' => $pager->getTotal()
                    ]
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in RegionScopeService::getMembersByRegion: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil data anggota: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get all members under koordinator scope
     * Returns all members in koordinator's assigned province
     * 
     * @param int $userId User ID of koordinator
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function getRegionMembers(int $userId): array
    {
        try {
            // Get koordinator's province
            $scopeData = $this->getScopeData($userId);

            if (!$scopeData['success']) {
                return $scopeData;
            }

            $provinceId = $scopeData['data']['province_id'];

            // Get members in that province
            return $this->getMembersByRegion($provinceId);
        } catch (\Exception $e) {
            log_message('error', 'Error in RegionScopeService::getRegionMembers: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil anggota wilayah: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Check if user has access to specific province
     * Validates province access for koordinator
     * 
     * @param int $userId User ID to check
     * @param int $provinceId Province ID to validate
     * @return bool True if user has access, false otherwise
     */
    public function hasAccessToProvince(int $userId, int $provinceId): bool
    {
        try {
            $user = $this->userModel->find($userId);

            if (!$user) {
                return false;
            }

            // Super Admin and Pengurus can access all provinces
            if ($user->inGroup('Super Admin') || $user->inGroup('Pengurus')) {
                return true;
            }

            // Koordinator Wilayah can only access their assigned province
            if ($user->inGroup('Koordinator Wilayah')) {
                $member = $this->memberModel->where('user_id', $userId)->first();
                return $member && $member->province_id === $provinceId;
            }

            return false;
        } catch (\Exception $e) {
            log_message('error', 'Error in RegionScopeService::hasAccessToProvince: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Assign province scope to koordinator
     * Sets koordinator's province assignment in member profile
     * 
     * @param int $userId User ID of koordinator
     * @param int $provinceId Province ID to assign
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function assignScope(int $userId, int $provinceId): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Validate user exists
            $user = $this->userModel->find($userId);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                    'data' => null
                ];
            }

            // Check if user is Koordinator Wilayah
            if (!$user->inGroup('Koordinator Wilayah')) {
                return [
                    'success' => false,
                    'message' => 'User bukan Koordinator Wilayah',
                    'data' => null
                ];
            }

            // Validate province exists
            $province = $this->provinceModel->find($provinceId);

            if (!$province) {
                return [
                    'success' => false,
                    'message' => 'Provinsi tidak ditemukan',
                    'data' => null
                ];
            }

            // Get member profile
            $member = $this->memberModel->where('user_id', $userId)->first();

            if (!$member) {
                return [
                    'success' => false,
                    'message' => 'Profil anggota tidak ditemukan',
                    'data' => null
                ];
            }

            // Update province assignment
            $this->memberModel->update($member->id, [
                'province_id' => $provinceId,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new DatabaseException('Transaction failed');
            }

            return [
                'success' => true,
                'message' => "Scope wilayah berhasil di-assign ke {$province->name}",
                'data' => [
                    'user_id' => $userId,
                    'province_id' => $provinceId,
                    'province_name' => $province->name
                ]
            ];
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error in RegionScopeService::assignScope: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal assign scope: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Remove province scope from koordinator
     * Clears koordinator's province assignment
     * 
     * @param int $userId User ID of koordinator
     * @param int $provinceId Province ID to remove
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function removeScope(int $userId, int $provinceId): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Get member profile
            $member = $this->memberModel->where('user_id', $userId)->first();

            if (!$member) {
                return [
                    'success' => false,
                    'message' => 'Profil anggota tidak ditemukan',
                    'data' => null
                ];
            }

            // Verify current province matches
            if ($member->province_id !== $provinceId) {
                return [
                    'success' => false,
                    'message' => 'Provinsi tidak sesuai dengan yang ter-assign',
                    'data' => null
                ];
            }

            // Remove province assignment (set to null)
            $this->memberModel->update($member->id, [
                'province_id' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new DatabaseException('Transaction failed');
            }

            return [
                'success' => true,
                'message' => 'Scope wilayah berhasil dihapus',
                'data' => [
                    'user_id' => $userId,
                    'removed_province_id' => $provinceId
                ]
            ];
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error in RegionScopeService::removeScope: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menghapus scope: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get assigned provinces for koordinator
     * Returns list of provinces assigned to koordinator
     * 
     * @param int $userId User ID of koordinator
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function getKoordinatorProvinces(int $userId): array
    {
        try {
            $user = $this->userModel->find($userId);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                    'data' => null
                ];
            }

            // Get member profile
            $member = $this->memberModel->where('user_id', $userId)->first();

            if (!$member) {
                return [
                    'success' => false,
                    'message' => 'Profil anggota tidak ditemukan',
                    'data' => null
                ];
            }

            $provinces = [];

            // Get assigned province if exists
            if ($member->province_id) {
                $province = $this->provinceModel->find($member->province_id);

                if ($province) {
                    $provinces[] = [
                        'id' => $province->id,
                        'name' => $province->name,
                        'code' => $province->code ?? null,
                        'member_count' => $this->memberModel
                            ->where('province_id', $province->id)
                            ->countAllResults()
                    ];
                }
            }

            return [
                'success' => true,
                'message' => 'Data provinsi berhasil diambil',
                'data' => [
                    'user_id' => $userId,
                    'provinces' => $provinces,
                    'total' => count($provinces)
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in RegionScopeService::getKoordinatorProvinces: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil data provinsi: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Validate scope access for user
     * Comprehensive validation for scope-based operations
     *
     * @param int $userId User ID to validate
     * @param array $data Data containing target province_id or member_id
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function validateScope(int $userId, array $data): array
    {
        try {
            $user = $this->userModel->find($userId);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                    'data' => null
                ];
            }

            // Super Admin and Pengurus bypass scope validation
            if ($user->inGroup('Super Admin') || $user->inGroup('Pengurus')) {
                return [
                    'success' => true,
                    'message' => 'Akses granted (Super Admin/Pengurus)',
                    'data' => ['bypass' => true]
                ];
            }

            // Validate for Koordinator Wilayah
            if ($user->inGroup('Koordinator Wilayah')) {
                // Check province_id access
                if (isset($data['province_id'])) {
                    $hasAccess = $this->hasAccessToProvince($userId, $data['province_id']);

                    return [
                        'success' => $hasAccess,
                        'message' => $hasAccess ? 'Akses granted' : 'Tidak memiliki akses ke provinsi ini',
                        'data' => ['has_access' => $hasAccess]
                    ];
                }

                // Check member_id access
                if (isset($data['member_id'])) {
                    $hasAccess = $this->canAccessMember($userId, $data['member_id']);

                    return [
                        'success' => $hasAccess,
                        'message' => $hasAccess ? 'Akses granted' : 'Tidak memiliki akses ke anggota ini',
                        'data' => ['has_access' => $hasAccess]
                    ];
                }
            }

            // Default: no access
            return [
                'success' => false,
                'message' => 'Tidak memiliki scope akses',
                'data' => null
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in RegionScopeService::validateScope: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal validasi scope: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Apply regional scope to payment queries
     * Restricts query to payments from members in koordinator's province
     *
     * @param object $builder Query builder instance
     * @param int $koordinatorId Koordinator user ID
     * @return object Modified query builder
     */
    public function applyScopeToPayments($builder, int $koordinatorId)
    {
        try {
            // Get koordinator's member profile
            $koordinator = $this->memberModel->where('user_id', $koordinatorId)->first();

            if (!$koordinator || !$koordinator->province_id) {
                // If no province assigned, return empty result
                $builder->where('1', '0'); // Always false condition
                return $builder;
            }

            // Join with member_profiles if not already joined
            $builder->join('member_profiles', 'member_profiles.user_id = payments.user_id', 'left');

            // Filter by province
            $builder->where('member_profiles.province_id', $koordinator->province_id);

            return $builder;
        } catch (\Exception $e) {
            log_message('error', 'Error in RegionScopeService::applyScopeToPayments: ' . $e->getMessage());

            // Return builder with no results on error
            $builder->where('1', '0');
            return $builder;
        }
    }

    /**
     * Check if koordinator can access specific payment
     * Validates that payment belongs to member in koordinator's province
     *
     * @param int $koordinatorId User ID of koordinator
     * @param int $paymentId Payment ID to check
     * @return bool True if koordinator has access, false otherwise
     */
    public function canAccessPayment(int $koordinatorId, int $paymentId): bool
    {
        try {
            // Get koordinator's member profile
            $koordinator = $this->memberModel->where('user_id', $koordinatorId)->first();

            if (!$koordinator || !$koordinator->province_id) {
                return false;
            }

            // Get payment with member profile
            $db = \Config\Database::connect();
            $payment = $db->table('payments')
                ->select('member_profiles.province_id')
                ->join('member_profiles', 'member_profiles.user_id = payments.user_id', 'left')
                ->where('payments.id', $paymentId)
                ->get()
                ->getRow();

            if (!$payment) {
                return false;
            }

            // Check if payment is in same province
            return $koordinator->province_id === $payment->province_id;
        } catch (\Exception $e) {
            log_message('error', 'Error in RegionScopeService::canAccessPayment: ' . $e->getMessage());
            return false;
        }
    }
}
