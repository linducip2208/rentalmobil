<?php

namespace App\Http\Controllers;

class DocsController extends Controller
{
    public function index()
    {
        $demoAccounts = [
            ['role' => 'Owner', 'email' => 'admin@rentalmobil.test', 'password' => 'password'],
            ['role' => 'Manager', 'email' => 'manager@rentalmobil.test', 'password' => 'password'],
            ['role' => 'Admin', 'email' => 'admin2@rentalmobil.test', 'password' => 'password'],
            ['role' => 'Kasir', 'email' => 'kasir@rentalmobil.test', 'password' => 'password'],
            ['role' => 'Driver', 'email' => 'driver@rentalmobil.test', 'password' => 'password'],
            ['role' => 'Customer', 'email' => 'customer@rentalmobil.test', 'password' => 'password'],
        ];

        return view('docs.index', compact('demoAccounts'));
    }
}
