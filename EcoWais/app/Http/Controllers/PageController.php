<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function dashboard()
    {
        return view('pages.dashboard');
    }

    public function tracking()
    {
        return view('pages.tracking');
    }

    public function admin()
    {
        return view('pages.admin');
    }

    public function driver()
    {
        return view('pages.driver');
    }

    public function resident()
    {
        return view('pages.resident');
    }

    public function barangayScheduling()
    {
        return view('pages.barangay-scheduling');
    }

    public function userManagement()
    {
        return view('municipality-admin.user-management');
    }
}
