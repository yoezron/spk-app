<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// Default controller
$routes->get('/', 'Public\HomeController::index');

// Convenience route for legacy logout URL
$routes->get('logout', 'Auth\\LoginController::logout');

/*
 * --------------------------------------------------------------------
 * Public Routes
 * --------------------------------------------------------------------
 * Routes accessible to anyone (no authentication required)
 */
$routes->group('', ['namespace' => 'App\Controllers\Public'], function ($routes) {
    // Homepage & Static Pages
    $routes->get('about', 'HomeController::about');
    $routes->get('contact', 'HomeController::contact');
    $routes->post('contact/send', 'HomeController::sendContact');
    $routes->get('pages/(:segment)', 'HomeController::page/$1');

    // Blog & News
    $routes->get('blog', 'BlogController::index');
    $routes->get('blog/(:segment)', 'BlogController::show/$1');
    $routes->get('blog/category/(:segment)', 'BlogController::category/$1');
    $routes->get('blog/tag/(:segment)', 'BlogController::tag/$1');

    // Organization Structure
    $routes->get('struktur-organisasi', 'OrgStructureController::index');
    $routes->get('struktur-organisasi/(:num)', 'OrgStructureController::show/$1');

    // Public Card Verification
    $routes->get('verify/(:segment)', 'VerifyCardController::verify/$1');
    $routes->get('v/(:segment)', 'VerifyCardController::verify/$1'); // Short URL
});

/*
 * --------------------------------------------------------------------
 * Authentication Routes
 * --------------------------------------------------------------------
 * Login, Register, Password Reset, Email Verification
 */

// Friendly root-level aliases for authentication pages
// Friendly authentication endpoints without the /auth prefix
$routes->group('', ['namespace' => 'App\Controllers\Auth'], function ($routes) {
    // Login shortcuts
    $routes->get('login', 'LoginController::index', ['as' => 'login.short']);
    $routes->post('login', 'LoginController::attempt');

    // Register shortcuts
    $routes->get('register', 'RegisterController::index', ['as' => 'register.short']);
    $routes->post('register', 'RegisterController::register');
});


$routes->group('auth', ['namespace' => 'App\Controllers\Auth'], function ($routes) {
    // Login
    $routes->get('login', 'LoginController::index', ['as' => 'login']);
    $routes->post('login', 'LoginController::attempt');
    $routes->get('logout', 'LoginController::logout', ['as' => 'logout']);

    // Register
    $routes->get('register', 'RegisterController::index', ['as' => 'register']);
    $routes->post('register', 'RegisterController::register');
    $routes->get('verify-email', 'RegisterController::verifyEmailPage'); // Success page after registration

    // Email Verification
    $routes->get('verify/(:segment)', 'VerifyController::verify/$1', ['as' => 'verify']);
    $routes->post('resend-verification', 'VerifyController::resendVerification');

    // Password Reset
    $routes->get('forgot-password', 'PasswordController::forgotPassword');
    $routes->post('forgot-password', 'PasswordController::sendResetLink');
    $routes->get('reset-password/(:segment)', 'PasswordController::resetPassword/$1');
    $routes->post('reset-password', 'PasswordController::updatePassword');
});

/*
 * --------------------------------------------------------------------
 * Member Routes
 * --------------------------------------------------------------------
 * Routes for authenticated members (role: anggota, calon anggota, pengurus, superadmin)
 */
