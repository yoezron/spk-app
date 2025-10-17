<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

/**
 * Home Controller
 * 
 * Handles the default landing page
 * Smart redirect based on user authentication status and role
 * 
 * @package App\Controllers
 * @author  SPK Development Team
 * @version 2.0.0 - FIXED: Smart role-based redirect
 */
class Home extends BaseController
{
    /**
     * Home page - Smart redirect based on auth status
     * 
     * 🔧 FIXED: Now properly redirects Super Admin to correct dashboard
     * 
     * Behavior:
     * - If logged in as Super Admin → /super/dashboard
     * - If logged in as Pengurus/Koordinator → /admin/dashboard  
     * - If logged in as Anggota/Calon Anggota → /member/dashboard
     * - If not logged in → Show public homepage
     * 
     * @return string|RedirectResponse
     */
    public function index()
    {
        // Check if user is logged in
        if (auth()->loggedIn()) {
            $user = auth()->user();

            // Redirect based on role (FIXED: Proper role names)
            if ($user->inGroup('superadmin')) {
                return redirect()->to('/super/dashboard');
            }

            if ($user->inGroup('pengurus') || $user->inGroup('koordinator')) {
                return redirect()->to('/admin/dashboard');
            }

            // Anggota or Calon Anggota
            return redirect()->to('/member/dashboard');
        }

        // Not logged in - show public homepage
        $data = [
            'title' => 'Selamat Datang - Satuan Pendidik Khusus',
            'meta_description' => 'Website resmi Satuan Pendidik Khusus (SPK) Indonesia',
        ];

        return view('public/home', $data);
    }
}
