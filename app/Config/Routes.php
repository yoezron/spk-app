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
 * Routes for authenticated members (role: anggota)
 */
$routes->group('member', ['namespace' => 'App\Controllers\Member', 'filter' => 'role:anggota,pengurus,superadmin'], function ($routes) {
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

    // Role Management
    $routes->get('roles', 'RoleController::index', ['filter' => 'permission:role.manage']);
    $routes->get('roles/create', 'RoleController::create', ['filter' => 'permission:role.create']);
    $routes->post('roles/store', 'RoleController::store', ['filter' => 'permission:role.create']);
    $routes->get('roles/(:num)/edit', 'RoleController::edit/$1', ['filter' => 'permission:role.edit']);
    $routes->post('roles/(:num)/update', 'RoleController::update/$1', ['filter' => 'permission:role.edit']);
    $routes->delete('roles/(:num)', 'RoleController::delete/$1', ['filter' => 'permission:role.delete']);
    $routes->get('roles/(:num)/permissions', 'RoleController::permissions/$1', ['filter' => 'permission:role.manage']);
    $routes->post('roles/(:num)/permissions/sync', 'RoleController::syncPermissions/$1', ['filter' => 'permission:role.manage']);

    // Permission Management
    $routes->get('permissions', 'PermissionController::index', ['filter' => 'permission:permission.manage']);
    $routes->post('permissions/store', 'PermissionController::store', ['filter' => 'permission:permission.create']);
    $routes->post('permissions/(:num)/update', 'PermissionController::update/$1', ['filter' => 'permission:permission.edit']);
    $routes->delete('permissions/(:num)', 'PermissionController::delete/$1', ['filter' => 'permission:permission.delete']);

    // Menu Management
    $routes->get('menus', 'MenuController::index', ['filter' => 'permission:menu.manage']);
    $routes->post('menus/store', 'MenuController::store', ['filter' => 'permission:menu.create']);
    $routes->post('menus/(:num)/update', 'MenuController::update/$1', ['filter' => 'permission:menu.edit']);
    $routes->delete('menus/(:num)', 'MenuController::delete/$1', ['filter' => 'permission:menu.delete']);
    $routes->post('menus/reorder', 'MenuController::reorder', ['filter' => 'permission:menu.manage']);

    // Master Data Management
    $routes->group('master-data', ['filter' => 'permission:master.manage'], function ($routes) {
        // Provinces
        $routes->get('provinces', 'MasterDataController::provinces');
        $routes->post('provinces/store', 'MasterDataController::storeProvince');
        $routes->post('provinces/(:num)/update', 'MasterDataController::updateProvince/$1');

        // Universities
        $routes->get('universities', 'MasterDataController::universities');
        $routes->post('universities/store', 'MasterDataController::storeUniversity');
        $routes->post('universities/(:num)/update', 'MasterDataController::updateUniversity/$1');
        $routes->delete('universities/(:num)', 'MasterDataController::deleteUniversity/$1');
        $routes->post('universities/bulk-upload', 'MasterDataController::bulkUploadUniversities');

        // Study Programs
        $routes->get('study-programs', 'MasterDataController::studyPrograms');
        $routes->post('study-programs/store', 'MasterDataController::storeStudyProgram');
        $routes->post('study-programs/(:num)/update', 'MasterDataController::updateStudyProgram/$1');
        $routes->delete('study-programs/(:num)', 'MasterDataController::deleteStudyProgram/$1');

        // Employment Status
        $routes->get('employment-statuses', 'MasterDataController::employmentStatuses');
        $routes->post('employment-statuses/store', 'MasterDataController::storeEmploymentStatus');
        $routes->post('employment-statuses/(:num)/update', 'MasterDataController::updateEmploymentStatus/$1');

        // Salary Ranges
        $routes->get('salary-ranges', 'MasterDataController::salaryRanges');
        $routes->post('salary-ranges/store', 'MasterDataController::storeSalaryRange');
        $routes->post('salary-ranges/(:num)/update', 'MasterDataController::updateSalaryRange/$1');
    });

    // System Settings
    $routes->get('settings', 'SettingsController::index', ['filter' => 'permission:settings.manage']);
    $routes->post('settings/update', 'SettingsController::update', ['filter' => 'permission:settings.manage']);
    $routes->get('settings/cache/clear', 'SettingsController::clearCache', ['filter' => 'permission:settings.manage']);

    // Audit Logs
    $routes->get('audit-logs', 'AuditLogController::index', ['filter' => 'permission:audit.view']);
    $routes->get('audit-logs/(:num)', 'AuditLogController::show/$1', ['filter' => 'permission:audit.view']);
    $routes->get('audit-logs/export', 'AuditLogController::export', ['filter' => 'permission:audit.export']);
});

// Super Admin Routes
$routes->group('super', ['filter' => 'group:superadmin'], function ($routes) {
    $routes->get('dashboard', 'Super\DashboardController::index');
    $routes->post('dashboard/refresh', 'Super\DashboardController::refresh');
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
$routes->set404Override(function () {
    return view('errors/html/error_404');
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