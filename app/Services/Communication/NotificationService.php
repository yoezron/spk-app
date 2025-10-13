<?php

namespace App\Services\Communication;

use App\Models\UserModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * NotificationService
 * 
 * Menangani in-app notification system
 * Termasuk send, read/unread status, dan notification management
 * 
 * @package App\Services\Communication
 * @author  SPK Development Team
 * @version 1.0.0
 */
class NotificationService
{
    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * @var \CodeIgniter\Database\BaseConnection
     */
    protected $db;

    /**
     * @var string
     */
    protected $table = 'notifications';

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Send notification to single user
     * Creates a new notification record for specific user
     * 
     * @param int $userId Recipient user ID
     * @param string $title Notification title
     * @param string $message Notification message
     * @param array $data Additional data (optional) - stored as JSON
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function send(int $userId, string $title, string $message, array $data = []): array
    {
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

            // Prepare notification data
            $notificationData = [
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'data' => !empty($data) ? json_encode($data) : null,
                'type' => $data['type'] ?? 'info',
                'is_read' => 0,
                'read_at' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Insert notification
            $builder = $this->db->table($this->table);
            $result = $builder->insert($notificationData);

            if (!$result) {
                throw new \Exception('Gagal menyimpan notifikasi');
            }

            $notificationId = $this->db->insertID();

            return [
                'success' => true,
                'message' => 'Notifikasi berhasil dikirim',
                'data' => [
                    'notification_id' => $notificationId,
                    'user_id' => $userId
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in NotificationService::send: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengirim notifikasi: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Send notification to multiple users
     * Bulk insert notifications for multiple recipients
     * 
     * @param array $userIds Array of recipient user IDs
     * @param string $title Notification title
     * @param string $message Notification message
     * @param array $data Additional data (optional)
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function sendBulk(array $userIds, string $title, string $message, array $data = []): array
    {
        $this->db->transStart();

        try {
            if (empty($userIds)) {
                return [
                    'success' => false,
                    'message' => 'Daftar user ID kosong',
                    'data' => null
                ];
            }

            // Validate all users exist
            $validUsers = $this->userModel->whereIn('id', $userIds)->findAll();
            $validUserIds = array_column($validUsers, 'id');

            if (empty($validUserIds)) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada user valid yang ditemukan',
                    'data' => null
                ];
            }

            // Prepare batch insert data
            $batchData = [];
            $timestamp = date('Y-m-d H:i:s');
            $jsonData = !empty($data) ? json_encode($data) : null;

            foreach ($validUserIds as $userId) {
                $batchData[] = [
                    'user_id' => $userId,
                    'title' => $title,
                    'message' => $message,
                    'data' => $jsonData,
                    'type' => $data['type'] ?? 'info',
                    'is_read' => 0,
                    'read_at' => null,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ];
            }

            // Bulk insert
            $builder = $this->db->table($this->table);
            $result = $builder->insertBatch($batchData);

            if (!$result) {
                throw new \Exception('Gagal mengirim notifikasi bulk');
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new DatabaseException('Transaction failed');
            }

            return [
                'success' => true,
                'message' => sprintf('Notifikasi berhasil dikirim ke %d user', count($validUserIds)),
                'data' => [
                    'total_sent' => count($validUserIds),
                    'invalid_users' => count($userIds) - count($validUserIds)
                ]
            ];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error in NotificationService::sendBulk: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengirim notifikasi bulk: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Send notification to all users with specific role
     * Bulk send to all members of a role/group
     * 
     * @param string $role Role name (e.g., "Anggota", "Koordinator Wilayah")
     * @param string $title Notification title
     * @param string $message Notification message
     * @param array $data Additional data (optional)
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function sendToRole(string $role, string $title, string $message, array $data = []): array
    {
        try {
            // Get all users with specific role from auth_groups_users
            $builder = $this->db->table('auth_groups_users');
            $builder->select('auth_groups_users.user_id')
                ->join('auth_groups', 'auth_groups.id = auth_groups_users.group')
                ->where('auth_groups.name', $role);

            $users = $builder->get()->getResultArray();

            if (empty($users)) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada user dengan role tersebut',
                    'data' => null
                ];
            }

            $userIds = array_column($users, 'user_id');

            // Use sendBulk method
            return $this->sendBulk($userIds, $title, $message, $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in NotificationService::sendToRole: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengirim notifikasi ke role: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Mark notification as read
     * Updates single notification read status
     * 
     * @param int $notificationId Notification ID
     * @param int $userId User ID (for verification)
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function markAsRead(int $notificationId, int $userId): array
    {
        try {
            $builder = $this->db->table($this->table);

            // Verify notification belongs to user
            $notification = $builder
                ->where('id', $notificationId)
                ->where('user_id', $userId)
                ->get()
                ->getRowArray();

            if (!$notification) {
                return [
                    'success' => false,
                    'message' => 'Notifikasi tidak ditemukan',
                    'data' => null
                ];
            }

            // Update read status
            $updateData = [
                'is_read' => 1,
                'read_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $builder->where('id', $notificationId)
                ->update($updateData);

            return [
                'success' => true,
                'message' => 'Notifikasi ditandai sebagai sudah dibaca',
                'data' => [
                    'notification_id' => $notificationId
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in NotificationService::markAsRead: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal update status notifikasi: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Mark all notifications as read for user
     * Bulk update all unread notifications
     * 
     * @param int $userId User ID
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function markAllAsRead(int $userId): array
    {
        try {
            $builder = $this->db->table($this->table);

            // Update all unread notifications for user
            $updateData = [
                'is_read' => 1,
                'read_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $builder->where('user_id', $userId)
                ->where('is_read', 0)
                ->update($updateData);

            $affectedRows = $this->db->affectedRows();

            return [
                'success' => true,
                'message' => sprintf('Berhasil menandai %d notifikasi sebagai sudah dibaca', $affectedRows),
                'data' => [
                    'user_id' => $userId,
                    'marked_count' => $affectedRows
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in NotificationService::markAllAsRead: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal update notifikasi: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get unread notifications for user
     * Returns list of unread notifications with limit
     * 
     * @param int $userId User ID
     * @param int $limit Maximum number of notifications to return
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function getUnread(int $userId, int $limit = 10): array
    {
        try {
            $builder = $this->db->table($this->table);

            $notifications = $builder
                ->where('user_id', $userId)
                ->where('is_read', 0)
                ->orderBy('created_at', 'DESC')
                ->limit($limit)
                ->get()
                ->getResultArray();

            // Parse JSON data field
            foreach ($notifications as &$notification) {
                if (!empty($notification['data'])) {
                    $notification['data'] = json_decode($notification['data'], true);
                }
            }

            return [
                'success' => true,
                'message' => 'Notifikasi unread berhasil diambil',
                'data' => [
                    'notifications' => $notifications,
                    'total' => count($notifications)
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in NotificationService::getUnread: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil notifikasi: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get all notifications for user with pagination
     * Returns paginated list of all notifications (read and unread)
     * 
     * @param int $userId User ID
     * @param int $limit Number of notifications per page
     * @param int $offset Offset for pagination
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function getAll(int $userId, int $limit = 20, int $offset = 0): array
    {
        try {
            $builder = $this->db->table($this->table);

            // Get total count
            $totalCount = $builder->where('user_id', $userId)->countAllResults(false);

            // Get paginated notifications
            $notifications = $builder
                ->where('user_id', $userId)
                ->orderBy('created_at', 'DESC')
                ->limit($limit, $offset)
                ->get()
                ->getResultArray();

            // Parse JSON data field
            foreach ($notifications as &$notification) {
                if (!empty($notification['data'])) {
                    $notification['data'] = json_decode($notification['data'], true);
                }
            }

            $totalPages = ceil($totalCount / $limit);
            $currentPage = floor($offset / $limit) + 1;

            return [
                'success' => true,
                'message' => 'Notifikasi berhasil diambil',
                'data' => [
                    'notifications' => $notifications,
                    'pagination' => [
                        'total' => $totalCount,
                        'per_page' => $limit,
                        'current_page' => $currentPage,
                        'total_pages' => $totalPages,
                        'offset' => $offset
                    ]
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in NotificationService::getAll: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil notifikasi: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Delete notification
     * Soft or hard delete notification for user
     * 
     * @param int $notificationId Notification ID
     * @param int $userId User ID (for verification)
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function deleteNotification(int $notificationId, int $userId): array
    {
        try {
            $builder = $this->db->table($this->table);

            // Verify notification belongs to user
            $notification = $builder
                ->where('id', $notificationId)
                ->where('user_id', $userId)
                ->get()
                ->getRowArray();

            if (!$notification) {
                return [
                    'success' => false,
                    'message' => 'Notifikasi tidak ditemukan',
                    'data' => null
                ];
            }

            // Delete notification
            $builder->where('id', $notificationId)
                ->where('user_id', $userId)
                ->delete();

            return [
                'success' => true,
                'message' => 'Notifikasi berhasil dihapus',
                'data' => [
                    'notification_id' => $notificationId
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in NotificationService::deleteNotification: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menghapus notifikasi: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get unread notification count for user
     * Returns total number of unread notifications
     * 
     * @param int $userId User ID
     * @return int Unread notification count
     */
    public function getUnreadCount(int $userId): int
    {
        try {
            $builder = $this->db->table($this->table);

            $count = $builder
                ->where('user_id', $userId)
                ->where('is_read', 0)
                ->countAllResults();

            return $count;
        } catch (\Exception $e) {
            log_message('error', 'Error in NotificationService::getUnreadCount: ' . $e->getMessage());
            return 0;
        }
    }
}
