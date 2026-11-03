<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
 
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // Add this import for Log
use App\Http\Controllers\SMS\SMSController;
use App\Models\Staff;
use App\Models\SchoolSetting;
use App\Models\UsrUserLog;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use App\Mail\SendPasswordMail;
use Illuminate\Support\Str;

class AuthenticationController extends Controller
{
    public function getAdminLoginPage(){
        if (Auth::check()) {
            return redirect()->intended('/dashboard');
        }

        return view('authentication.login', [
            'school' => SchoolSetting::current(),
        ]);
    }

    
    public function logout()
    {
        session()->flush();
        return redirect('/')->with('message_success', 'Logged out successfully');
    }

   

     public function authenticationProcess(Request $request){

        $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $maxAttempts = 5;
    $lockMinutes = 10;

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return back()->with('login_error_message','Email does not exist or wrong password.');
    }

    // Check if account is locked
    if ($user->locked_until && Carbon::now()->lt($user->locked_until)) {

        $secondsLeft = Carbon::now()->diffInSeconds($user->locked_until);
        $minutesLeft = ceil($secondsLeft / 60);

        return back()->with(
            'login_error_message',
            "Account locked. Try again in {$minutesLeft} minute(s)."
        );
    }

    // Check password
    if (!Hash::check($request->password, $user->password)) {

        $user->login_attempts += 1;

        $attemptsLeft = $maxAttempts - $user->login_attempts;

        if ($user->login_attempts >= $maxAttempts) {

            $user->locked_until = Carbon::now()->addMinutes($lockMinutes);
            $user->login_attempts = 0;
            $user->save();

            return back()->with(
                'login_error_message',
                "Too many failed attempts. Account locked for {$lockMinutes} minutes."
            );
        }

        $user->save();

        return back()->with(
            'login_error_message',
            "Wrong password. {$attemptsLeft} attempt(s) left before account lock."
        );
    }

    // Login successful
    Auth::login($user);

    $request->session()->regenerate();

    // Reset attempts
    $user->login_attempts = 0;
    $user->locked_until = null;
    $user->save();

    // Insert login log
    $insertLogs = new UsrUserLog();
    $insertLogs->user_id = Auth::user()->id;
    $insertLogs->login_date = Carbon::now();
    $insertLogs->login_ip = request()->ip();
    $insertLogs->save();

    session()->put('userLogId', $insertLogs->id);

    return redirect()->intended('/dashboard');
        
    }

    
 

  //Generate 8 randome characters
    function generateRandomPassword($length = 8) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+[]{}<>?';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $password;
    }
}
