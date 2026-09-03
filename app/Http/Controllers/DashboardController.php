<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return view('dashboard', [
            'rolId' => $user->rol_id, // puede ser null si el admin todavía no lo asignó
        ]);
    }
}