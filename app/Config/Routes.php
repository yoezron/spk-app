<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ========================================
// DEFAULT ROUTE
// ========================================
$routes->get('/', 'Public\HomeController::index');

// ========================================
// SHIELD AUTH ROUTES
// ========================================
service('auth')->routes($routes);

// ========================================
// PUBLIC ROUTES (No Auth Required)
// ========================================
$routes->group('', ['namespace' => 'App\Controllers\Public'], function ($routes) {
    $routes->get('home', 'HomeController::index');
    $routes->get('verify/(:segment)', 'VerifyCardController::verify/$1');

    // Blog
    $routes->get('blog', 'BlogController::index');
    $routes->get('blog/(:segment)', 'BlogController::detail/$1');
});

// ========================================
// AUTHENTICATION ROUTES (Custom - extend Shield)
// ========================================
$routes->group('auth', ['namespace' => 'App\Controllers\Auth'], function ($routes) {
    // Login
    $routes->get('login', 'LoginController::loginView');
    $routes->post('login', 'LoginController::loginAction');

    // Register
    $routes->get('register', 'RegisterController::registerView');
    $routes->post('register', 'RegisterController::registerAction');

    // Verify Email
    $routes->get('verify/(:segment)', 'VerifyController::verify/$1');

    // Logout
    $routes->get('logout', 'LoginController::logoutAction');
});

// ========================================
// MEMBER ROUTES (Role: anggota, pengurus, superadmin)
// ========================================
$routes->group('member', ['namespace' => 'App\Controllers\Member', 'filter' => 'session'], function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'DashboardController::index');

    // Profile
    $routes->get('profile', 'ProfileController::index');
    $routes->get('profile/edit', 'ProfileController::edit');
    $routes->post('profile/update', 'ProfileController::update');
    $routes->post('profile/upload-photo', 'ProfileController::uploadPhoto');
    $routes->get('profile/change-password', 'ProfileController::changePassword');
    $routes->post('profile/update-password', 'ProfileController::updatePassword');

    // Member Card
    $routes->get('card', 'CardController::index');
    $routes->get('card/download', 'CardController::download');

    // Forum
    $routes->get('forum', 'ForumController::index');
    $routes->get('forum/(:num)', 'ForumController::show/$1');
    $routes->get('forum/create', 'ForumController::create');
    $routes->post('forum/store', 'ForumController::store');
    $routes->post('forum/(:num)/reply', 'ForumController::reply/$1');

    // Survey
    $routes->get('survey', 'SurveyController::index');
    $routes->get('survey/(:num)', 'SurveyController::show/$1');
    $routes->post('survey/(:num)/submit', 'SurveyController::submit/$1');

    // Complaint/Ticket
    $routes->get('complaint', 'ComplaintController::index');
    $routes->get('complaint/create', 'ComplaintController::create');
    $routes->post('complaint/store', 'ComplaintController::store');
    $routes->get('complaint/(:num)', 'ComplaintController::show/$1');
});

