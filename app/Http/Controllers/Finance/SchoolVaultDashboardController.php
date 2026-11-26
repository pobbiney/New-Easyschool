<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\Finance\SchoolVaultDashboardService;
use Illuminate\Http\Request;

class SchoolVaultDashboardController extends Controller
{
    public function __construct(private SchoolVaultDashboardService $dashboard) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'period' => 'nullable|in:today,week,month,term,custom',
            'from_date' => 'nullable|required_if:period,custom|date',
            'to_date' => 'nullable|required_if:period,custom|date|after_or_equal:from_date',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'academic_term_id' => 'nullable|exists:academic_terms,id',
        ]);

        return view('finance.school-vault-dashboard', $this->dashboard->payload($request, $validated));
    }
}
