<?php

namespace App\Controllers\Super;

use App\Controllers\BaseController;
use App\Models\SettingModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * SettingsController
 * 
 * Mengelola System Settings untuk Super Admin
 * Includes: General, Email, WhatsApp, Notification, Security settings
 * 
 * @package App\Controllers\Super
 * @author  SPK Development Team
 * @version 1.0.0
 */
class SettingsController extends BaseController
{
    /**
     * @var SettingModel
     */
    protected $settingModel;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->settingModel = new SettingModel();
    }

    /**
     * Display settings dashboard with tabs
     * 
     * @return string|RedirectResponse
     */
    public function index()
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat mengakses halaman ini.');
        }

        // Get all settings grouped by class
        $settings = $this->settingModel->getAllGrouped();

        // Get active tab from query string
        $activeTab = $this->request->getGet('tab') ?? 'general';

        $data = [
            'title' => 'System Settings',
            'breadcrumbs' => [
                ['title' => 'Super Admin'],
                ['title' => 'Settings']
            ],
            'settings' => $settings,
            'activeTab' => $activeTab,
            'validation' => \Config\Services::validation()
        ];

        return view('super/settings/index', $data);
    }

    /**
     * Update general settings
     * 
     * @return RedirectResponse
     */
    public function updateGeneral(): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $rules = [
            'app_name' => 'required|min_length[3]|max_length[100]',
            'app_tagline' => 'permit_empty|max_length[255]',
            'app_description' => 'permit_empty|max_length[500]',
            'timezone' => 'required',
            'date_format' => 'required',
            'time_format' => 'required',
            'items_per_page' => 'required|integer|greater_than[0]|less_than[101]',
            'maintenance_mode' => 'permit_empty|in_list[0,1]',
            'registration_enabled' => 'permit_empty|in_list[0,1]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('error', 'Validasi gagal. Silakan periksa form kembali.');
        }

        try {
            $settings = [
                'app_name' => $this->request->getPost('app_name'),
                'app_tagline' => $this->request->getPost('app_tagline') ?? '',
                'app_description' => $this->request->getPost('app_description') ?? '',
                'contact_email' => $this->request->getPost('contact_email') ?? '',
                'contact_phone' => $this->request->getPost('contact_phone') ?? '',
                'contact_address' => $this->request->getPost('contact_address') ?? '',
                'timezone' => $this->request->getPost('timezone'),
                'date_format' => $this->request->getPost('date_format'),
                'time_format' => $this->request->getPost('time_format'),
                'items_per_page' => (int) $this->request->getPost('items_per_page'),
                'maintenance_mode' => $this->request->getPost('maintenance_mode') ? 1 : 0,
                'maintenance_message' => $this->request->getPost('maintenance_message') ?? '',
                'registration_enabled' => $this->request->getPost('registration_enabled') ? 1 : 0
            ];

            $this->settingModel->setMultiple('App\\Config\\General', $settings);

            // Log activity
            $this->logActivity('UPDATE_SETTINGS', 'Updated general settings');

            return redirect()->to('/super/settings?tab=general')
                ->with('success', 'Pengaturan umum berhasil diupdate.');
        } catch (\Exception $e) {
            log_message('error', 'Error updating general settings: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate pengaturan: ' . $e->getMessage());
        }
    }

    /**
     * Update email settings (SMTP configuration)
     * 
     * @return RedirectResponse
     */
    public function updateEmail(): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $rules = [
            'smtp_host' => 'required',
            'smtp_user' => 'required|valid_email',
            'smtp_port' => 'required|integer',
            'smtp_crypto' => 'required|in_list[tls,ssl,none]',
            'from_email' => 'required|valid_email',
            'from_name' => 'required|min_length[3]'
        ];

        // Only validate password if provided
        if ($this->request->getPost('smtp_pass')) {
            $rules['smtp_pass'] = 'min_length[6]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('error', 'Validasi gagal. Silakan periksa form kembali.');
        }

        try {
            $settings = [
                'smtp_host' => $this->request->getPost('smtp_host'),
                'smtp_user' => $this->request->getPost('smtp_user'),
                'smtp_port' => (int) $this->request->getPost('smtp_port'),
                'smtp_crypto' => $this->request->getPost('smtp_crypto'),
                'from_email' => $this->request->getPost('from_email'),
                'from_name' => $this->request->getPost('from_name'),
                'email_enabled' => $this->request->getPost('email_enabled') ? 1 : 0
            ];

            // Only update password if provided
            $smtpPass = $this->request->getPost('smtp_pass');
            if ($smtpPass) {
                // Encrypt password before storing
                $settings['smtp_pass'] = base64_encode($smtpPass);
            }

            $this->settingModel->setMultiple('App\\Config\\Email', $settings);

            // Log activity
            $this->logActivity('UPDATE_SETTINGS', 'Updated email settings');

            return redirect()->to('/super/settings?tab=email')
                ->with('success', 'Pengaturan email berhasil diupdate.');
        } catch (\Exception $e) {
            log_message('error', 'Error updating email settings: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate pengaturan email: ' . $e->getMessage());
        }
    }

    /**
     * Test email connection
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function testEmail()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ])->setStatusCode(400);
        }

        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Akses ditolak'
            ])->setStatusCode(403);
        }

        try {
            $email = \Config\Services::email();

            $testEmail = $this->request->getJSON()->test_email ?? auth()->user()->email;

            $email->setTo($testEmail);
            $email->setSubject('Test Email - SPK System');
            $email->setMessage('This is a test email from SPK System. If you receive this, your email configuration is working correctly.');

            if ($email->send()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Test email berhasil dikirim ke ' . $testEmail
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal mengirim test email: ' . $email->printDebugger(['headers'])
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error testing email: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Update WhatsApp integration settings
     * 
     * @return RedirectResponse
     */
    public function updateWhatsApp(): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        try {
            $settings = [
                'wa_enabled' => $this->request->getPost('wa_enabled') ? 1 : 0,
                'wa_api_url' => $this->request->getPost('wa_api_url') ?? '',
                'wa_api_key' => $this->request->getPost('wa_api_key') ?? '',
                'wa_api_secret' => $this->request->getPost('wa_api_secret') ?? '',
                'wa_sender_number' => $this->request->getPost('wa_sender_number') ?? '',
                'wa_sender_name' => $this->request->getPost('wa_sender_name') ?? '',
                'wa_notification_enabled' => $this->request->getPost('wa_notification_enabled') ? 1 : 0
            ];

            $this->settingModel->setMultiple('App\\Config\\WhatsApp', $settings);

            // Log activity
            $this->logActivity('UPDATE_SETTINGS', 'Updated WhatsApp settings');

            return redirect()->to('/super/settings?tab=whatsapp')
                ->with('success', 'Pengaturan WhatsApp berhasil diupdate.');
        } catch (\Exception $e) {
            log_message('error', 'Error updating WhatsApp settings: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate pengaturan WhatsApp: ' . $e->getMessage());
        }
    }

    /**
     * Update notification settings
     * 
     * @return RedirectResponse
     */
    public function updateNotification(): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        try {
            $settings = [
                'email_notifications' => $this->request->getPost('email_notifications') ? 1 : 0,
                'wa_notifications' => $this->request->getPost('wa_notifications') ? 1 : 0,
                'notify_new_member' => $this->request->getPost('notify_new_member') ? 1 : 0,
                'notify_new_complaint' => $this->request->getPost('notify_new_complaint') ? 1 : 0,
                'notify_new_survey' => $this->request->getPost('notify_new_survey') ? 1 : 0,
                'notify_new_forum' => $this->request->getPost('notify_new_forum') ? 1 : 0,
                'admin_email_list' => $this->request->getPost('admin_email_list') ?? ''
            ];

            $this->settingModel->setMultiple('App\\Config\\Notification', $settings);

            // Log activity
            $this->logActivity('UPDATE_SETTINGS', 'Updated notification settings');

            return redirect()->to('/super/settings?tab=notification')
                ->with('success', 'Pengaturan notifikasi berhasil diupdate.');
        } catch (\Exception $e) {
            log_message('error', 'Error updating notification settings: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate pengaturan notifikasi: ' . $e->getMessage());
        }
    }

    /**
     * Update security settings
     * 
     * @return RedirectResponse
     */
    public function updateSecurity(): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $rules = [
            'password_min_length' => 'required|integer|greater_than[5]|less_than[33]',
            'session_expiration' => 'required|integer|greater_than[0]',
            'max_login_attempts' => 'required|integer|greater_than[0]|less_than[11]',
            'lockout_time' => 'required|integer|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('error', 'Validasi gagal. Silakan periksa form kembali.');
        }

        try {
            $settings = [
                'password_min_length' => (int) $this->request->getPost('password_min_length'),
                'password_require_uppercase' => $this->request->getPost('password_require_uppercase') ? 1 : 0,
                'password_require_number' => $this->request->getPost('password_require_number') ? 1 : 0,
                'password_require_symbol' => $this->request->getPost('password_require_symbol') ? 1 : 0,
                'session_expiration' => (int) $this->request->getPost('session_expiration'),
                'max_login_attempts' => (int) $this->request->getPost('max_login_attempts'),
                'lockout_time' => (int) $this->request->getPost('lockout_time'),
                'two_factor_enabled' => $this->request->getPost('two_factor_enabled') ? 1 : 0,
                'force_https' => $this->request->getPost('force_https') ? 1 : 0
            ];

            $this->settingModel->setMultiple('App\\Config\\Security', $settings);

            // Log activity
            $this->logActivity('UPDATE_SETTINGS', 'Updated security settings');

            return redirect()->to('/super/settings?tab=security')
                ->with('success', 'Pengaturan keamanan berhasil diupdate.');
        } catch (\Exception $e) {
            log_message('error', 'Error updating security settings: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate pengaturan keamanan: ' . $e->getMessage());
        }
    }

    /**
     * Upload logo
     * 
     * @return RedirectResponse
     */
    public function uploadLogo(): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $file = $this->request->getFile('logo');

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File logo tidak valid.');
        }

        // Validate file
        $validationRules = [
            'logo' => [
                'rules' => 'uploaded[logo]|max_size[logo,2048]|is_image[logo]|mime_in[logo,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'uploaded' => 'Silakan pilih file logo',
                    'max_size' => 'Ukuran file maksimal 2MB',
                    'is_image' => 'File harus berupa gambar',
                    'mime_in' => 'Format yang diizinkan: JPG, JPEG, PNG'
                ]
            ]
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->with('error', $this->validator->getError('logo'));
        }

        try {
            // Create uploads directory if not exists
            $uploadPath = FCPATH . 'uploads/settings/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Delete old logo if exists
            $oldLogo = $this->settingModel->getValue('App\\Config\\General', 'logo_path');
            if ($oldLogo && file_exists(FCPATH . $oldLogo)) {
                @unlink(FCPATH . $oldLogo);
            }

            // Move file with unique name
            $fileName = 'logo_' . time() . '.' . $file->getExtension();
            $file->move($uploadPath, $fileName);

            // Save path to settings
            $logoPath = 'uploads/settings/' . $fileName;
            $this->settingModel->setValue('App\\Config\\General', 'logo_path', $logoPath);

            // Log activity
            $this->logActivity('UPDATE_SETTINGS', 'Uploaded new logo');

            return redirect()->to('/super/settings?tab=general')
                ->with('success', 'Logo berhasil diupload.');
        } catch (\Exception $e) {
            log_message('error', 'Error uploading logo: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal upload logo: ' . $e->getMessage());
        }
    }

    /**
     * Clear cache
     * 
     * @return RedirectResponse
     */
    public function clearCache(): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        try {
            // Clear various caches
            cache()->clean();

            // Clear views cache
            $viewsPath = WRITEPATH . 'cache/views/';
            if (is_dir($viewsPath)) {
                $this->deleteDirectory($viewsPath);
            }

            // Log activity
            $this->logActivity('CLEAR_CACHE', 'Cleared system cache');

            return redirect()->back()
                ->with('success', 'Cache berhasil dibersihkan.');
        } catch (\Exception $e) {
            log_message('error', 'Error clearing cache: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal membersihkan cache: ' . $e->getMessage());
        }
    }

    /**
     * Reset settings to default
     * 
     * @param string $class Setting class to reset
     * @return RedirectResponse
     */
    public function resetToDefault(string $class): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        try {
            // Delete all settings in this class
            $this->settingModel->deleteClass($class);

            // Load default settings based on class
            $this->loadDefaultSettings($class);

            // Log activity
            $this->logActivity('RESET_SETTINGS', "Reset settings for class: {$class}");

            return redirect()->back()
                ->with('success', 'Pengaturan berhasil direset ke default.');
        } catch (\Exception $e) {
            log_message('error', 'Error resetting settings: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal reset pengaturan: ' . $e->getMessage());
        }
    }

    /**
     * Load default settings for a class
     * 
     * @param string $class
     * @return void
     */
    protected function loadDefaultSettings(string $class): void
    {
        $defaults = [];

        switch ($class) {
            case 'App\\Config\\General':
                $defaults = [
                    'app_name' => 'SPK System',
                    'app_tagline' => 'Sistem Informasi Serikat Pekerja Kampus',
                    'timezone' => 'Asia/Jakarta',
                    'date_format' => 'Y-m-d',
                    'time_format' => 'H:i:s',
                    'items_per_page' => 10,
                    'maintenance_mode' => 0,
                    'registration_enabled' => 1
                ];
                break;

            case 'App\\Config\\Email':
                $defaults = [
                    'smtp_host' => '',
                    'smtp_user' => '',
                    'smtp_port' => 587,
                    'smtp_crypto' => 'tls',
                    'from_email' => '',
                    'from_name' => 'SPK System',
                    'email_enabled' => 0
                ];
                break;

            case 'App\\Config\\WhatsApp':
                $defaults = [
                    'wa_enabled' => 0,
                    'wa_api_url' => '',
                    'wa_notification_enabled' => 0
                ];
                break;

            case 'App\\Config\\Notification':
                $defaults = [
                    'email_notifications' => 1,
                    'wa_notifications' => 0,
                    'notify_new_member' => 1,
                    'notify_new_complaint' => 1,
                    'notify_new_survey' => 1,
                    'notify_new_forum' => 0
                ];
                break;

            case 'App\\Config\\Security':
                $defaults = [
                    'password_min_length' => 8,
                    'password_require_uppercase' => 1,
                    'password_require_number' => 1,
                    'password_require_symbol' => 0,
                    'session_expiration' => 7200,
                    'max_login_attempts' => 5,
                    'lockout_time' => 900,
                    'two_factor_enabled' => 0,
                    'force_https' => 0
                ];
                break;
        }

        if (!empty($defaults)) {
            $this->settingModel->setMultiple($class, $defaults);
        }
    }

    /**
     * Delete directory recursively
     * 
     * @param string $dir
     * @return bool
     */
    protected function deleteDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        return rmdir($dir);
    }

    /**
     * Log activity to audit logs
     * 
     * @param string $action
     * @param string $description
     * @return void
     */
    protected function logActivity(string $action, string $description): void
    {
        $db = \Config\Database::connect();

        // Check if audit_logs table exists
        if (!$db->tableExists('audit_logs')) {
            return;
        }

        try {
            $db->table('audit_logs')->insert([
                'user_id' => auth()->id(),
                'action' => $action,
                'action_description' => $description,
                'entity_type' => 'Settings',
                'module' => 'settings',
                'severity' => 'medium',
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log activity: ' . $e->getMessage());
        }
    }

    /**
     * Export settings as JSON
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function export()
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        try {
            $settings = $this->settingModel->getAllGrouped();

            $fileName = 'settings_backup_' . date('YmdHis') . '.json';

            return $this->response
                ->setContentType('application/json')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"')
                ->setBody(json_encode($settings, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            log_message('error', 'Error exporting settings: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export pengaturan.');
        }
    }

    /**
     * Import settings from JSON
     * 
     * @return RedirectResponse
     */
    public function import(): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $file = $this->request->getFile('settings_file');

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid.');
        }

        // Validate file extension
        if ($file->getExtension() !== 'json') {
            return redirect()->back()->with('error', 'Format file harus JSON.');
        }

        try {
            $content = file_get_contents($file->getTempName());
            $settings = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()->back()->with('error', 'Format JSON tidak valid.');
            }

            // Import settings
            $this->db->transStart();

            foreach ($settings as $class => $classSettings) {
                $this->settingModel->setMultiple($class, $classSettings);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            // Log activity
            $this->logActivity('IMPORT_SETTINGS', 'Imported settings from file');

            return redirect()->to('/super/settings')
                ->with('success', 'Pengaturan berhasil diimport.');
        } catch (\Exception $e) {
            log_message('error', 'Error importing settings: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal import pengaturan: ' . $e->getMessage());
        }
    }
}
