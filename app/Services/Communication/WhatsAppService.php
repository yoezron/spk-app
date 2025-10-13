<?php

namespace App\Services\Communication;

use App\Models\WAGroupModel;
use App\Models\ProvinceModel;
use App\Models\MemberProfileModel;
use App\Services\RegionScopeService;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * WhatsAppService
 * 
 * Menangani WhatsApp group link management per provinsi
 * Termasuk CRUD group links, validation, dan statistics
 * 
 * @package App\Services\Communication
 * @author  SPK Development Team
 * @version 1.0.0
 */
class WhatsAppService
{
    /**
     * @var WAGroupModel
     */
    protected $waGroupModel;

    /**
     * @var ProvinceModel
     */
    protected $provinceModel;

    /**
     * @var MemberProfileModel
     */
    protected $memberModel;

    /**
     * @var RegionScopeService
     */
    protected $regionScopeService;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->waGroupModel = new WAGroupModel();
        $this->provinceModel = new ProvinceModel();
        $this->memberModel = new MemberProfileModel();
        $this->regionScopeService = new RegionScopeService();
    }

    /**
     * Update WhatsApp group link for province
     * Creates or updates group link for specific province
     * 
     * @param int $provinceId Province ID
     * @param string $groupLink WhatsApp group link URL
     * @param array $additionalData Additional data (group_name, description, etc)
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function updateGroupLink(int $provinceId, string $groupLink, array $additionalData = []): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Validate province exists
            $province = $this->provinceModel->find($provinceId);

            if (!$province) {
                return [
                    'success' => false,
                    'message' => 'Provinsi tidak ditemukan',
                    'data' => null
                ];
            }

            // Validate WhatsApp link format
            if (!$this->validateGroupLink($groupLink)) {
                return [
                    'success' => false,
                    'message' => 'Format link WhatsApp tidak valid',
                    'data' => null
                ];
            }

            // Check if group link already exists for this province
            $existingGroup = $this->waGroupModel->where('province_id', $provinceId)->first();

            $groupData = [
                'province_id' => $provinceId,
                'group_link' => $groupLink,
                'group_name' => $additionalData['group_name'] ?? "WA Group SPK {$province->name}",
                'description' => $additionalData['description'] ?? null,
                'is_active' => $additionalData['is_active'] ?? 1,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($existingGroup) {
                // Update existing
                $this->waGroupModel->update($existingGroup->id, $groupData);
                $groupId = $existingGroup->id;
                $action = 'diperbarui';
            } else {
                // Create new
                $groupData['created_at'] = date('Y-m-d H:i:s');
                $groupId = $this->waGroupModel->insert($groupData);
                $action = 'ditambahkan';
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new DatabaseException('Transaction failed');
            }

            return [
                'success' => true,
                'message' => "Link WhatsApp group berhasil {$action}",
                'data' => [
                    'group_id' => $groupId,
                    'province_id' => $provinceId,
                    'province_name' => $province->name,
                    'group_link' => $groupLink,
                    'group_name' => $groupData['group_name']
                ]
            ];
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error in WhatsAppService::updateGroupLink: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal update link WhatsApp: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get WhatsApp group link by province
     * Returns group link for specific province
     * 
     * @param int $provinceId Province ID
     * @return string|null Group link or null if not found
     */
    public function getGroupLink(int $provinceId): ?string
    {
        try {
            $group = $this->waGroupModel
                ->where('province_id', $provinceId)
                ->where('is_active', 1)
                ->first();

            return $group ? $group->group_link : null;
        } catch (\Exception $e) {
            log_message('error', 'Error in WhatsAppService::getGroupLink: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all WhatsApp group links
     * Returns list of all active group links with province info
     * 
     * @param array $filters Optional filters (is_active, search, etc)
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function getAllGroupLinks(array $filters = []): array
    {
        try {
            $builder = $this->waGroupModel
                ->select('wa_groups.*, provinces.name as province_name, provinces.code as province_code')
                ->join('provinces', 'provinces.id = wa_groups.province_id', 'left');

            // Apply filters
            if (isset($filters['is_active'])) {
                $builder->where('wa_groups.is_active', $filters['is_active']);
            }

            if (isset($filters['search'])) {
                $search = $filters['search'];
                $builder->groupStart()
                    ->like('wa_groups.group_name', $search)
                    ->orLike('provinces.name', $search)
                    ->groupEnd();
            }

            $groups = $builder->orderBy('provinces.name', 'ASC')->findAll();

            // Add member count for each group
            foreach ($groups as &$group) {
                $group->member_count = $this->memberModel
                    ->where('province_id', $group->province_id)
                    ->countAllResults();
            }

            return [
                'success' => true,
                'message' => 'Data WhatsApp groups berhasil diambil',
                'data' => [
                    'groups' => $groups,
                    'total' => count($groups)
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in WhatsAppService::getAllGroupLinks: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil data groups: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Validate WhatsApp group link format
     * Checks if link is valid WhatsApp group URL
     * 
     * @param string $link WhatsApp group link
     * @return bool True if valid, false otherwise
     */
    public function validateGroupLink(string $link): bool
    {
        // WhatsApp group link formats:
        // https://chat.whatsapp.com/xxxxx
        // https://wa.me/xxxxx
        // http://chat.whatsapp.com/xxxxx

        $patterns = [
            '/^https?:\/\/(chat\.)?whatsapp\.com\/.+$/i',
            '/^https?:\/\/wa\.me\/.+$/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $link)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Delete WhatsApp group link
     * Removes group link for specific province
     * 
     * @param int $provinceId Province ID
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function deleteGroupLink(int $provinceId): array
    {
        try {
            $group = $this->waGroupModel->where('province_id', $provinceId)->first();

            if (!$group) {
                return [
                    'success' => false,
                    'message' => 'Link WhatsApp group tidak ditemukan',
                    'data' => null
                ];
            }

            // Soft delete by setting is_active to 0
            $this->waGroupModel->update($group->id, [
                'is_active' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Or hard delete (uncomment if preferred)
            // $this->waGroupModel->delete($group->id);

            return [
                'success' => true,
                'message' => 'Link WhatsApp group berhasil dihapus',
                'data' => [
                    'group_id' => $group->id,
                    'province_id' => $provinceId
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in WhatsAppService::deleteGroupLink: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menghapus link: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get WhatsApp groups under koordinator scope
     * Returns groups that koordinator has access to
     * 
     * @param int $userId User ID of koordinator
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function getGroupsByKoordinator(int $userId): array
    {
        try {
            // Get koordinator's scope
            $scopeData = $this->regionScopeService->getScopeData($userId);

            if (!$scopeData['success']) {
                return $scopeData;
            }

            $provinceId = $scopeData['data']['province_id'];

            // Get group for this province
            $group = $this->waGroupModel
                ->select('wa_groups.*, provinces.name as province_name')
                ->join('provinces', 'provinces.id = wa_groups.province_id', 'left')
                ->where('wa_groups.province_id', $provinceId)
                ->where('wa_groups.is_active', 1)
                ->first();

            if (!$group) {
                return [
                    'success' => true,
                    'message' => 'Belum ada WhatsApp group untuk wilayah ini',
                    'data' => [
                        'groups' => [],
                        'total' => 0
                    ]
                ];
            }

            // Add member count
            $group->member_count = $this->memberModel
                ->where('province_id', $provinceId)
                ->countAllResults();

            return [
                'success' => true,
                'message' => 'Data WhatsApp groups berhasil diambil',
                'data' => [
                    'groups' => [$group],
                    'total' => 1,
                    'province_id' => $provinceId,
                    'province_name' => $group->province_name
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in WhatsAppService::getGroupsByKoordinator: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil data groups: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Send broadcast info to WhatsApp group
     * Future integration with WhatsApp Business API
     * Currently returns placeholder for future implementation
     * 
     * @param int $provinceId Province ID
     * @param string $message Message to broadcast
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function sendBroadcastInfo(int $provinceId, string $message): array
    {
        try {
            // Validate group exists
            $group = $this->waGroupModel
                ->where('province_id', $provinceId)
                ->where('is_active', 1)
                ->first();

            if (!$group) {
                return [
                    'success' => false,
                    'message' => 'WhatsApp group tidak ditemukan untuk provinsi ini',
                    'data' => null
                ];
            }

            // TODO: Future implementation with WhatsApp Business API
            // For now, log the broadcast attempt
            log_message('info', sprintf(
                'Broadcast info to province %d: %s',
                $provinceId,
                substr($message, 0, 100)
            ));

            return [
                'success' => true,
                'message' => 'Fitur broadcast akan segera tersedia. Silakan gunakan link group untuk mengirim pesan manual.',
                'data' => [
                    'province_id' => $provinceId,
                    'group_link' => $group->group_link,
                    'group_name' => $group->group_name,
                    'message_preview' => substr($message, 0, 100),
                    'feature_status' => 'coming_soon'
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in WhatsAppService::sendBroadcastInfo: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengirim broadcast: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get WhatsApp group statistics
     * Returns statistics for specific province group
     * 
     * @param int $provinceId Province ID
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function getGroupStats(int $provinceId): array
    {
        try {
            // Get group info
            $group = $this->waGroupModel
                ->select('wa_groups.*, provinces.name as province_name')
                ->join('provinces', 'provinces.id = wa_groups.province_id', 'left')
                ->where('wa_groups.province_id', $provinceId)
                ->first();

            if (!$group) {
                return [
                    'success' => false,
                    'message' => 'WhatsApp group tidak ditemukan',
                    'data' => null
                ];
            }

            // Get member statistics for this province
            $totalMembers = $this->memberModel
                ->where('province_id', $provinceId)
                ->countAllResults();

            $activeMembers = $this->memberModel
                ->join('users', 'users.id = member_profiles.user_id')
                ->where('member_profiles.province_id', $provinceId)
                ->where('users.active', 1)
                ->countAllResults();

            // Get recent members (last 30 days)
            $recentMembers = $this->memberModel
                ->where('province_id', $provinceId)
                ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-30 days')))
                ->countAllResults();

            // Calculate engagement metrics
            $engagementRate = $totalMembers > 0
                ? round(($activeMembers / $totalMembers) * 100, 2)
                : 0;

            return [
                'success' => true,
                'message' => 'Statistik WhatsApp group berhasil diambil',
                'data' => [
                    'group_info' => [
                        'id' => $group->id,
                        'group_name' => $group->group_name,
                        'group_link' => $group->group_link,
                        'is_active' => $group->is_active,
                        'created_at' => $group->created_at
                    ],
                    'province_info' => [
                        'id' => $provinceId,
                        'name' => $group->province_name
                    ],
                    'member_stats' => [
                        'total_members' => $totalMembers,
                        'active_members' => $activeMembers,
                        'inactive_members' => $totalMembers - $activeMembers,
                        'recent_members' => $recentMembers,
                        'engagement_rate' => $engagementRate . '%'
                    ],
                    'last_updated' => $group->updated_at
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in WhatsAppService::getGroupStats: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil statistik: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
}