$routes->group('member', ['namespace' => 'App\Controllers\Member', 'filter' => 'role:anggota,calon anggota,pengurus,superadmin'], function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'DashboardController::index', ['as' => 'member.dashboard']);

    // Profile Management
    $routes->get('profile', 'ProfileController::index', ['as' => 'member.profile']);
    $routes->get('profile/edit', 'ProfileController::edit');
    $routes->post('profile/update', 'ProfileController::update');
    $routes->post('profile/upload-photo', 'ProfileController::uploadPhoto');
    $routes->post('profile/change-password', 'ProfileController::changePassword');

    // Member Card
    $routes->get('card', 'CardController::index', ['as' => 'member.card']);
    $routes->get('card/download', 'CardController::download');
    $routes->get('card/preview', 'CardController::preview');

    // Payment & Dues
    $routes->get('payment', 'PaymentController::index', ['as' => 'member.payment']);
    $routes->get('payment/history', 'PaymentController::history');
    $routes->post('payment/upload', 'PaymentController::uploadProof');

    // Forum
    $routes->get('forum', 'ForumController::index', ['as' => 'member.forum']);
    $routes->get('forum/(:num)', 'ForumController::show/$1');
    $routes->get('forum/create', 'ForumController::create');
    $routes->post('forum/store', 'ForumController::store');
    $routes->post('forum/(:num)/reply', 'ForumController::reply/$1');
    $routes->post('forum/(:num)/like', 'ForumController::like/$1');
    $routes->post('forum/comment/(:num)/like', 'ForumController::likeComment/$1');

    // Survey
    $routes->get('survey', 'SurveyController::index', ['as' => 'member.survey']);
    $routes->get('survey/(:num)', 'SurveyController::show/$1');
    $routes->get('survey/(:num)/participate', 'SurveyController::participate/$1');
    $routes->post('survey/(:num)/submit', 'SurveyController::submit/$1');

    // Complaints/Tickets
    $routes->get('complaint', 'ComplaintController::index', ['as' => 'member.complaint']);
    $routes->get('complaint/create', 'ComplaintController::create');
    $routes->post('complaint/store', 'ComplaintController::store');
    $routes->get('complaint/(:num)', 'ComplaintController::show/$1');
    $routes->post('complaint/(:num)/reply', 'ComplaintController::reply/$1');
});