// ========================================
// ADMIN ROUTES (Role: pengurus, koordinator_wilayah, superadmin)
// ========================================
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'role:pengurus,koordinator_wilayah,superadmin'], function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'DashboardController::index');

    // Members Management
    $routes->get('members', 'MemberController::index', ['filter' => 'permission:member.view']);
    $routes->get('members/pending', 'MemberController::pending', ['filter' => 'permission:member.approve']);
    $routes->get('members/(:num)', 'MemberController::detail/$1', ['filter' => 'permission:member.view_detail']);
    $routes->post('members/(:num)/approve', 'MemberController::approve/$1', ['filter' => 'permission:member.approve']);
    $routes->post('members/(:num)/reject', 'MemberController::reject/$1', ['filter' => 'permission:member.approve']);
    $routes->post('members/(:num)/suspend', 'MemberController::suspend/$1', ['filter' => 'permission:member.suspend']);
    $routes->post('members/(:num)/activate', 'MemberController::activate/$1', ['filter' => 'permission:member.suspend']);
    $routes->get('members/export', 'MemberController::export', ['filter' => 'permission:member.export']);

    // Bulk Import
    $routes->get('bulk-import', 'BulkImportController::index', ['filter' => 'permission:member.import']);
    $routes->post('bulk-import/upload', 'BulkImportController::upload', ['filter' => 'permission:member.import']);
    $routes->post('bulk-import/process', 'BulkImportController::process', ['filter' => 'permission:member.import']);
    $routes->get('bulk-import/template', 'BulkImportController::downloadTemplate');

    // Statistics
    $routes->get('statistics', 'StatisticsController::index', ['filter' => 'permission:statistics.view']);
    $routes->get('statistics/export', 'StatisticsController::export', ['filter' => 'permission:statistics.view']);

    // Forum Moderation
    $routes->get('forum', 'ForumController::index', ['filter' => 'permission:forum.moderate']);
    $routes->post('forum/(:num)/delete', 'ForumController::delete/$1', ['filter' => 'permission:forum.moderate']);

    // Survey Management
    $routes->get('survey', 'SurveyController::index', ['filter' => 'permission:survey.create']);
    $routes->get('survey/create', 'SurveyController::create', ['filter' => 'permission:survey.create']);
    $routes->post('survey/store', 'SurveyController::store', ['filter' => 'permission:survey.create']);
    $routes->get('survey/(:num)/edit', 'SurveyController::edit/$1', ['filter' => 'permission:survey.edit']);
    $routes->post('survey/(:num)/update', 'SurveyController::update/$1', ['filter' => 'permission:survey.edit']);
    $routes->get('survey/(:num)/responses', 'SurveyController::responses/$1', ['filter' => 'permission:survey.view']);

    // Complaint Management
    $routes->get('complaint', 'ComplaintController::index', ['filter' => 'permission:complaint.view']);
    $routes->get('complaint/(:num)', 'ComplaintController::show/$1', ['filter' => 'permission:complaint.view']);
    $routes->post('complaint/(:num)/reply', 'ComplaintController::reply/$1', ['filter' => 'permission:complaint.respond']);

    // WhatsApp Groups
    $routes->get('wa-groups', 'WAGroupController::index', ['filter' => 'permission:wagroup.manage']);
    $routes->get('wa-groups/create', 'WAGroupController::create', ['filter' => 'permission:wagroup.manage']);
    $routes->post('wa-groups/store', 'WAGroupController::store', ['filter' => 'permission:wagroup.manage']);

    // Content Management
    $routes->get('content/posts', 'ContentController::posts', ['filter' => 'permission:content.manage']);
    $routes->get('content/posts/create', 'ContentController::createPost', ['filter' => 'permission:content.manage']);
    $routes->post('content/posts/store', 'ContentController::storePost', ['filter' => 'permission:content.manage']);
});

// ========================================
// SUPER ADMIN ROUTES (Role: superadmin only)
// ========================================
$routes->group('super', ['namespace' => 'App\Controllers\Super', 'filter' => 'role:superadmin'], function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'RoleController::index'); // Temporary

    // Role Management
    $routes->get('roles', 'RoleController::index');
    $routes->get('roles/create', 'RoleController::create');
    $routes->post('roles/store', 'RoleController::store');
    $routes->get('roles/(:num)/edit', 'RoleController::edit/$1');
    $routes->post('roles/(:num)/update', 'RoleController::update/$1');
    $routes->post('roles/(:num)/delete', 'RoleController::delete/$1');

    // Permission Management
    $routes->get('permissions', 'PermissionController::index');
    $routes->get('permissions/create', 'PermissionController::create');
    $routes->post('permissions/store', 'PermissionController::store');
    $routes->get('permissions/(:num)/edit', 'PermissionController::edit/$1');
    $routes->post('permissions/(:num)/update', 'PermissionController::update/$1');

    // Menu Management
    $routes->get('menus', 'MenuController::index');
    $routes->get('menus/create', 'MenuController::create');
    $routes->post('menus/store', 'MenuController::store');
    $routes->get('menus/(:num)/edit', 'MenuController::edit/$1');
    $routes->post('menus/(:num)/update', 'MenuController::update/$1');

    // Master Data Management
    $routes->get('master-data/provinces', 'MasterDataController::provinces');
    $routes->get('master-data/provinces/create', 'MasterDataController::createProvince');
    $routes->post('master-data/provinces/store', 'MasterDataController::storeProvince');

    $routes->get('master-data/regencies', 'MasterDataController::regencies');
    $routes->get('master-data/universities', 'MasterDataController::universities');
});
