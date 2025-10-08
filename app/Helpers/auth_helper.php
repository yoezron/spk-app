<?php

if (!function_exists('auth')) {
    /**
     * Get authenticated user data
     */
    function auth()
    {
        $session = session();
        return $session->get('user');
    }
}

if (!function_exists('is_logged_in')) {
    /**
     * Check if user is logged in
     */
    function is_logged_in(): bool
    {
        $session = session();
        return $session->has('user');
    }
}

if (!function_exists('has_permission')) {
    /**
     * Check if user has specific permission
     */
    function has_permission(string $permission): bool
    {
        $session = session();
        $userPermissions = $session->get('permissions') ?? [];
        return in_array($permission, $userPermissions);
    }
}

if (!function_exists('is_super_admin')) {
    /**
     * Check if user is super admin
     */
    function is_super_admin(): bool
    {
        $session = session();
        $user = $session->get('user');
        return $user && isset($user['role_level']) && $user['role_level'] === 4;
    }
}

if (!function_exists('user_region_id')) {
    /**
     * Get user's region ID for ABAC
     */
    function user_region_id(): ?int
    {
        $session = session();
        $user = $session->get('user');
        return $user['region_id'] ?? null;
    }
}

if (!function_exists('can_access_region')) {
    /**
     * Check if user can access specific region (ABAC)
     * Super Admin and Pengurus can access all regions
     * Koordinator can only access their assigned region
     */
    function can_access_region(int $regionId): bool
    {
        $session = session();
        $user = $session->get('user');
        
        if (!$user) {
            return false;
        }
        
        // Super Admin and Pengurus can access all regions
        if ($user['role_level'] >= 3) {
            return true;
        }
        
        // Koordinator can only access their region
        if ($user['role_level'] === 2) {
            return $user['region_id'] === $regionId;
        }
        
        return false;
    }
}

if (!function_exists('log_activity')) {
    /**
     * Log user activity
     */
    function log_activity(string $action, string $resource, ?int $resourceId = null, ?string $description = null): void
    {
        $db = \Config\Database::connect();
        $session = session();
        $user = $session->get('user');
        
        $data = [
            'user_id'     => $user['id'] ?? null,
            'action'      => $action,
            'resource'    => $resource,
            'resource_id' => $resourceId,
            'description' => $description,
            'ip_address'  => \Config\Services::request()->getIPAddress(),
            'user_agent'  => \Config\Services::request()->getUserAgent()->getAgentString(),
            'created_at'  => date('Y-m-d H:i:s'),
        ];
        
        $db->table('activity_logs')->insert($data);
    }
}

if (!function_exists('generate_member_number')) {
    /**
     * Generate unique member number
     * Format: SPK-YYYY-XXXX
     */
    function generate_member_number(): string
    {
        $db = \Config\Database::connect();
        $year = date('Y');
        $prefix = "SPK-{$year}-";
        
        // Get last member number for current year
        $lastMember = $db->table('members')
            ->like('member_number', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRow();
        
        if ($lastMember) {
            // Extract number and increment
            $lastNumber = (int) substr($lastMember->member_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('generate_ticket_number')) {
    /**
     * Generate unique ticket number
     * Format: TKT-YYYYMMDD-XXXX
     */
    function generate_ticket_number(): string
    {
        $db = \Config\Database::connect();
        $date = date('Ymd');
        $prefix = "TKT-{$date}-";
        
        // Get last ticket number for current date
        $lastTicket = $db->table('tickets')
            ->like('ticket_number', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRow();
        
        if ($lastTicket) {
            $lastNumber = (int) substr($lastTicket->ticket_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