/*
 * --------------------------------------------------------------------
 * Admin Routes
 * --------------------------------------------------------------------
 * Routes for administrators (role: pengurus)
 */
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'role:pengurus,superadmin'], function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'DashboardController::index', ['as' => 'admin.dashboard']);

    // Member Management
    $routes->get('members', 'MemberController::index', ['filter' => 'permission:member.view']);
    $routes->get('members/pending', 'MemberController::pending', ['filter' => 'permission:member.approve']);
    $routes->get('members/(:num)', 'MemberController::show/$1', ['filter' => 'permission:member.view']);
    $routes->get('members/(:num)/edit', 'MemberController::edit/$1', ['filter' => 'permission:member.edit']);
    $routes->post('members/(:num)/update', 'MemberController::update/$1', ['filter' => 'permission:member.edit']);
    $routes->post('members/(:num)/approve', 'MemberController::approve/$1', ['filter' => 'permission:member.approve']);
    $routes->post('members/(:num)/reject', 'MemberController::reject/$1', ['filter' => 'permission:member.approve']);
    $routes->post('members/(:num)/suspend', 'MemberController::suspend/$1', ['filter' => 'permission:member.suspend']);
    $routes->post('members/(:num)/activate', 'MemberController::activate/$1', ['filter' => 'permission:member.approve']);
    $routes->delete('members/(:num)', 'MemberController::delete/$1', ['filter' => 'permission:member.delete']);
    $routes->get('members/export/excel', 'MemberController::exportExcel', ['filter' => 'permission:member.export']);
    $routes->get('members/export/pdf', 'MemberController::exportPdf', ['filter' => 'permission:member.export']);

    // Bulk Import
    $routes->get('bulk-import', 'BulkImportController::index', ['filter' => 'permission:member.import']);
    $routes->post('bulk-import/upload', 'BulkImportController::upload', ['filter' => 'permission:member.import']);
    $routes->get('bulk-import/preview', 'BulkImportController::preview', ['filter' => 'permission:member.import']);
    $routes->post('bulk-import/process', 'BulkImportController::process', ['filter' => 'permission:member.import']);
    $routes->get('bulk-import/download-template', 'BulkImportController::downloadTemplate');

    // Statistics
    $routes->get('statistics', 'StatisticsController::index', ['filter' => 'permission:stats.view']);
    $routes->get('statistics/export', 'StatisticsController::export', ['filter' => 'permission:stats.export']);

    // Forum Management
    $routes->get('forum', 'ForumController::index', ['filter' => 'permission:forum.moderate']);
    $routes->post('forum/(:num)/lock', 'ForumController::lock/$1', ['filter' => 'permission:forum.moderate']);
    $routes->post('forum/(:num)/unlock', 'ForumController::unlock/$1', ['filter' => 'permission:forum.moderate']);
    $routes->post('forum/(:num)/pin', 'ForumController::pin/$1', ['filter' => 'permission:forum.moderate']);
    $routes->post('forum/(:num)/unpin', 'ForumController::unpin/$1', ['filter' => 'permission:forum.moderate']);
    $routes->delete('forum/(:num)', 'ForumController::delete/$1', ['filter' => 'permission:forum.moderate']);
    $routes->delete('forum/comment/(:num)', 'ForumController::deleteComment/$1', ['filter' => 'permission:forum.moderate']);

    // Survey Management
    $routes->get('survey', 'SurveyController::index', ['filter' => 'permission:survey.manage']);
    $routes->get('survey/create', 'SurveyController::create', ['filter' => 'permission:survey.create']);
    $routes->post('survey/store', 'SurveyController::store', ['filter' => 'permission:survey.create']);
    $routes->get('survey/(:num)/edit', 'SurveyController::edit/$1', ['filter' => 'permission:survey.edit']);
    $routes->post('survey/(:num)/update', 'SurveyController::update/$1', ['filter' => 'permission:survey.edit']);
    $routes->delete('survey/(:num)', 'SurveyController::delete/$1', ['filter' => 'permission:survey.delete']);
    $routes->get('survey/(:num)/responses', 'SurveyController::responses/$1', ['filter' => 'permission:survey.view']);
    $routes->get('survey/(:num)/export', 'SurveyController::export/$1', ['filter' => 'permission:survey.export']);
    $routes->post('survey/(:num)/publish', 'SurveyController::publish/$1', ['filter' => 'permission:survey.manage']);
    $routes->post('survey/(:num)/close', 'SurveyController::close/$1', ['filter' => 'permission:survey.manage']);

    // Complaint/Ticket Management
    $routes->get('complaints', 'ComplaintController::index', ['filter' => 'permission:complaint.manage']);
    $routes->get('complaints/(:num)', 'ComplaintController::show/$1', ['filter' => 'permission:complaint.view']);
    $routes->post('complaints/(:num)/assign', 'ComplaintController::assign/$1', ['filter' => 'permission:complaint.manage']);
    $routes->post('complaints/(:num)/reply', 'ComplaintController::reply/$1', ['filter' => 'permission:complaint.reply']);
    $routes->post('complaints/(:num)/resolve', 'ComplaintController::resolve/$1', ['filter' => 'permission:complaint.manage']);
    $routes->post('complaints/(:num)/close', 'ComplaintController::close/$1', ['filter' => 'permission:complaint.manage']);
    $routes->post('complaints/(:num)/reopen', 'ComplaintController::reopen/$1', ['filter' => 'permission:complaint.manage']);

    // Content Management
    $routes->group('content', ['filter' => 'permission:content.manage'], function ($routes) {
        // Posts/Blog
        $routes->get('posts', 'ContentController::posts');
        $routes->get('posts/create', 'ContentController::createPost');
        $routes->post('posts/store', 'ContentController::storePost');
        $routes->get('posts/(:num)/edit', 'ContentController::editPost/$1');
        $routes->post('posts/(:num)/update', 'ContentController::updatePost/$1');
        $routes->delete('posts/(:num)', 'ContentController::deletePost/$1');
        $routes->post('posts/(:num)/publish', 'ContentController::publishPost/$1');

        // Pages
        $routes->get('pages', 'ContentController::pages');
        $routes->get('pages/create', 'ContentController::createPage');
        $routes->post('pages/store', 'ContentController::storePage');
        $routes->get('pages/(:num)/edit', 'ContentController::editPage/$1');
        $routes->post('pages/(:num)/update', 'ContentController::updatePage/$1');
        $routes->delete('pages/(:num)', 'ContentController::deletePage/$1');

        // Categories
        $routes->get('categories', 'ContentController::categories');
        $routes->post('categories/store', 'ContentController::storeCategory');
        $routes->post('categories/(:num)/update', 'ContentController::updateCategory/$1');
        $routes->delete('categories/(:num)', 'ContentController::deleteCategory/$1');
    });

    // Payment Management
    $routes->get('payments', 'PaymentController::index', ['filter' => 'permission:payment.manage']);
    $routes->get('payments/pending', 'PaymentController::pending', ['filter' => 'permission:payment.verify']);
    $routes->post('payments/(:num)/verify', 'PaymentController::verify/$1', ['filter' => 'permission:payment.verify']);
    $routes->post('payments/(:num)/reject', 'PaymentController::reject/$1', ['filter' => 'permission:payment.verify']);
    $routes->get('payments/export', 'PaymentController::export', ['filter' => 'permission:payment.export']);

    // WhatsApp Groups
    $routes->get('wa-groups', 'WAGroupController::index', ['filter' => 'permission:wagroup.manage']);
    $routes->post('wa-groups/store', 'WAGroupController::store', ['filter' => 'permission:wagroup.create']);
    $routes->post('wa-groups/(:num)/update', 'WAGroupController::update/$1', ['filter' => 'permission:wagroup.edit']);
    $routes->delete('wa-groups/(:num)', 'WAGroupController::delete/$1', ['filter' => 'permission:wagroup.delete']);
    $routes->get('wa-groups/(:num)/members', 'WAGroupController::members/$1', ['filter' => 'permission:wagroup.view']);

    // Organization Structure
    $routes->get('org-structure', 'OrgStructureController::index', ['filter' => 'permission:org.manage']);
    $routes->post('org-structure/unit/store', 'OrgStructureController::storeUnit', ['filter' => 'permission:org.create']);
    $routes->post('org-structure/position/store', 'OrgStructureController::storePosition', ['filter' => 'permission:org.create']);
    $routes->post('org-structure/assignment/store', 'OrgStructureController::storeAssignment', ['filter' => 'permission:org.assign']);
    $routes->delete('org-structure/assignment/(:num)', 'OrgStructureController::deleteAssignment/$1', ['filter' => 'permission:org.assign']);
});

