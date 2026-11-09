
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign in — EasySchool</title>
<link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}" sizes="16x16">
<link rel="stylesheet" href="{{ asset('assets/css/lib/bootstrap.min.css') }}">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { height: 100%; }

  body {
    font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
    color: #ECEAE4;
    background: #0A0D0C;
    min-height: 100vh;
    position: relative;
    overflow-x: hidden;
    display: flex;
    align-items: center;
  }

  /* subtle grid texture */
  .grid-overlay {
    position: absolute;
    inset: 0;
    background-image:
      linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
    background-size: 44px 44px;
    z-index: 0;
    mask-image: radial-gradient(ellipse at center, black 30%, transparent 75%);
  }

  .bg-glow-1 {
    position: absolute;
    width: 750px; height: 750px;
    background: radial-gradient(circle, rgba(37,161,148,0.16) 0%, transparent 70%);
    top: -220px; left: -180px;
    z-index: 0;
  }
  .bg-glow-2 {
    position: absolute;
    
    background: radial-gradient(circle, rgba(124,92,255,0.09) 0%, transparent 70%);
    bottom: -260px; right: 6%;
    z-index: 0;
  }

  .page {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 1440px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 48px;
    padding: 56px 64px;
    flex-wrap: wrap;
  }

  .hero { max-width: 580px; flex: 1; min-width: 340px; }

  .eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 12.5px;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #25A194;
    background: rgba(37,161,148,0.1);
    border: 1px solid rgba(37,161,148,0.3);
    padding: 6px 14px;
    border-radius: 100px;
    margin-bottom: 22px;
  }

  .eyebrow .pulse-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #25A194;
    box-shadow: 0 0 0 0 rgba(37,161,148,0.6);
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(37,161,148,0.5); }
    70% { box-shadow: 0 0 0 8px rgba(37,161,148,0); }
    100% { box-shadow: 0 0 0 0 rgba(37,161,148,0); }
  }

  .hero h1 {
    font-family: 'Sora', 'Segoe UI', system-ui, sans-serif;
    font-weight: 800;
    font-size: 54px;
    line-height: 1.14;
    color: #F5F3EE;
    letter-spacing: -0.01em;
  }

  .hero h1 .accent {
    color: #25A194;
    position: relative;
  }

  .hero p {
    font-size: 16px;
    line-height: 1.75;
    color: #A6A39B;
    margin-top: 22px;
    max-width: 460px;
  }

  .stat-row {
    display: flex;
    gap: 36px;
    margin-top: 36px;
  }

  .stat b {
    display: block;
    font-family: 'Sora', 'Segoe UI', system-ui, sans-serif;
    font-size: 24px;
    font-weight: 700;
    color: #F5F3EE;
  }

  .stat span {
    font-size: 12.5px;
    color: #83807A;
    letter-spacing: 0.02em;
  }

  /* illustration cluster */
  .illustration {
    margin-top: 44px;
    position: relative;
    width: 480px;
    height: 220px;
  }

  .illustration svg { position: absolute; }

  .pc-monitor {
    top: 0; left: 120px;
    filter: drop-shadow(0 20px 40px rgba(0,0,0,0.45));
    animation: float 6s ease-in-out infinite;
  }

  .bar-card {
    top: 60px; left: 0;
    filter: drop-shadow(0 16px 30px rgba(0,0,0,0.4));
    animation: float 5s ease-in-out infinite;
    animation-delay: -2s;
  }

  .badge-check {
    top: 8px; left: 330px;
    animation: float 4.5s ease-in-out infinite;
    animation-delay: -1s;
  }

  .badge-bell {
    top: 150px; left: 260px;
    animation: float 5.5s ease-in-out infinite;
    animation-delay: -3s;
  }

  @keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-12px); }
  }

  /* login card */
  .card-wrap {
    position: relative;
    width: 500px;
    flex-shrink: 0;
  }

  .card-border-glow {
    position: absolute;
    inset: -1px;
    border-radius: 22px;
    background: linear-gradient(135deg, rgba(37,161,148,0.55), rgba(124,92,255,0.25), rgba(37,161,148,0.1));
    z-index: 0;
  }

  .card {
    position: relative;
    z-index: 1;
    background: rgba(17, 20, 19, 0.82);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 21px;
    padding: 42px 38px 30px;
    box-shadow: 0 30px 70px rgba(0,0,0,0.55);
    text-align: center;
  }

  .logo {
    width: 88px;
    height: 88px;
    border-radius: 50%;
    background: transparent;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
    padding: 0;
    overflow: hidden;
  }

  .logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
  }

  .card h2 {
    font-family: 'Sora', 'Segoe UI', system-ui, sans-serif;
    font-weight: 700;
    font-size: 21px;
    color: #F5F3EE;
    margin-bottom: 4px;
  }

  .card .sub {
    font-size: 13px;
    color: #83807A;
    margin-bottom: 30px;
  }

  .field { text-align: left; margin-bottom: 20px; }

  .field label {
    display: block;
    font-size: 11.5px;
    font-weight: 600;
    color: #B7B4AC;
    margin-bottom: 8px;
    letter-spacing: 0.03em;
    text-transform: uppercase;
  }

  .input-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.09);
    border-radius: 10px;
    padding: 12px 14px;
    transition: border-color 0.15s ease, background 0.15s ease;
  }

  .input-wrap:focus-within {
    border-color: #25A194;
    background: rgba(37,161,148,0.05);
  }

  .input-wrap svg { flex-shrink: 0; opacity: 0.75; }

  .input-wrap input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    color: #F5F3EE;
    font-size: 14px;
    font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
  }

  .input-wrap input::placeholder { color: #706D66; }

  .toggle-eye {
    background: none; border: none; cursor: pointer;
    padding: 0; display: flex; opacity: 0.65;
  }
  .toggle-eye:hover { opacity: 1; }

  .row-between {
    display: flex; align-items: center; justify-content: space-between;
    font-size: 12.5px; margin-bottom: 24px;
  }

  .remember { display: flex; align-items: center; gap: 7px; color: #A6A39B; }
  .remember input { accent-color: #25A194; }

  .row-between a { color: #25A194; text-decoration: none; font-weight: 500; }
  .row-between a:hover { text-decoration: underline; }

  button.submit {
    width: 100%;
    height: 48px;
    background: linear-gradient(135deg, #2DBFAE, #1C8078);
    color: #fff;
    border: none;
    border-radius: 11px;
    font-family: 'Sora', 'Segoe UI', system-ui, sans-serif;
    font-weight: 700;
    font-size: 14.5px;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
  }

  button.submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 24px rgba(37,161,148,0.35);
  }

  .divider-line {
    display: flex; align-items: center; gap: 12px;
    margin: 24px 0 20px;
    color: #6E6B63;
    font-size: 11.5px;
  }
  .divider-line::before, .divider-line::after {
    content: ''; flex: 1; height: 1px; background: rgba(255,255,255,0.08);
  }

  .footer-links {
    font-size: 12px;
    color: #8B887F;
    line-height: 2.1;
  }

  .footer-links a { color: #25A194; text-decoration: none; font-weight: 500; }
  .footer-links a:hover { text-decoration: underline; }
  .divider { color: #4B4941; margin: 0 6px; }

  .copyright { margin-top: 20px; font-size: 11px; color: #605D57; }

  @media (max-width: 980px) {
    .page { flex-direction: column; padding: 40px 22px; }
    .hero h1 { font-size: 38px; }
    .illustration { width: 100%; max-width: 420px; }
    .card-wrap { width: 100%; max-width: 400px; }
  }
</style>
</head>
<body>

  <div class="grid-overlay"></div>
  <div class="bg-glow-1"></div>
  <div class="bg-glow-2"></div>

  <div class="page">

    <div class="hero">
      <div class="eyebrow"><span class="pulse-dot"></span> Built for schools, not spreadsheets</div>

      <h1>Manage your<br>school,<br><span class="accent">effortlessly.</span></h1>
      <p>The all-in-one platform for schools. Payroll, billing, attendance, reports, learning management, AI insights and more — built for the way you actually work.</p>

      <div class="stat-row">
        <div class="stat"><b>200+</b><span>Schools onboarded</span></div>
        <div class="stat"><b>98%</b><span>On-time payroll</span></div>
        <div class="stat"><b>24/7</b><span>Support</span></div>
      </div>

      <div class="illustration">

        <svg class="bar-card" width="150" height="130" viewBox="0 0 150 130" fill="none">
          <rect x="1" y="1" width="148" height="128" rx="14" fill="#14201E" stroke="#25A194" stroke-opacity="0.35"/>
          <text x="18" y="28" fill="#ECEAE4" font-family="Inter" font-size="11" font-weight="600">Attendance</text>
          <rect x="18" y="46" width="16" height="60" rx="3" fill="#25A194"/>
          <rect x="42" y="60" width="16" height="46" rx="3" fill="#7C5CFF"/>
          <rect x="66" y="38" width="16" height="68" rx="3" fill="#25A194" opacity="0.6"/>
          <rect x="90" y="70" width="16" height="36" rx="3" fill="#7C5CFF" opacity="0.6"/>
          <rect x="114" y="52" width="16" height="54" rx="3" fill="#25A194" opacity="0.35"/>
        </svg>

        <svg class="pc-monitor" width="220" height="160" viewBox="0 0 220 160" fill="none">
          <rect x="1" y="1" width="218" height="140" rx="12" fill="#0F1613" stroke="#25A194" stroke-opacity="0.35"/>
          <rect x="90" y="141" width="40" height="10" fill="#1B2422"/>
          <rect x="70" y="151" width="80" height="6" rx="3" fill="#1B2422"/>

          <circle cx="36" cy="45" r="18" fill="#25A194" opacity="0.85"/>
          <path d="M20 66c0-9 7-15 16-15s16 6 16 15" fill="#1C8078"/>

          <rect x="66" y="26" width="132" height="8" rx="3" fill="#25A194" opacity="0.7"/>
          <rect x="66" y="42" width="108" height="8" rx="3" fill="#7C5CFF" opacity="0.55"/>
          <rect x="66" y="58" width="90" height="8" rx="3" fill="#ECEAE4" opacity="0.18"/>

          <rect x="18" y="88" width="60" height="36" rx="6" fill="#14201E" stroke="#25A194" stroke-opacity="0.3"/>
          <path d="M22 116l10-14 8 8 12-18 8 6" stroke="#25A194" stroke-width="2.2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>

          <rect x="86" y="88" width="112" height="36" rx="6" fill="#14201E" stroke="#7C5CFF" stroke-opacity="0.3"/>
          <circle cx="105" cy="106" r="12" fill="none" stroke="#7C5CFF" stroke-width="4" stroke-dasharray="50 100"/>
          <rect x="126" y="98" width="60" height="6" rx="3" fill="#ECEAE4" opacity="0.25"/>
          <rect x="126" y="110" width="40" height="6" rx="3" fill="#ECEAE4" opacity="0.15"/>
        </svg>

        <svg class="badge-check" width="56" height="56" viewBox="0 0 56 56" fill="none">
          <circle cx="28" cy="28" r="27" fill="#14201E" stroke="#25A194" stroke-opacity="0.5"/>
          <path d="M17 29l7 7 15-15" stroke="#25A194" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>

        <svg class="badge-bell" width="50" height="50" viewBox="0 0 50 50" fill="none">
          <circle cx="25" cy="25" r="24" fill="#161116" stroke="#7C5CFF" stroke-opacity="0.5"/>
          <path d="M25 14c-5 0-8 4-8 9v5l-3 4h22l-3-4v-5c0-5-3-9-8-9z" fill="#7C5CFF" opacity="0.8"/>
          <path d="M22 34a3 3 0 0 0 6 0" stroke="#7C5CFF" stroke-width="2" fill="none"/>
        </svg>

      </div>
    </div>
       
    <div class="card-wrap">
        
      <div class="card-border-glow"></div>
      <div class="card">
        <div class="logo"><img src="{{ asset('assets/images/logo-icon.png') }}" alt="EasySchool"></div>
        <h2>EasySchool</h2>
        <p class="sub">Welcome back — sign in to continue</p>
         @if (session('login_success_message'))
            <p class="alert" align="center" style="background:rgba(37,161,148,0.15);border:1px solid rgba(37,161,148,0.35);color:#5eead4;">{{ session('login_success_message') }}</p>
        @endif
         @if (session('login_error_message'))
            <p class="alert alert-danger" align="center">{{session('login_error_message')}}</p>
        @endif<br/>
       
        <form id="loginForm" enctype="multipart/form-data" action="{{ route('authentication-process', [], false) }}" method="POST">
           @csrf
          <div class="field">
            <label for="staffId">Staff / Student ID / Email</label>
            <div class="input-wrap">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#25A194" stroke-width="2">
                <circle cx="12" cy="8" r="4"/>
                <path d="M4 20c0-4 3.5-7 8-7s8 3 8 7"/>
              </svg>
              <input type="text" name="email" value="{{ old('email') }}" placeholder="Enter your Email" required>
               @error('email') <small style="color:red;">{{$message}}</small>@enderror
            </div>
          </div>

          <div class="field">
            <label for="password">Password</label>
            <div class="input-wrap">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#25A194" stroke-width="2">
                <rect x="5" y="11" width="14" height="9" rx="2"/>
                <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
              </svg>
              <input type="password"   name="password" placeholder="Enter your password" required>
              
              <button type="button" class="toggle-eye" id="toggleEye" aria-label="Show password">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ECEAE4" stroke-width="2" id="eyeIcon">
                  <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
               @error('password') <small style="color:red;">{{$message}}</small>@enderror
            </div>
          </div>

          <div class="row-between">
            <label class="remember">
              <input type="checkbox" name="remember"> Remember me
            </label>
            <a href="#">Forgot password?</a>
          </div>

          <button type="submit" class="submit">
            Sign In
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
              <path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </form>

         

        <div class="copyright">© 2026 EasySchool. All rights reserved.</div>
      </div>
    </div>

  </div>

<script>
  const toggleEye = document.getElementById('toggleEye');
  const passwordInput = document.getElementById('password');
  const eyeIcon = document.getElementById('eyeIcon');

  const eyeOpenPath = '<path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/>';
  const eyeClosedPath = '<path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a21.6 21.6 0 0 1 5.06-5.94M9.9 4.24A10.4 10.4 0 0 1 12 4c7 0 11 7 11 7a21.6 21.6 0 0 1-3.06 4.06M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';

  toggleEye.addEventListener('click', function () {
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';
    eyeIcon.innerHTML = isPassword ? eyeClosedPath : eyeOpenPath;
    toggleEye.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
  });
</script>
<script src="{{ asset('assets/js/lib/bootstrap.bundle.min.js') }}"></script>

</body>
</html>
