<?php

namespace App\Http\Controllers;

use App\Support\StaffWorkspace;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->hasRole('admin')) {
            return app(AdminController::class)->index($request);
        }
        if ($request->user()->hasRole('demandeur')) {
            return app(DemandeurController::class)->dashboard($request);
        }
        if (StaffWorkspace::role($request->user())) {
            return app(StaffController::class)->dashboard($request);
        }
        abort(403, 'Aucun espace disponible pour votre rôle.');
    }
}
