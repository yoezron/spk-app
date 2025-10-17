<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * LoginLogModel
 * 
 * Model untuk tracking login/logout activity
 * 
 * @package App\Models
 */
class LoginLogModel extends Model
{
    protected $table            = 'login_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields    = [
        'user_id',
        'email',
        'login_type',
        'status',
        'failure_reason',
        'session_id',
        'remember_token',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'platform',
        'country',
        'city',
        'logout_at',
        'logout_type',
        'is_suspicious',
        'risk_score',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = false;
    protected $deletedField  = false;

    // Validation - basic rules
    protected $validationRules = [
        'user_id'    => 'permit_empty|integer',
        'email'      => 'required|valid_email',
        'status'     => 'required|in_list[success,failed,blocked,locked]',
        'ip_address' => 'permit_empty|valid_ip',
    ];

    protected $validationMessages = [
        'email' => [
            'required'    => 'Email harus diisi',
            'valid_email' => 'Format email tidak valid',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
