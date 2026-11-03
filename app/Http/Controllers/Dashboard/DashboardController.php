<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\UsrUserLog;
use App\Services\DashboardHomeService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller implements HasMiddleware
{
    public function __construct(private DashboardHomeService $home) {}

    public static function middleware(): array
    {
        return ['staff.auth'];
    }

    public function index()
    {
        return view('dashboard', $this->home->payload());
    }

    public function logoutAuthenticationProcess()
    {
        Auth::logout();

        $logId = (int) session('userLogId');
        if ($logId > 0) {
            $updateLogs = UsrUserLog::find($logId);
            if ($updateLogs) {
                $updateLogs->logout_date = Carbon::now();
                $updateLogs->update();
            }
        }

        session()->forget('userLogId');

        return redirect('/')->with('message_success', 'Logged out successfully');
    }
}
