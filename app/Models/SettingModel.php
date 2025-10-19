<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * SettingModel
 * 
 * Model untuk mengelola system settings menggunakan CodeIgniter Settings Library
 * Settings disimpan dalam format key-value dengan class untuk grouping
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class SettingModel extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'class',
        'key',
        'value',
        'type',
        'context',
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
        'class' => 'required|max_length[255]',
        'key' => 'required|max_length[255]',
        'value' => 'permit_empty',
        'type' => 'required|in_list[string,int,bool,array,float,null]'
    ];

    protected $validationMessages = [
        'class' => [
            'required' => 'Class setting wajib diisi'
        ],
        'key' => [
            'required' => 'Key setting wajib diisi'
        ]
    ];

    /**
     * Get all settings grouped by class
     * 
     * @return array
     */
    public function getAllGrouped(): array
    {
        $settings = $this->orderBy('class', 'ASC')
            ->orderBy('key', 'ASC')
            ->findAll();

        $grouped = [];
        foreach ($settings as $setting) {
            if (!isset($grouped[$setting->class])) {
                $grouped[$setting->class] = [];
            }
            $grouped[$setting->class][$setting->key] = $this->castValue($setting->value, $setting->type);
        }

        return $grouped;
    }

    /**
     * Get settings by class
     * 
     * @param string $class
     * @return array
     */
    public function getByClass(string $class): array
    {
        $settings = $this->where('class', $class)
            ->orderBy('key', 'ASC')
            ->findAll();

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = $this->castValue($setting->value, $setting->type);
        }

        return $result;
    }

    /**
     * Get single setting value
     * 
     * @param string $class
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getValue(string $class, string $key, $default = null)
    {
        $setting = $this->where('class', $class)
            ->where('key', $key)
            ->first();

        if (!$setting) {
            return $default;
        }

        return $this->castValue($setting->value, $setting->type);
    }

    /**
     * Set or update setting
     * 
     * @param string $class
     * @param string $key
     * @param mixed $value
     * @param string|null $type Auto-detect if null
     * @return bool
     */
    public function setValue(string $class, string $key, $value, ?string $type = null): bool
    {
        // Auto-detect type if not provided
        if ($type === null) {
            $type = $this->detectType($value);
        }

        // Convert value to string for storage
        $storedValue = $this->prepareValue($value, $type);

        // Check if setting exists
        $existing = $this->where('class', $class)
            ->where('key', $key)
            ->first();

        $data = [
            'class' => $class,
            'key' => $key,
            'value' => $storedValue,
            'type' => $type
        ];

        if ($existing) {
            return $this->update($existing->id, $data);
        } else {
            return $this->insert($data) !== false;
        }
    }

    /**
     * Bulk set settings
     * 
     * @param string $class
     * @param array $settings Key-value pairs
     * @return bool
     */
    public function setMultiple(string $class, array $settings): bool
    {
        $this->db->transStart();

        foreach ($settings as $key => $value) {
            $this->setValue($class, $key, $value);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Delete setting
     * 
     * @param string $class
     * @param string $key
     * @return bool
     */
    public function deleteSetting(string $class, string $key): bool
    {
        return $this->where('class', $class)
            ->where('key', $key)
            ->delete();
    }

    /**
     * Delete all settings in a class
     * 
     * @param string $class
     * @return bool
     */
    public function deleteClass(string $class): bool
    {
        return $this->where('class', $class)->delete();
    }

    /**
     * Cast value from string to proper type
     * 
     * @param string $value
     * @param string $type
     * @return mixed
     */
    protected function castValue(string $value, string $type)
    {
        switch ($type) {
            case 'int':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'bool':
                return (bool) $value || $value === 'true' || $value === '1';
            case 'array':
                return json_decode($value, true) ?? [];
            case 'null':
                return null;
            default: // string
                return $value;
        }
    }

    /**
     * Prepare value for storage
     * 
     * @param mixed $value
     * @param string $type
     * @return string
     */
    protected function prepareValue($value, string $type): string
    {
        switch ($type) {
            case 'bool':
                return $value ? '1' : '0';
            case 'array':
                return json_encode($value);
            case 'null':
                return '';
            default:
                return (string) $value;
        }
    }

    /**
     * Auto-detect value type
     * 
     * @param mixed $value
     * @return string
     */
    protected function detectType($value): string
    {
        if (is_bool($value)) {
            return 'bool';
        }
        if (is_int($value)) {
            return 'int';
        }
        if (is_float($value)) {
            return 'float';
        }
        if (is_array($value)) {
            return 'array';
        }
        if ($value === null) {
            return 'null';
        }
        return 'string';
    }

    /**
     * Get available setting classes
     * 
     * @return array
     */
    public function getClasses(): array
    {
        $classes = $this->select('class')
            ->distinct()
            ->orderBy('class', 'ASC')
            ->findColumn('class');

        return $classes ?? [];
    }

    /**
     * Count settings by class
     * 
     * @param string $class
     * @return int
     */
    public function countByClass(string $class): int
    {
        return $this->where('class', $class)->countAllResults();
    }

    /**
     * Search settings
     * 
     * @param string $search
     * @return array
     */
    public function search(string $search): array
    {
        return $this->groupStart()
            ->like('key', $search)
            ->orLike('value', $search)
            ->orLike('class', $search)
            ->groupEnd()
            ->orderBy('class', 'ASC')
            ->orderBy('key', 'ASC')
            ->findAll();
    }
}
