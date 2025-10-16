<?php

declare(strict_types=1);

namespace App\Controllers\Super;

use App\Controllers\Admin\DashboardController as AdminDashboardController;

/**
 * Super Admin Dashboard Controller
 *
 * Reuse the admin dashboard logic so super administrators can access
 * the same analytics overview without duplicating implementation.
 */
class DashboardController extends AdminDashboardController {}