/*
 * --------------------------------------------------------------------
 * Super Admin Routes
 * --------------------------------------------------------------------
 * Routes for super administrators only
 */
$routes->group('super', ['namespace' => 'App\Controllers\Super', 'filter' => 'role:superadmin'], function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'DashboardController::index', ['as' => 'super.dashboard']);
    $routes->post('dashboard/refresh', 'DashboardController::refresh');

    // Role Management - HAPUS semua filter permission
    $routes->get('roles', 'RoleController::index');
    $routes->get('roles/create', 'RoleController::create');
    $routes->post('roles/store', 'RoleController::store');
    $routes->get('roles/(:num)/edit', 'RoleController::edit/$1');
    $routes->post('roles/(:num)/update', 'RoleController::update/$1');
    $routes->post('roles/(:num)/delete', 'RoleController::delete/$1');
    $routes->get('roles/(:num)/members', 'RoleController::members/$1');
    $routes->get('roles/matrix', 'RoleController::matrix');

    // Permission Management - HAPUS semua filter
    $routes->get('permissions', 'PermissionController::index');
    $routes->get('permissions/create', 'PermissionController::create');
    $routes->post('permissions/store', 'PermissionController::store');
    $routes->get('permissions/(:num)/edit', 'PermissionController::edit/$1');
    $routes->post('permissions/(:num)/update', 'PermissionController::update/$1');
    $routes->post('permissions/(:num)/delete', 'PermissionController::delete/$1');
    $routes->get('permissions/(:num)/roles', 'PermissionController::roles/$1');
    $routes->get('permissions/sync', 'PermissionController::syncToShield'); // ← Ubah dari POST ke GET

    // Menu Management - HAPUS semua filter
    $routes->get('menus', 'MenuController::index');
    $routes->get('menus/create', 'MenuController::create');
    $routes->post('menus/store', 'MenuController::store');
    $routes->get('menus/(:num)/edit', 'MenuController::edit/$1');
    $routes->post('menus/(:num)/update', 'MenuController::update/$1');
    $routes->post('menus/(:num)/delete', 'MenuController::delete/$1');
    $routes->post('menus/reorder', 'MenuController::reorder');
    $routes->get('menus/preview', 'MenuController::preview');
    $routes->get('menus/preview/(:segment)', 'MenuController::preview/$1');

    // Master Data Management - HAPUS semua filter
    $routes->group('master-data', function ($routes) {
        // Provinces
        $routes->get('provinces', 'MasterDataController::provinces');
        $routes->post('provinces/store', 'MasterDataController::storeProvince');
        $routes->post('provinces/(:num)/update', 'MasterDataController::updateProvince/$1');
        $routes->post('provinces/(:num)/delete', 'MasterDataController::deleteProvince/$1');

        // Regencies
        $routes->get('regencies', 'MasterDataController::regencies');
        $routes->post('regencies/store', 'MasterDataController::storeRegency');
        $routes->post('regencies/(:num)/update', 'MasterDataController::updateRegency/$1');
        $routes->post('regencies/(:num)/delete', 'MasterDataController::deleteRegency/$1');

        // Universities
        $routes->get('universities', 'MasterDataController::universities');
        $routes->post('universities/store', 'MasterDataController::storeUniversity');
        $routes->post('universities/(:num)/update', 'MasterDataController::updateUniversity/$1');
        $routes->post('universities/(:num)/delete', 'MasterDataController::deleteUniversity/$1');

        // Study Programs
        $routes->get('study-programs', 'MasterDataController::studyPrograms');
        $routes->post('study-programs/store', 'MasterDataController::storeStudyProgram');
        $routes->post('study-programs/(:num)/update', 'MasterDataController::updateStudyProgram/$1');
        $routes->post('study-programs/(:num)/delete', 'MasterDataController::deleteStudyProgram/$1');

        // Export
        $routes->get('export/(:segment)', 'MasterDataController::export/$1');
    });

    // System Settings
    $routes->get('settings', 'SettingsController::index');
    $routes->post('settings/update', 'SettingsController::update');
    $routes->get('settings/cache/clear', 'SettingsController::clearCache');

    // Audit Logs
    $routes->get('audit-logs', 'AuditLogController::index');
    $routes->get('audit-logs/(:num)', 'AuditLogController::show/$1');
    $routes->get('audit-logs/export', 'AuditLogController::export');
});

