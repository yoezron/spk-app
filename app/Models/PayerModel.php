<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * PayerModel
 *
 * Model untuk master data pemberi gaji
 * Represents entities that pay member salaries (university, foundation, etc.)
 *
 * @package App\Models
 */
class PayerModel extends Model
{
    protected $table = 'payers';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'name',
        'description',
        'is_active',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|max_length[100]|is_unique[payers.name,id,{id}]',
        'is_active' => 'in_list[0,1]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Nama pemberi gaji harus diisi',
            'max_length' => 'Nama pemberi gaji maksimal 100 karakter',
            'is_unique' => 'Nama pemberi gaji sudah terdaftar'
        ],
        'is_active' => [
            'in_list' => 'Status harus aktif atau tidak aktif'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Get active payers
     *
     * @return array
     */
    public function getActive(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get payer by ID with validation
     *
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        return $this->find($id);
    }

    /**
     * Search payers by name
     *
     * @param string $keyword
     * @return array
     */
    public function search(string $keyword): array
    {
        return $this->like('name', $keyword)
            ->orLike('description', $keyword)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Toggle active status
     *
     * @param int $id
     * @return bool
     */
    public function toggleActive(int $id): bool
    {
        $payer = $this->find($id);
        if (!$payer) {
            return false;
        }

        return $this->update($id, [
            'is_active' => $payer['is_active'] ? 0 : 1
        ]);
    }
}
