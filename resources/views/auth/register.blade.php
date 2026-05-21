<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legal Case Management — Create Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg-deep: #0d0b1a;
            --bg-card: rgba(255,255,255,0.04);
            --border: rgba(255,255,255,0.08);
            --purple-core: #7c3aed;
            --purple-light: #a855f7;
            --purple-glow: rgba(124,58,237,0.35);
            --text-primary: #f0ecff;
            --text-muted: rgba(240,236,255,0.45);
            --input-bg: rgba(255,255,255,0.05);
            --input-border: rgba(255,255,255,0.1);
            --input-focus: rgba(124,58,237,0.6);
            --error: #f87171;
        }
        html, body { height: 100%; font-family: 'DM Sans', sans-serif; background-color: var(--bg-deep); color: var(--text-primary); }
        .bg-scene { position: fixed; inset: 0; z-index: 0; background: radial-gradient(ellipse 80% 60% at 20% 10%, rgba(124,58,237,0.18) 0%, transparent 70%), radial-gradient(ellipse 60% 50% at 80% 80%, rgba(168,85,247,0.12) 0%, transparent 70%), #0d0b1a; }
        .bg-grid { position: fixed; inset: 0; z-index: 0; background-image: linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px); background-size: 48px 48px; mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 20%, transparent 100%); }
        .orb { position: fixed; border-radius: 50%; filter: blur(80px); z-index: 0; pointer-events: none; animation: drift 12s ease-in-out infinite alternate; }
        .orb-1 { width: 400px; height: 400px; background: rgba(124,58,237,0.12); top: -100px; left: -100px; }
        .orb-2 { width: 300px; height: 300px; background: rgba(168,85,247,0.10); bottom: -80px; right: -80px; animation-delay: -6s; }
        @keyframes drift { from { transform: translate(0,0) scale(1); } to { transform: translate(40px,30px) scale(1.08); } }
        .page { position: relative; z-index: 1; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 24px; }
        .card { width: 100%; max-width: 440px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 20px; padding: 44px 40px 36px; backdrop-filter: blur(24px); box-shadow: 0 0 0 1px rgba(255,255,255,0.04) inset, 0 32px 64px rgba(0,0,0,0.4), 0 0 80px var(--purple-glow); animation: rise 0.6s cubic-bezier(0.16,1,0.3,1) both; }
        @keyframes rise { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
        .logo-wrap { display: flex; justify-content: center; margin-bottom: 20px; }
        .logo-icon { width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(135deg, var(--purple-core), var(--purple-light)); display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(124,58,237,0.4), 0 0 0 1px rgba(255,255,255,0.1) inset; }
        .logo-icon svg { width: 28px; height: 28px; color: #fff; }
        .card-title { font-family: 'Cormorant Garamond', serif; font-size: 2rem; font-weight: 600; text-align: center; letter-spacing: -0.02em; color: var(--text-primary); margin-bottom: 6px; }
        .card-sub { text-align: center; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 20px; font-weight: 300; }
        .badge { display: flex; align-items: center; gap: 6px; background: rgba(124,58,237,0.15); border: 1px solid rgba(124,58,237,0.3); border-radius: 20px; padding: 4px 12px; font-size: 0.75rem; color: var(--purple-light); font-weight: 500; letter-spacing: 0.04em; text-transform: uppercase; width: fit-content; margin: 0 auto 24px; }
        .badge svg { width: 12px; height: 12px; }
        .field { margin-bottom: 16px; }
        label { display: block; font-size: 0.78rem; font-weight: 500; letter-spacing: 0.04em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px; }
        .input-wrap { position: relative; display: flex; align-items: center; }
        .input-icon { position: absolute; left: 14px; color: var(--text-muted); pointer-events: none; }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 12px 16px 12px 42px; background: var(--input-bg); border: 1px solid var(--input-border); border-radius: 10px; color: var(--text-primary); font-family: 'DM Sans', sans-serif; font-size: 0.92rem; outline: none; transition: border-color 0.2s, box-shadow 0.2s; }
        input::placeholder { color: var(--text-muted); }
        input:focus { border-color: var(--purple-core); box-shadow: 0 0 0 3px var(--input-focus); }
        .divider { height: 1px; background: var(--border); margin: 20px 0; }
        .terms-row { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 20px; }
        .terms-row input[type="checkbox"] { width: 16px; height: 16px; margin-top: 2px; accent-color: var(--purple-core); flex-shrink: 0; cursor: pointer; }
        .terms-text { font-size: 0.82rem; color: var(--text-muted); line-height: 1.5; }
        .terms-text a { color: var(--purple-light); text-decoration: none; }
        .terms-text a:hover { opacity: 0.7; }
        .btn-create { width: 100%; padding: 13px; background: linear-gradient(135deg, var(--purple-core), var(--purple-light)); border: none; border-radius: 10px; color: #fff; font-family: 'DM Sans', sans-serif; font-size: 0.95rem; font-weight: 500; cursor: pointer; box-shadow: 0 4px 20px rgba(124,58,237,0.4); transition: opacity 0.2s, transform 0.15s; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-create:hover { opacity: 0.9; transform: translateY(-1px); }
        .signin-link { text-align: center; margin-top: 20px; font-size: 0.82rem; color: var(--text-muted); }
        .signin-link a { color: var(--purple-light); text-decoration: none; font-weight: 500; }
        .signin-link a:hover { opacity: 0.7; }
        .error-box { background: rgba(248,113,113,0.08); border: 1px solid rgba(248,113,113,0.25); border-radius: 8px; padding: 10px 14px; font-size: 0.82rem; color: var(--error); margin-bottom: 18px; }
        .page-footer { margin-top: 28px; font-size: 0.75rem; color: rgba(255,255,255,0.2); text-align: center; }
    </style>
</head>
<body>
<div class="bg-scene"></div>
<div class="bg-grid"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="page">
    <div class="card">
        <div class="logo-wrap">
            <div class="logo-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                    <path d="M2 17l10 5 10-5"/>
                    <path d="M2 12l10 5 10-5"/>
                </svg>
            </div>
        </div>

        <h1 class="card-title">Create Account</h1>
        <p class="card-sub">Join our legal management platform</p>

        <div class="badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            Client Account
        </div>

        @if ($errors->any())
        <div class="error-box">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <input type="hidden" name="role" value="client">

            <div class="field">
                <label for="name">Full Name</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </span>
                    <input type="text" id="name" name="name" placeholder="John Doe" value="{{ old('name') }}" required autofocus>
                </div>
            </div>

            <div class="field">
                <label for="email">Email Address</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 4H4a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2z"/>
                            <path d="M22 6l-10 7L2 6"/>
                        </svg>
                    </span>
                    <input type="email" id="email" name="email" placeholder="you@example.com" value="{{ old('email') }}" required>
                </div>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                    </span>
                    <input type="password" id="password" name="password" placeholder="Min. 8 characters" required>
                </div>
            </div>

            <div class="field">
                <label for="password_confirmation">Confirm Password</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                    </span>
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repeat your password" required>
                </div>
            </div>

            <div class="divider"></div>

            <div class="terms-row">
                <input type="checkbox" id="terms" name="terms" required>
                <span class="terms-text">
                    I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.
                    My account will be reviewed before full activation.
                </span>
            </div>

            <button type="submit" class="btn-create">
                Create Account
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </button>
        </form>

        <div class="signin-link">
            Already have an account? <a href="{{ route('login') }}">Sign in instead</a>
        </div>
    </div>
    <p class="page-footer">© 2026 Legal Case Management. All rights reserved.</p>
</div>
</body>
</html>