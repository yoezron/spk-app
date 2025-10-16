<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// --------------------------------------------------------------------
// Default Route Configuration
// --------------------------------------------------------------------
$routes->setDefaultNamespace('App\\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

// --------------------------------------------------------------------
// Landing Page
// --------------------------------------------------------------------
$routes->get('/', 'Public\\HomeController::index');

// --------------------------------------------------------------------
// Authentication Routes
// --------------------------------------------------------------------
$authRoutes = static function (RouteCollection $routes): void {
    // Login & logout
    $routes->get('login', 'LoginController::index');
    $routes->post('login', 'LoginController::attempt');
    $routes->get('logout', 'LoginController::logout');

    // Registration
    $routes->get('register', 'RegisterController::index');
    $routes->post('register', 'RegisterController::store');
    $routes->get('register/kabupaten', 'RegisterController::getKabupaten');
    $routes->get('register/kampus', 'RegisterController::getKampus');
    $routes->get('register/prodi', 'RegisterController::getProdi');

    // Email verification
    $routes->get('verify-email', 'VerifyController::index');
    $routes->get('verify-email/status', 'VerifyController::checkStatus');
    $routes->get('verify-email/(:segment)', 'VerifyController::verify/$1');
    $routes->post('verify-email/resend', 'VerifyController::resend');

    // Legacy aliases
    $routes->get('verify', 'VerifyController::index');
    $routes->get('verify/check-status', 'VerifyController::checkStatus');
    $routes->post('verify/resend', 'VerifyController::resend');
};

$routes->group('', ['namespace' => 'App\\Controllers\\Auth'], $authRoutes);
$routes->group('auth', ['namespace' => 'App\\Controllers\\Auth'], $authRoutes);

// --------------------------------------------------------------------
// Public Routes
// --------------------------------------------------------------------
$routes->group('', ['namespace' => 'App\\Controllers\\Public'], static function (RouteCollection $routes): void {
    // Informational pages
    $routes->get('home', 'HomeController::index');
    $routes->get('about', 'HomeController::about');
    $routes->get('contact', 'HomeController::contact');
    $routes->post('contact', 'HomeController::submitContact');
    $routes->get('manifesto', 'HomeController::manifesto');
    $routes->get('adart', 'HomeController::adart');

    // Verify member card
    $routes->get('verify-card', 'VerifyCardController::index');
    $routes->post('verify-card', 'VerifyCardController::verify');
    $routes->get('verify-card/verify', 'VerifyCardController::verify');
    $routes->match(['get', 'post'], 'verify-card/quick', 'VerifyCardController::quickVerify');
    $routes->get('verify-card/statistics', 'VerifyCardController::statistics', ['filter' => 'permission:member.manage']);
    $routes->get('verify/(:segment)', 'VerifyCardController::verify/$1');

    // Blog & content
    $routes->get('blog', 'BlogController::index');
    $routes->get('blog/search', 'BlogController::search');
    $routes->get('blog/rss', 'BlogController::rss');
    $routes->get('blog/category/(:segment)', 'BlogController::category/$1');
    $routes->get('blog/tag/(:segment)', 'BlogController::tag/$1');
    $routes->get('blog/(:segment)', 'BlogController::show/$1');

    // Organizational structure
    $routes->get('org-structure', 'OrgStructureController::index');
    $routes->get('org-structure/chart', 'OrgStructureController::chart');
    $routes->get('org-structure/leadership', 'OrgStructureController::leadership');
    $routes->get('org-structure/regional/(:num)', 'OrgStructureController::regional/$1');
    $routes->get('org-structure/chart/data', 'OrgStructureController::getChartData');
    $routes->get('org-structure/search', 'OrgStructureController::search');
    $routes->get('org-structure/units/(:num)/data', 'OrgStructureController::getUnitData/$1');
    $routes->get('org-structure/units/(:segment)/positions/(:segment)', 'OrgStructureController::positionDetail/$1/$2');
    $routes->get('org-structure/units/(:segment)', 'OrgStructureController::detail/$1');
});

// --------------------------------------------------------------------
// Member Area Routes (Authenticated Users)
// --------------------------------------------------------------------
$routes->group('member', ['namespace' => 'App\\Controllers\\Member', 'filter' => 'session'], static function (RouteCollection $routes): void {
    // Dashboard
    $routes->get('dashboard', 'DashboardController::index');
    $routes->get('dashboard/stats', 'DashboardController::getStats');
    $routes->post('dashboard/notifications/(:num)/read', 'DashboardController::markNotificationRead/$1');
    $routes->post('dashboard/notifications/read-all', 'DashboardController::markAllNotificationsRead');

    // Profile management
    $routes->get('profile', 'ProfileController::index');
    $routes->post('profile', 'ProfileController::update');
    $routes->get('profile/edit', 'ProfileController::edit');
    $routes->post('profile/photo', 'ProfileController::updatePhoto');
    $routes->get('profile/change-password', 'ProfileController::changePassword');
    $routes->post('profile/change-password', 'ProfileController::updatePassword');
    $routes->get('profile/settings', 'ProfileController::settings');
    $routes->post('profile/settings', 'ProfileController::updateSettings');

    // Digital member card
    $routes->get('card', 'CardController::index');
    $routes->get('card/download', 'CardController::download');
    $routes->get('card/qrcode', 'CardController::qrcode');
    $routes->get('card/renew', 'CardController::renew');
    $routes->post('card/renew', 'CardController::submitRenewal');
    $routes->get('card/history', 'CardController::history');

    // Forum
    $routes->get('forum', 'ForumController::index');
    $routes->get('forum/create', 'ForumController::create');
    $routes->post('forum', 'ForumController::store');
    $routes->get('forum/search', 'ForumController::search');
    $routes->get('forum/my-threads', 'ForumController::myThreads');
    $routes->get('forum/(:num)', 'ForumController::show/$1');
    $routes->post('forum/(:num)/reply', 'ForumController::reply/$1');
    $routes->get('forum/posts/(:num)/edit', 'ForumController::editPost/$1');
    $routes->post('forum/posts/(:num)/update', 'ForumController::updatePost/$1');
    $routes->post('forum/posts/(:num)/delete', 'ForumController::deletePost/$1');

    // Surveys
    $routes->get('surveys', 'SurveyController::index');
    $routes->get('surveys/history', 'SurveyController::history');
    $routes->get('surveys/stats', 'SurveyController::getStats');
    $routes->get('surveys/(:num)', 'SurveyController::show/$1');
    $routes->post('surveys/(:num)/submit', 'SurveyController::submit/$1');
    $routes->get('surveys/(:num)/results', 'SurveyController::results/$1');
    $routes->get('surveys/(:num)/my-response', 'SurveyController::myResponse/$1');

    // Complaint center
    $routes->get('complaints', 'ComplaintController::index');
    $routes->get('complaints/create', 'ComplaintController::create');
    $routes->post('complaints', 'ComplaintController::store');
    $routes->get('complaints/(:num)', 'ComplaintController::show/$1');
    $routes->post('complaints/(:num)/reply', 'ComplaintController::reply/$1');
    $routes->post('complaints/(:num)/close', 'ComplaintController::close/$1');
    $routes->post('complaints/(:num)/reopen', 'ComplaintController::reopen/$1');
    $routes->post('complaints/(:num)/rate', 'ComplaintController::rate/$1');

    // Payment management
    $routes->get('payment', 'PaymentController::index');
    $routes->get('payment/create', 'PaymentController::create');
    $routes->post('payment', 'PaymentController::store');
    $routes->get('payment/(:num)', 'PaymentController::detail/$1');
    $routes->get('payment/(:num)/download', 'PaymentController::download/$1');
    $routes->post('payment/(:num)/cancel', 'PaymentController::cancel/$1');
});

// --------------------------------------------------------------------
// Administrator Routes (Pengurus, Koordinator, Super Admin)
// --------------------------------------------------------------------
$routes->group('admin', ['namespace' => 'App\\Controllers\\Admin', 'filter' => 'role:pengurus,koordinator_wilayah,superadmin'], static function (RouteCollection $routes): void {
    // Dashboard
    $routes->get('dashboard', 'DashboardController::index');
    $routes->get('dashboard/quick-stats', 'DashboardController::getQuickStats');
    $routes->get('dashboard/recent-activities', 'DashboardController::getRecentActivities');
    $routes->get('dashboard/charts', 'DashboardController::getCharts');

    // Member management
    $routes->get('members', 'MemberController::index', ['filter' => 'permission:member.view']);
    $routes->get('members/pending', 'MemberController::pending', ['filter' => 'permission:member.approve']);
    $routes->get('members/(:num)', 'MemberController::show/$1', ['filter' => 'permission:member.view_detail']);
    $routes->post('members/(:num)/approve', 'MemberController::approve/$1', ['filter' => 'permission:member.approve']);
    $routes->post('members/(:num)/reject', 'MemberController::reject/$1', ['filter' => 'permission:member.approve']);
    $routes->post('members/(:num)/suspend', 'MemberController::suspend/$1', ['filter' => 'permission:member.suspend']);
    $routes->post('members/(:num)/activate', 'MemberController::activate/$1', ['filter' => 'permission:member.suspend']);
    $routes->post('members/bulk-approve', 'MemberController::bulkApprove', ['filter' => 'permission:member.approve']);
    $routes->get('members/export', 'MemberController::export', ['filter' => 'permission:member.export']);
    $routes->get('members/regional', 'MemberController::getByRegion', ['filter' => 'permission:member.view']);

    // Bulk import
    $routes->get('members/import', 'BulkImportController::index', ['filter' => 'permission:member.import']);
    $routes->get('members/import/template', 'BulkImportController::downloadTemplate', ['filter' => 'permission:member.import']);
    $routes->post('members/import/upload', 'BulkImportController::uploadFile', ['filter' => 'permission:member.import']);
    $routes->get('members/import/preview', 'BulkImportController::preview', ['filter' => 'permission:member.import']);
    $routes->post('members/import/process', 'BulkImportController::process', ['filter' => 'permission:member.import']);
    $routes->get('members/import/history', 'BulkImportController::history', ['filter' => 'permission:member.import']);
    $routes->get('members/import/history/(:num)', 'BulkImportController::detail/$1', ['filter' => 'permission:member.import']);
    $routes->post('members/import/cancel', 'BulkImportController::cancel', ['filter' => 'permission:member.import']);

    // Statistics
    $routes->get('statistics', 'StatisticsController::index', ['filter' => 'permission:statistics.view']);
    $routes->get('statistics/members', 'StatisticsController::members', ['filter' => 'permission:statistics.view']);
    $routes->get('statistics/regional', 'StatisticsController::regional', ['filter' => 'permission:statistics.view']);
    $routes->get('statistics/growth', 'StatisticsController::growth', ['filter' => 'permission:statistics.view']);
    $routes->get('statistics/export', 'StatisticsController::export', ['filter' => 'permission:statistics.export']);

    // Forum moderation
    $routes->get('forum', 'ForumController::index', ['filter' => 'permission:forum.moderate']);
    $routes->get('forum/deleted', 'ForumController::deleted', ['filter' => 'permission:forum.moderate']);
    $routes->get('forum/categories', 'ForumController::categories', ['filter' => 'permission:forum.moderate']);
    $routes->get('forum/(:num)', 'ForumController::show/$1', ['filter' => 'permission:forum.moderate']);
    $routes->post('forum/(:num)/pin', 'ForumController::pin/$1', ['filter' => 'permission:forum.moderate']);
    $routes->post('forum/(:num)/lock', 'ForumController::lock/$1', ['filter' => 'permission:forum.moderate']);
    $routes->post('forum/posts/(:num)/delete', 'ForumController::deletePost/$1', ['filter' => 'permission:forum.moderate']);
    $routes->post('forum/threads/(:num)/delete', 'ForumController::deleteThread/$1', ['filter' => 'permission:forum.moderate']);
    $routes->post('forum/threads/(:num)/restore', 'ForumController::restoreThread/$1', ['filter' => 'permission:forum.moderate']);
    $routes->get('forum/stats', 'ForumController::getStats', ['filter' => 'permission:forum.moderate']);

    // Survey management
    $routes->get('surveys', 'SurveyController::index', ['filter' => 'permission:survey.view']);
    $routes->get('surveys/create', 'SurveyController::create', ['filter' => 'permission:survey.create']);
    $routes->post('surveys', 'SurveyController::store', ['filter' => 'permission:survey.create']);
    $routes->get('surveys/(:num)/edit', 'SurveyController::edit/$1', ['filter' => 'permission:survey.update']);
    $routes->post('surveys/(:num)/update', 'SurveyController::update/$1', ['filter' => 'permission:survey.update']);
    $routes->post('surveys/(:num)/delete', 'SurveyController::delete/$1', ['filter' => 'permission:survey.delete']);
    $routes->get('surveys/(:num)/responses', 'SurveyController::responses/$1', ['filter' => 'permission:survey.view']);
    $routes->get('surveys/(:num)/export', 'SurveyController::export/$1', ['filter' => 'permission:survey.export']);
    $routes->post('surveys/(:num)/publish', 'SurveyController::publish/$1', ['filter' => 'permission:survey.publish']);
    $routes->post('surveys/(:num)/close', 'SurveyController::close/$1', ['filter' => 'permission:survey.publish']);
    $routes->get('surveys/(:num)/statistics', 'SurveyController::getStatistics/$1', ['filter' => 'permission:survey.view']);

    // Complaint management
    $routes->get('complaints', 'ComplaintController::index', ['filter' => 'permission:complaint.view']);
    $routes->get('complaints/(:num)', 'ComplaintController::show/$1', ['filter' => 'permission:complaint.view']);
    $routes->post('complaints/(:num)/assign', 'ComplaintController::assign/$1', ['filter' => 'permission:complaint.respond']);
    $routes->post('complaints/(:num)/reply', 'ComplaintController::reply/$1', ['filter' => 'permission:complaint.respond']);
    $routes->post('complaints/(:num)/status', 'ComplaintController::updateStatus/$1', ['filter' => 'permission:complaint.respond']);
    $routes->post('complaints/(:num)/close', 'ComplaintController::close/$1', ['filter' => 'permission:complaint.respond']);
    $routes->get('complaints/export', 'ComplaintController::export', ['filter' => 'permission:complaint.export']);
    $routes->get('complaints/stats', 'ComplaintController::getStats', ['filter' => 'permission:complaint.view']);

    // Payment verification
    $routes->get('payments', 'PaymentController::index', ['filter' => 'permission:payment.view']);
    $routes->get('payments/pending', 'PaymentController::pending', ['filter' => 'permission:payment.verify']);
    $routes->get('payments/(:num)', 'PaymentController::detail/$1', ['filter' => 'permission:payment.view']);
    $routes->post('payments/(:num)/verify', 'PaymentController::verify/$1', ['filter' => 'permission:payment.verify']);
    $routes->post('payments/(:num)/reject', 'PaymentController::reject/$1', ['filter' => 'permission:payment.verify']);
    $routes->get('payments/report', 'PaymentController::report', ['filter' => 'permission:payment.report']);
    $routes->get('payments/export', 'PaymentController::export', ['filter' => 'permission:payment.export']);
    $routes->post('payments/(:num)/delete', 'PaymentController::delete/$1', ['filter' => 'role:superadmin']);

    // WhatsApp group management
    $routes->get('wagroups', 'WAGroupController::index', ['filter' => 'permission:wagroup.manage']);
    $routes->get('wagroups/create', 'WAGroupController::create', ['filter' => 'permission:wagroup.manage']);
    $routes->post('wagroups', 'WAGroupController::store', ['filter' => 'permission:wagroup.manage']);
    $routes->get('wagroups/(:num)/edit', 'WAGroupController::edit/$1', ['filter' => 'permission:wagroup.manage']);
    $routes->post('wagroups/(:num)/update', 'WAGroupController::update/$1', ['filter' => 'permission:wagroup.manage']);
    $routes->post('wagroups/(:num)/delete', 'WAGroupController::delete/$1', ['filter' => 'permission:wagroup.manage']);
    $routes->get('wagroups/(:num)/members', 'WAGroupController::members/$1', ['filter' => 'permission:wagroup.manage']);
    $routes->post('wagroups/(:num)/members/(:num)/confirm', 'WAGroupController::confirmJoin/$1/$2', ['filter' => 'permission:wagroup.manage']);
    $routes->post('wagroups/(:num)/members/(:num)/remove', 'WAGroupController::removeMember/$1/$2', ['filter' => 'permission:wagroup.manage']);
    $routes->get('wagroups/stats', 'WAGroupController::getStats', ['filter' => 'permission:wagroup.manage']);

    // Content management
    $routes->get('content/posts', 'ContentController::posts', ['filter' => 'permission:content.manage']);
    $routes->get('content/posts/create', 'ContentController::createPost', ['filter' => 'permission:content.manage']);
    $routes->post('content/posts', 'ContentController::storePost', ['filter' => 'permission:content.manage']);
    $routes->get('content/posts/(:num)/edit', 'ContentController::editPost/$1', ['filter' => 'permission:content.manage']);
    $routes->post('content/posts/(:num)/update', 'ContentController::updatePost/$1', ['filter' => 'permission:content.manage']);
    $routes->post('content/posts/(:num)/delete', 'ContentController::deletePost/$1', ['filter' => 'permission:content.manage']);
    $routes->post('content/posts/(:num)/publish', 'ContentController::publish/$1', ['filter' => 'permission:content.manage']);
    $routes->post('content/posts/(:num)/unpublish', 'ContentController::unpublish/$1', ['filter' => 'permission:content.manage']);
    $routes->post('content/posts/generate-slug', 'ContentController::generateSlug', ['filter' => 'permission:content.manage']);
    $routes->get('content/pages', 'ContentController::pages', ['filter' => 'permission:content.manage']);
    $routes->get('content/pages/(:segment)/edit', 'ContentController::editPage/$1', ['filter' => 'permission:content.manage']);
    $routes->post('content/pages/(:segment)/update', 'ContentController::updatePage/$1', ['filter' => 'permission:content.manage']);
    $routes->get('content/categories', 'ContentController::categories', ['filter' => 'permission:content.manage']);
    $routes->post('content/categories', 'ContentController::storeCategory', ['filter' => 'permission:content.manage']);
    $routes->post('content/categories/(:num)/update', 'ContentController::updateCategory/$1', ['filter' => 'permission:content.manage']);
    $routes->post('content/categories/(:num)/delete', 'ContentController::deleteCategory/$1', ['filter' => 'permission:content.manage']);

    // Organizational structure management
    $routes->get('org-structure', 'OrgStructureController::index', ['filter' => 'permission:org_structure.view']);
    $routes->get('org-structure/units/(:num)', 'OrgStructureController::showUnit/$1', ['filter' => 'permission:org_structure.view']);
    $routes->get('org-structure/units/create', 'OrgStructureController::createUnit', ['filter' => 'permission:org_structure.manage']);
    $routes->post('org-structure/units', 'OrgStructureController::storeUnit', ['filter' => 'permission:org_structure.manage']);
    $routes->get('org-structure/units/(:num)/edit', 'OrgStructureController::editUnit/$1', ['filter' => 'permission:org_structure.manage']);
    $routes->post('org-structure/units/(:num)/update', 'OrgStructureController::updateUnit/$1', ['filter' => 'permission:org_structure.manage']);
    $routes->post('org-structure/units/(:num)/delete', 'OrgStructureController::deleteUnit/$1', ['filter' => 'permission:org_structure.manage']);
    $routes->get('org-structure/positions/create', 'OrgStructureController::createPosition', ['filter' => 'permission:org_structure.manage']);
    $routes->post('org-structure/positions', 'OrgStructureController::storePosition', ['filter' => 'permission:org_structure.manage']);
    $routes->get('org-structure/positions/(:num)/edit', 'OrgStructureController::editPosition/$1', ['filter' => 'permission:org_structure.manage']);
    $routes->post('org-structure/positions/(:num)/update', 'OrgStructureController::updatePosition/$1', ['filter' => 'permission:org_structure.manage']);
    $routes->post('org-structure/positions/(:num)/delete', 'OrgStructureController::deletePosition/$1', ['filter' => 'permission:org_structure.manage']);
    $routes->get('org-structure/positions/(:num)/assign', 'OrgStructureController::assignMemberForm/$1', ['filter' => 'permission:org_structure.assign']);
    $routes->post('org-structure/positions/(:num)/assign', 'OrgStructureController::assignMember/$1', ['filter' => 'permission:org_structure.assign']);
    $routes->post('org-structure/assignments/(:num)/end', 'OrgStructureController::endAssignment/$1', ['filter' => 'permission:org_structure.assign']);
    $routes->get('org-structure/units/(:num)/data', 'OrgStructureController::getUnitData/$1', ['filter' => 'permission:org_structure.view']);
    $routes->get('org-structure/positions/(:num)/data', 'OrgStructureController::getPositionData/$1', ['filter' => 'permission:org_structure.view']);
    $routes->get('org-structure/statistics', 'OrgStructureController::getStatistics', ['filter' => 'permission:org_structure.view']);
    $routes->get('org-structure/search-members', 'OrgStructureController::searchMembers', ['filter' => 'permission:org_structure.assign']);
});

// --------------------------------------------------------------------
// Super Admin Routes
// --------------------------------------------------------------------
$routes->group('super', ['namespace' => 'App\\Controllers\\Super', 'filter' => 'role:superadmin'], static function (RouteCollection $routes): void {
    // Dashboard
    $routes->get('dashboard', 'DashboardController::index');

    // Role management
    $routes->get('roles', 'RoleController::index');
    $routes->get('roles/create', 'RoleController::create');
    $routes->post('roles', 'RoleController::store');
    $routes->get('roles/(:num)/edit', 'RoleController::edit/$1');
    $routes->post('roles/(:num)/update', 'RoleController::update/$1');
    $routes->post('roles/(:num)/delete', 'RoleController::delete/$1');
    $routes->get('roles/(:num)/permissions', 'RoleController::permissions/$1');
    $routes->post('roles/(:num)/permissions', 'RoleController::assignPermissions/$1');
    $routes->get('roles/(:num)/members', 'RoleController::members/$1');

    // Permission management
    $routes->get('permissions', 'PermissionController::index');
    $routes->get('permissions/create', 'PermissionController::create');
    $routes->post('permissions', 'PermissionController::store');
    $routes->get('permissions/(:num)/edit', 'PermissionController::edit/$1');
    $routes->post('permissions/(:num)/update', 'PermissionController::update/$1');
    $routes->post('permissions/(:num)/delete', 'PermissionController::delete/$1');
    $routes->get('permissions/(:num)/roles', 'PermissionController::roles/$1');
    $routes->post('permissions/sync-shield', 'PermissionController::syncToShield');

    // Menu management
    $routes->get('menus', 'MenuController::index');
    $routes->get('menus/create', 'MenuController::create');
    $routes->post('menus', 'MenuController::store');
    $routes->get('menus/(:num)/edit', 'MenuController::edit/$1');
    $routes->post('menus/(:num)/update', 'MenuController::update/$1');
    $routes->post('menus/(:num)/delete', 'MenuController::delete/$1');
    $routes->post('menus/(:num)/toggle-status', 'MenuController::toggleStatus/$1');
    $routes->post('menus/reorder', 'MenuController::reorder');
    $routes->get('menus/preview', 'MenuController::preview');
    $routes->get('menus/preview/(:segment)', 'MenuController::preview/$1');

    // Master data
    $routes->get('master/provinces', 'MasterDataController::provinces');
    $routes->post('master/provinces', 'MasterDataController::storeProvince');
    $routes->post('master/provinces/(:num)/update', 'MasterDataController::updateProvince/$1');
    $routes->post('master/provinces/(:num)/delete', 'MasterDataController::deleteProvince/$1');

    $routes->get('master/regencies', 'MasterDataController::regencies');
    $routes->post('master/regencies', 'MasterDataController::storeRegency');
    $routes->post('master/regencies/(:num)/update', 'MasterDataController::updateRegency/$1');
    $routes->post('master/regencies/(:num)/delete', 'MasterDataController::deleteRegency/$1');

    $routes->get('master/universities', 'MasterDataController::universities');
    $routes->post('master/universities', 'MasterDataController::storeUniversity');
    $routes->post('master/universities/(:num)/update', 'MasterDataController::updateUniversity/$1');
    $routes->post('master/universities/(:num)/delete', 'MasterDataController::deleteUniversity/$1');

    $routes->get('master/study-programs', 'MasterDataController::studyPrograms');
    $routes->post('master/study-programs', 'MasterDataController::storeStudyProgram');
    $routes->post('master/study-programs/(:num)/update', 'MasterDataController::updateStudyProgram/$1');
    $routes->post('master/study-programs/(:num)/delete', 'MasterDataController::deleteStudyProgram/$1');

    $routes->get('master/export/(:segment)', 'MasterDataController::export/$1');
});

// --------------------------------------------------------------------
// Environment Specific Routes
// --------------------------------------------------------------------
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