/*
 * --------------------------------------------------------------------
 * API Routes
 * --------------------------------------------------------------------
 * RESTful API endpoints for AJAX requests
 */
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    // Master Data Endpoints
    $routes->get('provinces', 'MasterDataController::getProvinces');
    $routes->get('regencies', 'MasterDataController::getRegencies');
    $routes->get('districts', 'MasterDataController::getDistricts');
    $routes->get('villages', 'MasterDataController::getVillages');
    $routes->get('universities', 'MasterDataController::getUniversities');
    $routes->get('study-programs', 'MasterDataController::getStudyPrograms');
    $routes->get('universities/search', 'MasterDataController::searchUniversities');

    // Cache Management (Admin only)
    $routes->get('cache/clear', 'MasterDataController::clearCache', ['filter' => 'permission:master.manage']);

    // Member API
    $routes->group('members', ['filter' => 'permission:member.view'], function ($routes) {
        $routes->get('search', 'MemberController::search');
        $routes->get('statistics', 'MemberController::statistics');
    });

    // Dashboard API
    $routes->get('dashboard/stats', 'DashboardController::getStats', ['filter' => 'role:pengurus,superadmin']);
    $routes->get('dashboard/charts', 'DashboardController::getCharts', ['filter' => 'role:pengurus,superadmin']);
});

/*
 * --------------------------------------------------------------------
 * Service Worker & PWA Routes
 * --------------------------------------------------------------------
 */
$routes->get('service-worker.js', function () {
    return view('pwa/service-worker');
});
$routes->get('manifest.json', function () {
    return view('pwa/manifest');
});

/*
 * --------------------------------------------------------------------
 * Error Routes
 * --------------------------------------------------------------------
*/
$routes->set404Override(function (string $message) {
    return view('errors/html/error_404', ['message' => $message]);
});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 */

// If you have additional routes, add them here

/*
 * --------------------------------------------------------------------
 * Route Priority
 * --------------------------------------------------------------------
 * Higher priority routes should be defined first
 * More specific routes before general ones
 */