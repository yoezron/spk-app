<?php

namespace App\Controllers\Member;

use App\Controllers\BaseController;
use App\Services\Communication\NotificationService;
use App\Services\Member\MemberStatisticsService;
use CodeIgniter\HTTP\ResponseInterface;


/**
 * DashboardController (Member Area)
 * 
 * Menangani dashboard untuk member yang sudah login
 * Menampilkan statistik personal, notifikasi, dan aktivitas terkini
 * 
 * @package App\Controllers\Member
 * @author  SPK Development Team
 * @version 1.0.0
 */
class DashboardController extends BaseController
{
    /**
     * @var MemberStatisticsService
     */
    protected $statisticsService;

    /**
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->statisticsService = new MemberStatisticsService();
        $this->notificationService = new NotificationService();
    }

    /**
     * Display member dashboard
     * Shows personal info, statistics, notifications, and quick actions
   
     */
    public function index(): ResponseInterface|string
    {
        // Check if user is logged in
        if (!auth()->loggedIn()) {
            return redirect()->to('/login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();

        try {
            // Get member profile
            $memberModel = model('MemberProfileModel');
            $member = $memberModel->where('user_id', $user->id)->first();

            if (!$member) {
                return redirect()->to('/login')
                    ->with('error', 'Profil anggota tidak ditemukan.');
            }

            // Check if user is calon anggota (pending member)
            $isCalonAnggota = $user->inGroup('calon_anggota') || $user->inGroup('Calon Anggota');

            // If calon anggota, show limited dashboard with status
            if ($isCalonAnggota) {
                $data = [
                    'title' => 'Status Pendaftaran - Serikat Pekerja Kampus',
                    'pageTitle' => 'Status Pendaftaran',
                    'user' => $user,
                    'member' => $member,
                    'membershipStatus' => $member->membership_status ?? 'pending'
                ];
                return view('member/dashboard_pending', $data);
            }

            // Get personal statistics
            $personalStats = $this->getPersonalStatistics($user->id);

            // Get recent notifications (unread + 5 latest read)
            $notifications = $this->notificationService->getUserNotifications($user->id, [
                'limit' => 10,
                'include_read' => true
            ]);

            // Get unread notification count
            $unreadCount = $this->notificationService->getUnreadCount($user->id);

            // Get recent activities (forum posts, surveys, complaints)
            $recentActivities = $this->getRecentActivities($user->id);

            // Get upcoming events (if any)
            $upcomingEvents = $this->getUpcomingEvents();

            // Check account status and warnings
            $accountWarnings = $this->checkAccountWarnings($user, $member);

            $data = [
                'title' => 'Dashboard - Serikat Pekerja Kampus',
                'pageTitle' => 'Dashboard',

                // User & Member Info
                'user' => $user,
                'member' => $member,

                // Statistics
                'personalStats' => $personalStats,

                // Notifications
                'notifications' => $notifications['data'] ?? [],
                'unreadCount' => $unreadCount['count'] ?? 0,

                // Activities
                'recentActivities' => $recentActivities,
                'upcomingEvents' => $upcomingEvents,

                // Warnings
                'accountWarnings' => $accountWarnings,

                // Quick Stats Cards
                'quickStats' => [
                    'forum_posts' => $personalStats['forum_posts_count'] ?? 0,
                    'surveys_completed' => $personalStats['surveys_completed'] ?? 0,
                    'tickets_open' => $personalStats['tickets_open'] ?? 0,
                    'member_since_days' => $personalStats['member_since_days'] ?? 0
                ]
            ];

            return view('member/dashboard', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading member dashboard: ' . $e->getMessage());

            return view('member/dashboard', [
                'title' => 'Dashboard',
                'pageTitle' => 'Dashboard',
                'user' => $user,
                'member' => null,
                'personalStats' => [],
                'notifications' => [],
                'unreadCount' => 0,
                'recentActivities' => [],
                'upcomingEvents' => [],
                'accountWarnings' => [],
                'quickStats' => []
            ]);
        }
    }

    /**
     * Get personal statistics for member
     * 
     * @param int $userId User ID
     * @return array
     */
    protected function getPersonalStatistics(int $userId): array
    {
        try {
            $stats = [];

            // Get member profile
            $memberModel = model('MemberProfileModel');
            $member = $memberModel->where('user_id', $userId)->first();

            if (!$member) {
                return $stats;
            }

            // Member since (days)
            if ($member->join_date) {
                $joinDate = strtotime($member->join_date);
                $today = time();
                $stats['member_since_days'] = floor(($today - $joinDate) / (60 * 60 * 24));
            } else {
                $stats['member_since_days'] = 0;
            }

            // Forum posts count
            try {
                $forumPostModel = model('ForumPostModel');
                $stats['forum_posts_count'] = $forumPostModel ? $forumPostModel->where('user_id', $userId)->countAllResults() : 0;
            } catch (\Exception $e) {
                $stats['forum_posts_count'] = 0;
            }

            // Forum threads created
            try {
                $forumThreadModel = model('ForumThreadModel');
                $stats['forum_threads_count'] = $forumThreadModel ? $forumThreadModel->where('created_by', $userId)->countAllResults() : 0;
            } catch (\Exception $e) {
                $stats['forum_threads_count'] = 0;
            }

            // Surveys completed
            try {
                $surveyResponseModel = model('SurveyResponseModel');
                $stats['surveys_completed'] = $surveyResponseModel ? $surveyResponseModel->where('user_id', $userId)->countAllResults() : 0;
            } catch (\Exception $e) {
                $stats['surveys_completed'] = 0;
            }

            // Complaints/tickets
            try {
                $complaintModel = model('ComplaintModel');
                if ($complaintModel) {
                    $stats['tickets_total'] = $complaintModel->where('user_id', $userId)->countAllResults();
                    $stats['tickets_open'] = $complaintModel->where('user_id', $userId)->whereIn('status', ['open', 'in_progress'])->countAllResults();
                    $stats['tickets_closed'] = $complaintModel->where('user_id', $userId)->where('status', 'closed')->countAllResults();
                } else {
                    $stats['tickets_total'] = 0;
                    $stats['tickets_open'] = 0;
                    $stats['tickets_closed'] = 0;
                }
            } catch (\Exception $e) {
                $stats['tickets_total'] = 0;
                $stats['tickets_open'] = 0;
                $stats['tickets_closed'] = 0;
            }

            return $stats;
        } catch (\Exception $e) {
            log_message('error', 'Error getting personal statistics: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent activities for member
     * 
     * @param int $userId User ID
     * @return array
     */
    protected function getRecentActivities(int $userId): array
    {
        try {
            $activities = [];

            // Get recent forum posts (last 5)
            try {
                $forumPostModel = model('ForumPostModel');
                if ($forumPostModel) {
                    $recentPosts = $forumPostModel->select('forum_posts.*, forum_threads.title as thread_title')
                        ->join('forum_threads', 'forum_threads.id = forum_posts.thread_id')
                        ->where('forum_posts.user_id', $userId)
                        ->orderBy('forum_posts.created_at', 'DESC')
                        ->findAll(5);

                    foreach ($recentPosts as $post) {
                        $activities[] = [
                            'type' => 'forum_post',
                            'icon' => 'message-square',
                            'title' => 'Membalas thread: ' . $post->thread_title,
                            'time' => $post->created_at,
                            'url' => base_url('member/forum/thread/' . $post->thread_id)
                        ];
                    }
                }
            } catch (\Exception $e) {
                log_message('error', 'Error getting forum posts: ' . $e->getMessage());
            }

            // Get recent survey responses (last 3)
            try {
                $surveyResponseModel = model('SurveyResponseModel');
                if ($surveyResponseModel) {
                    $recentSurveys = $surveyResponseModel->select('survey_responses.*, surveys.title as survey_title')
                        ->join('surveys', 'surveys.id = survey_responses.survey_id')
                        ->where('survey_responses.user_id', $userId)
                        ->orderBy('survey_responses.created_at', 'DESC')
                        ->findAll(3);

                    foreach ($recentSurveys as $response) {
                        $activities[] = [
                            'type' => 'survey',
                            'icon' => 'clipboard-check',
                            'title' => 'Mengisi survei: ' . $response->survey_title,
                            'time' => $response->created_at,
                            'url' => base_url('member/surveys/' . $response->survey_id)
                        ];
                    }
                }
            } catch (\Exception $e) {
                log_message('error', 'Error getting survey responses: ' . $e->getMessage());
            }

            // Get recent tickets (last 3)
            try {
                $complaintModel = model('ComplaintModel');
                if ($complaintModel) {
                    $recentTickets = $complaintModel->where('user_id', $userId)
                        ->orderBy('created_at', 'DESC')
                        ->findAll(3);

                    foreach ($recentTickets as $ticket) {
                        $activities[] = [
                            'type' => 'ticket',
                            'icon' => 'alert-circle',
                            'title' => 'Ticket: ' . $ticket->subject,
                            'time' => $ticket->created_at,
                            'url' => base_url('member/complaints/' . $ticket->id),
                            'status' => $ticket->status
                        ];
                    }
                }
            } catch (\Exception $e) {
                log_message('error', 'Error getting recent tickets: ' . $e->getMessage());
            }

            // Sort by time (most recent first)
            if (!empty($activities)) {
                usort($activities, function ($a, $b) {
                    return strtotime($b['time']) - strtotime($a['time']);
                });

                // Return only top 10
                return array_slice($activities, 0, 10);
            }

            return [];
        } catch (\Exception $e) {
            log_message('error', 'Error getting recent activities: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get upcoming events
     * 
     * @return array
     */
    protected function getUpcomingEvents(): array
    {
        try {
            // TODO: Implement events system if needed
            // For now, return empty array
            return [];
        } catch (\Exception $e) {
            log_message('error', 'Error getting upcoming events: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Check account warnings
     * Returns array of warnings/alerts for the user
     * 
     * @param object $user User entity
     * @param object $member Member entity
     * @return array
     */
    protected function checkAccountWarnings($user, $member): array
    {
        $warnings = [];

        try {
            // Check if email is not verified
            if (!$user->email_verified_at) {
                $warnings[] = [
                    'type' => 'warning',
                    'icon' => 'mail',
                    'message' => 'Email Anda belum diverifikasi. Silakan cek inbox untuk verifikasi.',
                    'action_text' => 'Kirim Ulang Email',
                    'action_url' => base_url('verify-email/resend')
                ];
            }

            // Check if profile is incomplete
            if ($this->isProfileIncomplete($member)) {
                $warnings[] = [
                    'type' => 'info',
                    'icon' => 'user',
                    'message' => 'Profil Anda belum lengkap. Lengkapi profil untuk mendapatkan manfaat penuh.',
                    'action_text' => 'Lengkapi Profil',
                    'action_url' => base_url('member/profile/edit')
                ];
            }

            // Check if membership is pending
            if ($user->inGroup('Calon Anggota')) {
                $warnings[] = [
                    'type' => 'info',
                    'icon' => 'clock',
                    'message' => 'Akun Anda masih menunggu verifikasi dari pengurus.',
                    'action_text' => null,
                    'action_url' => null
                ];
            }

            // Check if card is expiring soon (within 30 days)
            if ($member->join_date) {
                $expirationDate = strtotime($member->join_date . ' + 3 years');
                $today = time();
                $daysUntilExpiration = floor(($expirationDate - $today) / (60 * 60 * 24));

                if ($daysUntilExpiration > 0 && $daysUntilExpiration <= 30) {
                    $warnings[] = [
                        'type' => 'warning',
                        'icon' => 'credit-card',
                        'message' => 'Kartu anggota Anda akan kadaluarsa dalam ' . $daysUntilExpiration . ' hari.',
                        'action_text' => 'Perpanjang Sekarang',
                        'action_url' => base_url('member/card/renew')
                    ];
                } elseif ($daysUntilExpiration <= 0) {
                    $warnings[] = [
                        'type' => 'danger',
                        'icon' => 'credit-card',
                        'message' => 'Kartu anggota Anda sudah kadaluarsa. Segera perpanjang.',
                        'action_text' => 'Perpanjang Sekarang',
                        'action_url' => base_url('member/card/renew')
                    ];
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Error checking account warnings: ' . $e->getMessage());
        }

        return $warnings;
    }

    /**
     * Check if profile is incomplete
     * 
     * @param object $member Member entity
     * @return bool
     */
    protected function isProfileIncomplete($member): bool
    {
        // Check required fields
        $requiredFields = [
            'full_name',
            'gender',
            'address',
            'whatsapp',
            'province_id',
            'university_id',
            'study_program_id'
        ];

        foreach ($requiredFields as $field) {
            if (empty($member->$field)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get personal statistics (AJAX endpoint)
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getStats()
    {
        if (!auth()->loggedIn()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Not authenticated'
            ]);
        }

        try {
            $userId = auth()->id();
            $stats = $this->getPersonalStatistics($userId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting stats: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error getting statistics'
            ]);
        }
    }

    /**
     * Mark notification as read
     * 
     * @param int $notificationId Notification ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function markNotificationRead(int $notificationId)
    {
        if (!auth()->loggedIn()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Not authenticated'
            ]);
        }

        try {
            $result = $this->notificationService->markAsRead($notificationId, auth()->id());

            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            log_message('error', 'Error marking notification as read: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error updating notification'
            ]);
        }
    }

    /**
     * Mark all notifications as read
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function markAllNotificationsRead()
    {
        if (!auth()->loggedIn()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Not authenticated'
            ]);
        }

        try {
            $result = $this->notificationService->markAllAsRead(auth()->id());

            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            log_message('error', 'Error marking all notifications as read: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error updating notifications'
            ]);
        }
    }
}
