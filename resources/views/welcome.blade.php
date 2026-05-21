<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legal Case Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg-deep: #0d0b1a;
            --bg-card: rgba(255,255,255,0.04);
            --bg-card-hover: rgba(255,255,255,0.07);
            --border: rgba(255,255,255,0.08);
            --border-hover: rgba(124,58,237,0.4);
            --purple-core: #7c3aed;
            --purple-light: #a855f7;
            --purple-glow: rgba(124,58,237,0.35);
            --text-primary: #f0ecff;
            --text-muted: rgba(240,236,255,0.45);
            --text-secondary: rgba(240,236,255,0.7);
        }
        html { scroll-behavior: smooth; }
        body { font-family: 'DM Sans', sans-serif; background-color: var(--bg-deep); color: var(--text-primary); overflow-x: hidden; }

        /* ── Background ── */
        .bg-scene { position: fixed; inset: 0; z-index: 0; background: radial-gradient(ellipse 80% 60% at 20% 10%, rgba(124,58,237,0.18) 0%, transparent 70%), radial-gradient(ellipse 60% 50% at 80% 80%, rgba(168,85,247,0.12) 0%, transparent 70%), #0d0b1a; pointer-events: none; }
        .bg-grid { position: fixed; inset: 0; z-index: 0; background-image: linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px); background-size: 48px 48px; mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 20%, transparent 100%); pointer-events: none; }
        .orb { position: fixed; border-radius: 50%; filter: blur(80px); z-index: 0; pointer-events: none; animation: drift 12s ease-in-out infinite alternate; }
        .orb-1 { width: 500px; height: 500px; background: rgba(124,58,237,0.1); top: -150px; left: -150px; }
        .orb-2 { width: 400px; height: 400px; background: rgba(168,85,247,0.08); bottom: -100px; right: -100px; animation-delay: -6s; }
        @keyframes drift { from { transform: translate(0,0) scale(1); } to { transform: translate(40px,30px) scale(1.08); } }

        /* ── Nav ── */
        nav { position: fixed; top: 0; left: 0; right: 0; z-index: 100; padding: 20px 60px; display: flex; align-items: center; justify-content: space-between; background: rgba(13,11,26,0.8); backdrop-filter: blur(20px); border-bottom: 1px solid var(--border); }
        .nav-logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .nav-logo-icon { width: 36px; height: 36px; border-radius: 9px; background: linear-gradient(135deg, var(--purple-core), var(--purple-light)); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(124,58,237,0.4); }
        .nav-logo-icon svg { width: 18px; height: 18px; color: #fff; }
        .nav-logo-text { font-family: 'Cormorant Garamond', serif; font-size: 1.1rem; font-weight: 600; color: var(--text-primary); }
        .nav-links { display: flex; align-items: center; gap: 32px; }
        .nav-links a { font-size: 0.85rem; color: var(--text-muted); text-decoration: none; transition: color 0.2s; }
        .nav-links a:hover { color: var(--text-primary); }
        .nav-actions { display: flex; align-items: center; gap: 12px; }
        .btn-outline { padding: 8px 20px; border: 1px solid var(--border); border-radius: 8px; color: var(--text-primary); font-family: 'DM Sans', sans-serif; font-size: 0.85rem; text-decoration: none; transition: border-color 0.2s, background 0.2s; }
        .btn-outline:hover { border-color: var(--purple-core); background: rgba(124,58,237,0.1); }
        .btn-primary { padding: 8px 20px; background: linear-gradient(135deg, var(--purple-core), var(--purple-light)); border: none; border-radius: 8px; color: #fff; font-family: 'DM Sans', sans-serif; font-size: 0.85rem; text-decoration: none; transition: opacity 0.2s, transform 0.15s; box-shadow: 0 4px 12px rgba(124,58,237,0.3); }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }

        /* ── Sections ── */
        section { position: relative; z-index: 1; }

        /* ── Hero ── */
        .hero { min-height: 100vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 120px 24px 80px; }
        .hero-inner { max-width: 760px; animation: rise 0.8s cubic-bezier(0.16,1,0.3,1) both; }
        @keyframes rise { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .hero-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(124,58,237,0.15); border: 1px solid rgba(124,58,237,0.3); border-radius: 20px; padding: 6px 16px; font-size: 0.78rem; color: var(--purple-light); font-weight: 500; letter-spacing: 0.06em; text-transform: uppercase; margin-bottom: 28px; }
        .hero-badge span { width: 6px; height: 6px; background: var(--purple-light); border-radius: 50%; animation: pulse 2s infinite; }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.3; } }
        .hero-title { font-family: 'Cormorant Garamond', serif; font-size: clamp(2.8rem, 6vw, 4.5rem); font-weight: 600; line-height: 1.1; letter-spacing: -0.02em; margin-bottom: 20px; }
        .hero-title span { background: linear-gradient(135deg, var(--purple-light), #c084fc); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .hero-desc { font-size: 1.05rem; color: var(--text-secondary); line-height: 1.7; max-width: 560px; margin: 0 auto 40px; font-weight: 300; }
        .hero-actions { display: flex; align-items: center; justify-content: center; gap: 16px; flex-wrap: wrap; }
        .btn-hero-primary { padding: 14px 32px; background: linear-gradient(135deg, var(--purple-core), var(--purple-light)); border: none; border-radius: 10px; color: #fff; font-family: 'DM Sans', sans-serif; font-size: 0.95rem; font-weight: 500; text-decoration: none; display: flex; align-items: center; gap: 8px; box-shadow: 0 8px 24px rgba(124,58,237,0.4); transition: opacity 0.2s, transform 0.15s; }
        .btn-hero-primary:hover { opacity: 0.9; transform: translateY(-2px); }
        .btn-hero-outline { padding: 14px 32px; border: 1px solid var(--border); border-radius: 10px; color: var(--text-primary); font-family: 'DM Sans', sans-serif; font-size: 0.95rem; text-decoration: none; display: flex; align-items: center; gap: 8px; transition: border-color 0.2s, background 0.2s; }
        .btn-hero-outline:hover { border-color: var(--purple-core); background: rgba(124,58,237,0.08); }
        .hero-stats { display: flex; align-items: center; justify-content: center; gap: 48px; margin-top: 64px; padding-top: 40px; border-top: 1px solid var(--border); flex-wrap: wrap; }
        .stat { text-align: center; }
        .stat-number { font-family: 'Cormorant Garamond', serif; font-size: 2.2rem; font-weight: 600; background: linear-gradient(135deg, var(--purple-light), #c084fc); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .stat-label { font-size: 0.8rem; color: var(--text-muted); margin-top: 4px; text-transform: uppercase; letter-spacing: 0.06em; }

        /* ── Section Header ── */
        .section-wrap { padding: 100px 60px; max-width: 1200px; margin: 0 auto; }
        .section-header { text-align: center; margin-bottom: 60px; }
        .section-tag { display: inline-block; font-size: 0.75rem; font-weight: 500; letter-spacing: 0.08em; text-transform: uppercase; color: var(--purple-light); margin-bottom: 12px; }
        .section-title { font-family: 'Cormorant Garamond', serif; font-size: clamp(1.8rem, 3vw, 2.6rem); font-weight: 600; letter-spacing: -0.02em; margin-bottom: 14px; }
        .section-desc { font-size: 0.95rem; color: var(--text-muted); max-width: 500px; margin: 0 auto; line-height: 1.7; font-weight: 300; }

        /* ── Features ── */
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
        .feature-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px; padding: 32px; transition: border-color 0.3s, background 0.3s, transform 0.3s; }
        .feature-card:hover { border-color: var(--border-hover); background: var(--bg-card-hover); transform: translateY(-4px); }
        .feature-icon { width: 48px; height: 48px; border-radius: 12px; background: rgba(124,58,237,0.15); border: 1px solid rgba(124,58,237,0.25); display: flex; align-items: center; justify-content: center; margin-bottom: 20px; }
        .feature-icon svg { width: 22px; height: 22px; color: var(--purple-light); }
        .feature-title { font-family: 'Cormorant Garamond', serif; font-size: 1.2rem; font-weight: 600; margin-bottom: 10px; }
        .feature-desc { font-size: 0.85rem; color: var(--text-muted); line-height: 1.7; font-weight: 300; }

        /* ── How It Works ── */
        .how-bg { background: rgba(124,58,237,0.03); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); }
        .steps-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; position: relative; }
        .step-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px; padding: 32px; text-align: center; transition: border-color 0.3s, transform 0.3s; }
        .step-card:hover { border-color: var(--border-hover); transform: translateY(-4px); }
        .step-number { width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, var(--purple-core), var(--purple-light)); display: flex; align-items: center; justify-content: center; font-family: 'Cormorant Garamond', serif; font-size: 1.2rem; font-weight: 600; color: #fff; margin: 0 auto 20px; box-shadow: 0 4px 16px rgba(124,58,237,0.4); }
        .step-title { font-family: 'Cormorant Garamond', serif; font-size: 1.15rem; font-weight: 600; margin-bottom: 10px; }
        .step-desc { font-size: 0.85rem; color: var(--text-muted); line-height: 1.7; font-weight: 300; }

        /* ── Services ── */
        .services-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
        .service-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 14px; padding: 28px 24px; display: flex; flex-direction: column; align-items: flex-start; gap: 14px; transition: border-color 0.3s, background 0.3s, transform 0.3s; }
        .service-card:hover { border-color: var(--border-hover); background: var(--bg-card-hover); transform: translateY(-4px); }
        .service-icon { width: 40px; height: 40px; border-radius: 10px; background: rgba(124,58,237,0.15); border: 1px solid rgba(124,58,237,0.2); display: flex; align-items: center; justify-content: center; }
        .service-icon svg { width: 18px; height: 18px; color: var(--purple-light); }
        .service-name { font-family: 'Cormorant Garamond', serif; font-size: 1.05rem; font-weight: 600; }
        .service-desc { font-size: 0.8rem; color: var(--text-muted); line-height: 1.6; font-weight: 300; }

        /* ── Rules ── */
        .rules-bg { background: rgba(124,58,237,0.03); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); }
        .rules-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; }
        .rule-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 14px; padding: 28px; display: flex; gap: 16px; align-items: flex-start; transition: border-color 0.3s, transform 0.3s; }
        .rule-card:hover { border-color: var(--border-hover); transform: translateY(-3px); }
        .rule-num { font-family: 'Cormorant Garamond', serif; font-size: 2rem; font-weight: 700; color: rgba(124,58,237,0.3); line-height: 1; flex-shrink: 0; }
        .rule-title { font-size: 0.9rem; font-weight: 500; margin-bottom: 6px; }
        .rule-desc { font-size: 0.82rem; color: var(--text-muted); line-height: 1.6; font-weight: 300; }

        /* ── CTA ── */
        .cta-wrap { text-align: center; }
        .cta-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 24px; padding: 80px 60px; position: relative; overflow: hidden; }
        .cta-card::before { content: ''; position: absolute; inset: 0; background: radial-gradient(ellipse 60% 60% at 50% 50%, rgba(124,58,237,0.12) 0%, transparent 70%); pointer-events: none; }
        .cta-title { font-family: 'Cormorant Garamond', serif; font-size: clamp(1.8rem, 3vw, 2.8rem); font-weight: 600; margin-bottom: 16px; position: relative; }
        .cta-desc { font-size: 0.95rem; color: var(--text-muted); max-width: 480px; margin: 0 auto 36px; line-height: 1.7; font-weight: 300; position: relative; }
        .cta-actions { display: flex; align-items: center; justify-content: center; gap: 16px; flex-wrap: wrap; position: relative; }

        /* ── Footer ── */
        footer { border-top: 1px solid var(--border); padding: 40px 60px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px; position: relative; z-index: 1; }
        .footer-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .footer-logo-icon { width: 28px; height: 28px; border-radius: 7px; background: linear-gradient(135deg, var(--purple-core), var(--purple-light)); display: flex; align-items: center; justify-content: center; }
        .footer-logo-icon svg { width: 14px; height: 14px; color: #fff; }
        .footer-logo-text { font-family: 'Cormorant Garamond', serif; font-size: 0.95rem; font-weight: 600; color: var(--text-primary); }
        .footer-copy { font-size: 0.78rem; color: var(--text-muted); }
        .footer-links { display: flex; gap: 24px; }
        .footer-links a { font-size: 0.82rem; color: var(--text-muted); text-decoration: none; transition: color 0.2s; }
        .footer-links a:hover { color: var(--purple-light); }

        @media (max-width: 768px) {
            nav { padding: 16px 24px; }
            .nav-links { display: none; }
            .section-wrap { padding: 70px 24px; }
            footer { padding: 32px 24px; flex-direction: column; text-align: center; }
            .cta-card { padding: 48px 24px; }
        }
    </style>
</head>
<body>

<div class="bg-scene"></div>
<div class="bg-grid"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<!-- Nav -->
<nav>
    <a href="/" class="nav-logo">
        <div class="nav-logo-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                <path d="M2 17l10 5 10-5"/>
                <path d="M2 12l10 5 10-5"/>
            </svg>
        </div>
        <span class="nav-logo-text">LegalCase</span>
    </a>
    <div class="nav-links">
        <a href="#features">Features</a>
        <a href="#how-it-works">How It Works</a>
        <a href="#services">Services</a>
        <a href="#rules">Guidelines</a>
    </div>
    <div class="nav-actions">
        <a href="{{ route('login') }}" class="btn-outline">Sign In</a>
        <a href="{{ route('register') }}" class="btn-primary">Get Started</a>
    </div>
</nav>

<!-- Hero -->
<section class="hero">
    <div class="hero-inner">
        <div class="hero-badge">
            <span></span>
            Professional Legal Management Platform
        </div>
        <h1 class="hero-title">
            Your Legal Cases,<br>
            <span>Managed with Precision</span>
        </h1>
        <p class="hero-desc">
            A secure, professional platform connecting clients with experienced lawyers.
            Track your cases, manage documents, and communicate — all in one place.
        </p>
        <div class="hero-actions">
            <a href="{{ route('register') }}" class="btn-hero-primary">
                Get Started Free
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
            <a href="{{ route('login') }}" class="btn-hero-outline">
                Sign In to Dashboard
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            </a>
        </div>
        <div class="hero-stats">
            <div class="stat">
                <div class="stat-number">500+</div>
                <div class="stat-label">Cases Resolved</div>
            </div>
            <div class="stat">
                <div class="stat-number">98%</div>
                <div class="stat-label">Client Satisfaction</div>
            </div>
            <div class="stat">
                <div class="stat-number">50+</div>
                <div class="stat-label">Expert Lawyers</div>
            </div>
            <div class="stat">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Platform Access</div>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section id="features">
    <div class="section-wrap">
        <div class="section-header">
            <div class="section-tag">Platform Features</div>
            <h2 class="section-title">Everything You Need in One Place</h2>
            <p class="section-desc">Powerful tools designed to make legal case management seamless, transparent, and efficient.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 3H8a2 2 0 00-2 2v2h12V5a2 2 0 00-2-2z"/></svg>
                </div>
                <div class="feature-title">Case Management</div>
                <div class="feature-desc">Create, track, and manage legal cases from start to finish. Monitor status updates, court dates, and case progress in real time.</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
                <div class="feature-title">Document Storage</div>
                <div class="feature-desc">Securely upload, store, and version-track legal documents. Share files between lawyers and clients with controlled access permissions.</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                </div>
                <div class="feature-title">Billing & Invoicing</div>
                <div class="feature-desc">Track billable hours, generate professional invoices, and monitor payment status. Full transparency between lawyers and clients.</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                </div>
                <div class="feature-title">Secure Messaging</div>
                <div class="feature-desc">Communicate directly with your lawyer through encrypted case-specific messaging. Stay informed with real-time updates.</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div class="feature-title">Schedule & Deadlines</div>
                <div class="feature-desc">Never miss a court date or deadline. Manage hearings, appointments, and important milestones with an integrated calendar.</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div class="feature-title">Role-Based Access</div>
                <div class="feature-desc">Secure, role-specific dashboards for Admins, Lawyers, and Clients. Each user sees only what they need — nothing more.</div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section id="how-it-works" class="how-bg">
    <div class="section-wrap">
        <div class="section-header">
            <div class="section-tag">How It Works</div>
            <h2 class="section-title">Get Started in Three Simple Steps</h2>
            <p class="section-desc">From registration to full case management — the process is simple, secure, and transparent.</p>
        </div>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <div class="step-title">Create Your Account</div>
                <div class="step-desc">Register as a client in minutes. Your account is reviewed and activated by our admin team to ensure platform security.</div>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <div class="step-title">Get Assigned a Lawyer</div>
                <div class="step-desc">Our admin matches you with an experienced lawyer based on your case type. You'll be notified once your case is opened.</div>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <div class="step-title">Track Your Case</div>
                <div class="step-desc">Monitor progress, upload documents, communicate with your lawyer, view invoices, and book appointments — all from your dashboard.</div>
            </div>
        </div>
    </div>
</section>

<!-- Services -->
<section id="services">
    <div class="section-wrap">
        <div class="section-header">
            <div class="section-tag">Our Services</div>
            <h2 class="section-title">Legal Expertise Across Every Area</h2>
            <p class="section-desc">Our platform supports a wide range of legal practice areas handled by qualified professionals.</p>
        </div>
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div class="service-name">Criminal Law</div>
                <div class="service-desc">Defense and prosecution representation for criminal offenses and proceedings.</div>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                </div>
                <div class="service-name">Civil Law</div>
                <div class="service-desc">Resolution of disputes between individuals, organizations, and institutions.</div>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <div class="service-name">Family Law</div>
                <div class="service-desc">Divorce, custody, adoption, and all domestic relations legal matters.</div>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
                </div>
                <div class="service-name">Corporate Law</div>
                <div class="service-desc">Business formation, contracts, mergers, and commercial legal advisory.</div>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><circle cx="12" cy="12" r="2"/></svg>
                </div>
                <div class="service-name">Labor Law</div>
                <div class="service-desc">Employment disputes, workplace rights, and labor relations advocacy.</div>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                </div>
                <div class="service-name">Real Estate Law</div>
                <div class="service-desc">Property acquisition, land disputes, titles, and real estate transactions.</div>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 010 20M12 2a15.3 15.3 0 000 20"/></svg>
                </div>
                <div class="service-name">Immigration Law</div>
                <div class="service-desc">Visa applications, citizenship, and immigration compliance assistance.</div>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
                <div class="service-name">Tax Law</div>
                <div class="service-desc">Tax planning, compliance, and dispute resolution with government agencies.</div>
            </div>
        </div>
    </div>
</section>

<!-- Rules & Guidelines -->
<section id="rules" class="rules-bg">
    <div class="section-wrap">
        <div class="section-header">
            <div class="section-tag">Platform Guidelines</div>
            <h2 class="section-title">Rules & Regulations</h2>
            <p class="section-desc">To maintain a professional and secure environment, all users must adhere to the following guidelines.</p>
        </div>
        <div class="rules-grid">
            <div class="rule-card">
                <div class="rule-num">01</div>
                <div>
                    <div class="rule-title">Confidentiality</div>
                    <div class="rule-desc">All case information, documents, and communications shared on this platform are strictly confidential and protected by attorney-client privilege.</div>
                </div>
            </div>
            <div class="rule-card">
                <div class="rule-num">02</div>
                <div>
                    <div class="rule-title">Accurate Information</div>
                    <div class="rule-desc">Users must provide truthful and accurate information at all times. Providing false information may result in account suspension or legal consequences.</div>
                </div>
            </div>
            <div class="rule-card">
                <div class="rule-num">03</div>
                <div>
                    <div class="rule-title">Respectful Communication</div>
                    <div class="rule-desc">All interactions between clients, lawyers, and administrators must remain professional and respectful at all times.</div>
                </div>
            </div>
            <div class="rule-card">
                <div class="rule-num">04</div>
                <div>
                    <div class="rule-title">Document Integrity</div>
                    <div class="rule-desc">Uploaded documents must be authentic and unaltered. Submission of falsified documents is strictly prohibited and may be reported to authorities.</div>
                </div>
            </div>
            <div class="rule-card">
                <div class="rule-num">05</div>
                <div>
                    <div class="rule-title">Timely Response</div>
                    <div class="rule-desc">Clients are expected to respond promptly to lawyer requests and upload required documents within the specified deadlines.</div>
                </div>
            </div>
            <div class="rule-card">
                <div class="rule-num">06</div>
                <div>
                    <div class="rule-title">Payment Obligations</div>
                    <div class="rule-desc">Clients are responsible for settling invoices within the agreed payment terms. Delays may affect case proceedings.</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section>
    <div class="section-wrap">
        <div class="cta-wrap">
            <div class="cta-card">
                <h2 class="cta-title">Ready to Get Started?</h2>
                <p class="cta-desc">Join our platform today and connect with experienced legal professionals who will guide you through every step of your case.</p>
                <div class="cta-actions">
                    <a href="{{ route('register') }}" class="btn-hero-primary">
                        Create Free Account
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('login') }}" class="btn-hero-outline">Sign In Instead</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer>
    <a href="/" class="footer-logo">
        <div class="footer-logo-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                <path d="M2 17l10 5 10-5"/>
                <path d="M2 12l10 5 10-5"/>
            </svg>
        </div>
        <span class="footer-logo-text">LegalCase</span>
    </a>
    <div class="footer-links">
        <a href="#features">Features</a>
        <a href="#services">Services</a>
        <a href="#rules">Guidelines</a>
        <a href="{{ route('login') }}">Sign In</a>
        <a href="{{ route('register') }}">Register</a>
    </div>
    <p class="footer-copy">© 2026 Legal Case Management. All rights reserved.</p>
</footer>

</body>
</html>