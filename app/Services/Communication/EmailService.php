<?php

namespace App\Services\Communication;

use App\Models\UserModel;
use CodeIgniter\Email\Email;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * EmailService
 * 
 * Menangani email sending dengan template system
 * Termasuk basic email, template emails, bulk sending, dan queue support
 * 
 * @package App\Services\Communication
 * @author  SPK Development Team
 * @version 1.0.0
 */
class EmailService
{
    /**
     * @var Email
     */
    protected $email;

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
    protected $queueTable = 'email_queue';

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->email = \Config\Services::email();
        $this->userModel = new UserModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Send basic email
     * Sends simple email with subject and message
     * 
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $message Email message (HTML or plain text)
     * @param array $options Additional options (cc, bcc, attachments, etc)
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function send(string $to, string $subject, string $message, array $options = []): array
    {
        try {
            // Configure email
            $this->email->setTo($to);
            $this->email->setSubject($subject);

            // Check if message is HTML or plain text
            if (isset($options['html']) && $options['html'] === true) {
                $this->email->setMessage($message);
            } else {
                // Auto-detect HTML
                if (strip_tags($message) !== $message) {
                    $this->email->setMessage($message);
                } else {
                    $this->email->setMessage($message);
                }
            }

            // Set from address
            if (isset($options['from'])) {
                $this->email->setFrom($options['from']['email'], $options['from']['name'] ?? '');
            } else {
                // Default from config
                $fromEmail = env('email.fromEmail', 'noreply@spk.org');
                $fromName = env('email.fromName', 'SPK Notification');
                $this->email->setFrom($fromEmail, $fromName);
            }

            // CC
            if (isset($options['cc'])) {
                if (is_array($options['cc'])) {
                    $this->email->setCC(implode(',', $options['cc']));
                } else {
                    $this->email->setCC($options['cc']);
                }
            }

            // BCC
            if (isset($options['bcc'])) {
                if (is_array($options['bcc'])) {
                    $this->email->setBCC(implode(',', $options['bcc']));
                } else {
                    $this->email->setBCC($options['bcc']);
                }
            }

            // Attachments
            if (isset($options['attachments']) && is_array($options['attachments'])) {
                foreach ($options['attachments'] as $attachment) {
                    $this->email->attach($attachment);
                }
            }

            // Send email
            $result = $this->email->send();

            if (!$result) {
                $error = $this->email->printDebugger(['headers']);
                log_message('error', 'Email failed to send: ' . $error);

                return [
                    'success' => false,
                    'message' => 'Gagal mengirim email',
                    'data' => ['debug' => $error]
                ];
            }

            return [
                'success' => true,
                'message' => 'Email berhasil dikirim',
                'data' => [
                    'to' => $to,
                    'subject' => $subject
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in EmailService::send: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengirim email: ' . $e->getMessage(),
                'data' => null
            ];
        } finally {
            // Clear email data for next send
            $this->email->clear();
        }
    }

    /**
     * Send email with template
     * Uses view template for email content
     * 
     * @param string $to Recipient email address
     * @param string $template Template view path (e.g., 'emails/welcome')
     * @param array $data Data to pass to template
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function sendTemplate(string $to, string $template, array $data): array
    {
        try {
            // Load template
            $message = view($template, $data);

            if (empty($message)) {
                throw new \Exception("Template '{$template}' tidak ditemukan atau kosong");
            }

            // Get subject from data or use default
            $subject = $data['subject'] ?? 'Notifikasi SPK';

            // Prepare options
            $options = [
                'html' => true
            ];

            // Pass through additional options from data
            if (isset($data['cc'])) {
                $options['cc'] = $data['cc'];
            }
            if (isset($data['bcc'])) {
                $options['bcc'] = $data['bcc'];
            }
            if (isset($data['attachments'])) {
                $options['attachments'] = $data['attachments'];
            }

            // Send using base send method
            return $this->send($to, $subject, $message, $options);
        } catch (\Exception $e) {
            log_message('error', 'Error in EmailService::sendTemplate: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengirim email template: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Send email to multiple recipients
     * Bulk email sending with individual or batch processing
     * 
     * @param array $recipients Array of email addresses or array of ['email' => '', 'name' => '']
     * @param string $subject Email subject
     * @param string $message Email message
     * @param array $options Additional options
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function sendBulk(array $recipients, string $subject, string $message, array $options = []): array
    {
        try {
            if (empty($recipients)) {
                return [
                    'success' => false,
                    'message' => 'Daftar penerima kosong',
                    'data' => null
                ];
            }

            $results = [
                'total' => count($recipients),
                'success' => 0,
                'failed' => 0,
                'details' => []
            ];

            // Process each recipient
            foreach ($recipients as $recipient) {
                $email = is_array($recipient) ? $recipient['email'] : $recipient;

                $result = $this->send($email, $subject, $message, $options);

                if ($result['success']) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                }

                $results['details'][$email] = $result;
            }

            return [
                'success' => $results['failed'] === 0,
                'message' => sprintf(
                    'Berhasil: %d, Gagal: %d dari %d email',
                    $results['success'],
                    $results['failed'],
                    $results['total']
                ),
                'data' => $results
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in EmailService::sendBulk: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengirim bulk email: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Queue email for later sending
     * Stores email in database queue for background processing
     * 
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $message Email message
     * @param array $options Additional options
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function queueEmail(string $to, string $subject, string $message, array $options = []): array
    {
        try {
            $queueData = [
                'to' => $to,
                'subject' => $subject,
                'message' => $message,
                'options' => json_encode($options),
                'status' => 'pending',
                'attempts' => 0,
                'max_attempts' => 3,
                'scheduled_at' => $options['scheduled_at'] ?? date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $builder = $this->db->table($this->queueTable);
            $result = $builder->insert($queueData);

            if (!$result) {
                throw new \Exception('Gagal menyimpan email ke queue');
            }

            $queueId = $this->db->insertID();

            return [
                'success' => true,
                'message' => 'Email berhasil ditambahkan ke queue',
                'data' => [
                    'queue_id' => $queueId,
                    'scheduled_at' => $queueData['scheduled_at']
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in EmailService::queueEmail: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal queue email: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Send verification email
     * Sends email verification link to user
     * 
     * @param object $user User entity or object with email property
     * @param string $token Verification token
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function sendVerificationEmail(object $user, string $token): array
    {
        try {
            $verificationUrl = base_url("auth/verify-email/{$token}");

            $data = [
                'subject' => 'Verifikasi Email - SPK',
                'user_name' => $user->full_name ?? $user->username ?? 'Member',
                'verification_url' => $verificationUrl,
                'token' => $token,
                'expires_in' => '24 jam'
            ];

            return $this->sendTemplate($user->email, 'emails/verification', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in EmailService::sendVerificationEmail: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengirim email verifikasi: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Send welcome email
     * Sends welcome email to newly registered/approved member
     * 
     * @param object $user User entity or object
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function sendWelcomeEmail(object $user): array
    {
        try {
            $data = [
                'subject' => 'Selamat Datang di SPK!',
                'user_name' => $user->full_name ?? $user->username ?? 'Member',
                'login_url' => base_url('login'),
                'profile_url' => base_url('member/profile'),
                'card_url' => base_url('member/card')
            ];

            return $this->sendTemplate($user->email, 'emails/welcome', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in EmailService::sendWelcomeEmail: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengirim welcome email: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Send password reset email
     * Sends password reset link to user
     * 
     * @param object $user User entity or object
     * @param string $token Reset token
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function sendPasswordResetEmail(object $user, string $token): array
    {
        try {
            $resetUrl = base_url("auth/reset-password/{$token}");

            $data = [
                'subject' => 'Reset Password - SPK',
                'user_name' => $user->full_name ?? $user->username ?? 'Member',
                'reset_url' => $resetUrl,
                'token' => $token,
                'expires_in' => '1 jam'
            ];

            return $this->sendTemplate($user->email, 'emails/password_reset', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in EmailService::sendPasswordResetEmail: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengirim email reset password: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Send approval notification email
     * Sends email when member is approved or rejected
     * 
     * @param object $user User entity or object
     * @param bool $approved True if approved, false if rejected
     * @param string $notes Optional notes/reason
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function sendApprovalNotification(object $user, bool $approved, string $notes = ''): array
    {
        try {
            $template = $approved ? 'emails/approval_approved' : 'emails/approval_rejected';
            $subject = $approved ? 'Pendaftaran Anda Disetujui - SPK' : 'Pendaftaran Anda Ditolak - SPK';

            $data = [
                'subject' => $subject,
                'user_name' => $user->full_name ?? $user->username ?? 'Member',
                'approved' => $approved,
                'notes' => $notes,
                'login_url' => base_url('login'),
                'contact_url' => base_url('contact')
            ];

            return $this->sendTemplate($user->email, $template, $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in EmailService::sendApprovalNotification: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengirim email approval: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Send reminder email
     * Sends reminder email to user (payment, renewal, etc)
     * 
     * @param object $user User entity or object
     * @param string $type Reminder type (payment, renewal, etc)
     * @param array $additionalData Additional data for template
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function sendReminderEmail(object $user, string $type, array $additionalData = []): array
    {
        try {
            $templates = [
                'payment' => 'emails/reminder_payment',
                'renewal' => 'emails/reminder_renewal',
                'dues' => 'emails/reminder_dues',
                'general' => 'emails/reminder_general'
            ];

            $subjects = [
                'payment' => 'Pengingat Pembayaran - SPK',
                'renewal' => 'Pengingat Perpanjangan Keanggotaan - SPK',
                'dues' => 'Pengingat Iuran - SPK',
                'general' => 'Pengingat - SPK'
            ];

            $template = $templates[$type] ?? $templates['general'];
            $subject = $subjects[$type] ?? $subjects['general'];

            $data = array_merge([
                'subject' => $subject,
                'user_name' => $user->full_name ?? $user->username ?? 'Member',
                'reminder_type' => $type,
                'payment_url' => base_url('member/payment'),
                'contact_url' => base_url('contact')
            ], $additionalData);

            return $this->sendTemplate($user->email, $template, $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in EmailService::sendReminderEmail: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengirim reminder email: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Send verification email
     * 
     * @param User $user
     * @return array
     */
    public function sendVerificationEmail($user): array
    {
        try {
            // Generate verification token
            $token = bin2hex(random_bytes(32));

            // Store token in database (implement your token storage logic)
            // e.g., save to user_tokens table with expiry time

            // Generate verification link
            $verificationLink = base_url("auth/verify/{$token}");

            // Prepare email data
            $emailData = [
                'fullName' => $user->full_name ?? $user->username,
                'verificationLink' => $verificationLink,
                'contactEmail' => 'admin@spk.org',
                'contactPhone' => '+62 812-3456-7890',
                'facebookUrl' => 'https://facebook.com/spk',
                'twitterUrl' => 'https://twitter.com/spk',
                'instagramUrl' => 'https://instagram.com/spk',
                'linkedinUrl' => 'https://linkedin.com/company/spk',
            ];

            // Load email template
            $message = view('emails/verify_email', $emailData);

            // Send email
            $email = \Config\Services::email();
            $email->setFrom('noreply@spk.org', 'SPK - Serikat Pekerja Kampus');
            $email->setTo($user->email);
            $email->setSubject('Verifikasi Email Anda - SPK');
            $email->setMessage($message);

            if ($email->send()) {
                return [
                    'success' => true,
                    'message' => 'Email verifikasi berhasil dikirim'
                ];
            } else {
                log_message('error', 'Email send failed: ' . $email->printDebugger(['headers']));
                return [
                    'success' => false,
                    'message' => 'Gagal mengirim email'
                ];
            }
        } catch (\Exception $e) {
            log_message('error', 'Error sending verification email: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengirim email'
            ];
        }
    }
}
