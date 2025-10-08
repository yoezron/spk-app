<?php

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

/**
 * UserModel
 * 
 * Extended dari Shield UserModel dengan custom functionality untuk SPK
 * Mengelola data user dan integrasi dengan member profiles
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class UserModel extends ShieldUserModel
{
    protected $returnType = 'CodeIgniter\Shield\Entities\User';
    protected $allowCallbacks = true;

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get user with member profile
     * 
     * @return object
     */
    public function withProfile()
    {
        return $this->select('users.*, member_profiles.*')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left');
    }

    /**
     * Get user with roles/groups
     * 
     * @return object
     */
    public function withRoles()
    {
        return $this->select('users.*, auth_groups_users.group')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id', 'left');
    }

    /**
     * Get user with complete data (profile + roles)
     * 
     * @return object
     */
    public function withComplete()
    {
        return $this->select('users.*, member_profiles.*, auth_groups_users.group')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id', 'left');
    }

    // ========================================
    // SCOPES - FILTERING
    // ========================================

    /**
     * Get only active users
     * 
     * @return object
     */
    public function activeUsers()
    {
        return $this->where('users.active', 1);
    }

    /**
     * Get only inactive/suspended users
     * 
     * @return object
     */
    public function inactiveUsers()
    {
        return $this->where('users.active', 0);
    }

    /**
     * Get users by role/group
     * 
     * @param string $role Role name (e.g., 'Super Admin', 'Pengurus')
     * @return object
     */
    public function byRole(string $role)
    {
        return $this->select('users.*')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->where('auth_groups_users.group', $role);
    }

    /**
     * Get users by multiple roles
     * 
     * @param array $roles Array of role names
     * @return object
     */
    public function byRoles(array $roles)
    {
        return $this->select('users.*')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->whereIn('auth_groups_users.group', $roles);
    }

    /**
     * Get users who are members (Anggota or higher, excluding Calon Anggota)
     * 
     * @return object
     */
    public function members()
    {
        return $this->byRoles(['Super Admin', 'Pengurus', 'Koordinator Wilayah', 'Anggota']);
    }

    /**
     * Get pending members (Calon Anggota)
     * 
     * @return object
     */
    public function pendingMembers()
    {
        return $this->byRole('Calon Anggota');
    }

    /**
     * Search users by name, email, or username
     * 
     * @param string $keyword Search keyword
     * @return object
     */
    public function search(string $keyword)
    {
        return $this->groupStart()
            ->like('username', $keyword)
            ->orLike('users.id', $keyword) // Search by user ID
            ->groupEnd();
    }

    /**
     * Search users including member profile data
     * 
     * @param string $keyword Search keyword
     * @return object
     */
    public function searchWithProfile(string $keyword)
    {
        return $this->withProfile()
            ->groupStart()
            ->like('username', $keyword)
            ->orLike('member_profiles.full_name', $keyword)
            ->orLike('member_profiles.member_number', $keyword)
            ->orLike('member_profiles.nidn_nip', $keyword)
            ->groupEnd();
    }

    // ========================================
    // CUSTOM METHODS
    // ========================================

    /**
     * Get user by username
     * 
     * @param string $username Username
     * @return object|null
     */
    public function findByUsername(string $username)
    {
        return $this->where('username', $username)->first();
    }

    /**
     * Get user by email (from auth_identities)
     * 
     * @param string $email Email address
     * @return object|null
     */
    public function findByEmail(string $email)
    {
        return $this->select('users.*')
            ->join('auth_identities', 'auth_identities.user_id = users.id')
            ->where('auth_identities.type', 'email_password')
            ->where('auth_identities.secret', $email)
            ->first();
    }

    /**
     * Get user by member number
     * 
     * @param string $memberNumber Member number
     * @return object|null
     */
    public function findByMemberNumber(string $memberNumber)
    {
        return $this->withProfile()
            ->where('member_profiles.member_number', $memberNumber)
            ->first();
    }

    /**
     * Check if username exists
     * 
     * @param string $username Username to check
     * @param int|null $excludeUserId Exclude this user ID (for updates)
     * @return bool
     */
    public function usernameExists(string $username, ?int $excludeUserId = null): bool
    {
        $builder = $this->where('username', $username);

        if ($excludeUserId) {
            $builder->where('id !=', $excludeUserId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Get total users count
     * 
     * @return int
     */
    public function getTotalUsers(): int
    {
        return $this->countAllResults(false);
    }

    /**
     * Get total active users count
     * 
     * @return int
     */
    public function getTotalActiveUsers(): int
    {
        return $this->where('active', 1)->countAllResults(false);
    }

    /**
     * Get total users by role
     * 
     * @param string $role Role name
     * @return int
     */
    public function getTotalByRole(string $role): int
    {
        return $this->byRole($role)->countAllResults(false);
    }

    /**
     * Get users registered in date range
     * 
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @return object
     */
    public function registeredBetween(string $startDate, string $endDate)
    {
        return $this->where('users.created_at >=', $startDate . ' 00:00:00')
            ->where('users.created_at <=', $endDate . ' 23:59:59');
    }

    /**
     * Get recently registered users
     * 
     * @param int $days Number of days (default: 7)
     * @param int $limit Limit results (default: 10)
     * @return array
     */
    public function recentlyRegistered(int $days = 7, int $limit = 10): array
    {
        return $this->where('users.created_at >=', date('Y-m-d H:i:s', strtotime("-{$days} days")))
            ->orderBy('users.created_at', 'DESC')
            ->limit($limit)
            ->find();
    }

    /**
     * Activate user account
     * 
     * @param int $userId User ID
     * @return bool
     */
    public function activateUser(int $userId): bool
    {
        return $this->update($userId, ['active' => 1]);
    }

    /**
     * Deactivate/suspend user account
     * 
     * @param int $userId User ID
     * @return bool
     */
    public function deactivateUser(int $userId): bool
    {
        return $this->update($userId, ['active' => 0]);
    }

    /**
     * Assign role to user
     * 
     * @param int $userId User ID
     * @param string $role Role name
     * @return bool
     */
    public function assignRole(int $userId, string $role): bool
    {
        // Remove existing role first
        $this->db->table('auth_groups_users')->where('user_id', $userId)->delete();

        // Insert new role
        return $this->db->table('auth_groups_users')->insert([
            'user_id'    => $userId,
            'group'      => $role,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get user's role
     * 
     * @param int $userId User ID
     * @return string|null
     */
    public function getUserRole(int $userId): ?string
    {
        $result = $this->db->table('auth_groups_users')
            ->where('user_id', $userId)
            ->get()
            ->getRow();

        return $result ? $result->group : null;
    }

    // ========================================
    // STATISTICS METHODS
    // ========================================

    /**
     * Get user statistics
     * 
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'total'             => $this->getTotalUsers(),
            'active'            => $this->getTotalActiveUsers(),
            'inactive'          => $this->getTotalUsers() - $this->getTotalActiveUsers(),
            'super_admin'       => $this->getTotalByRole('Super Admin'),
            'pengurus'          => $this->getTotalByRole('Pengurus'),
            'koordinator'       => $this->getTotalByRole('Koordinator Wilayah'),
            'anggota'           => $this->getTotalByRole('Anggota'),
            'calon_anggota'     => $this->getTotalByRole('Calon Anggota'),
            'registered_today'  => $this->registeredBetween(date('Y-m-d'), date('Y-m-d'))->countAllResults(false),
            'registered_week'   => $this->registeredBetween(date('Y-m-d', strtotime('-7 days')), date('Y-m-d'))->countAllResults(false),
            'registered_month'  => $this->registeredBetween(date('Y-m-01'), date('Y-m-d'))->countAllResults(false),
        ];
    }
}
