<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LegalCase | Legal Case Management</title>
    <style>
        :root {
            color-scheme: dark;
            color: #ffffff;
            background-color: #090713;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #090713;
            color: #e2e8f0;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .gradient-circle {
            position: fixed;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            background: radial-gradient(circle at top left, rgba(139, 92, 246, 0.22), transparent 22%),
                        radial-gradient(circle at 80% 16%, rgba(124, 58, 237, 0.16), transparent 20%),
                        radial-gradient(circle at bottom, rgba(59, 130, 246, 0.12), transparent 18%);
        }

        .glass-card {
            background: rgba(15, 23, 42, 0.78);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(18px);
        }

        .nav-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 32px 24px 0;
            max-width: 1240px;
            margin: 0 auto;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 14px;
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 16px;
            background: rgba(124, 58, 237, 0.16);
            color: #ede9fe;
            font-weight: 700;
            font-size: 1rem;
        }

        .brand-label {
            display: grid;
            gap: 2px;
            line-height: 1;
        }

        .brand-label strong {
            font-size: 1rem;
        }

        .brand-label small {
            color: #94a3b8;
        }

        .hero {
            position: relative;
            overflow: hidden;
            padding: 96px 24px 72px;
            max-width: 1240px;
            margin: 0 auto;
        }

        .hero-grid {
            display: grid;
            gap: 48px;
        }

        .hero-heading {
            margin: 0;
            font-size: clamp(2.8rem, 5vw, 4.8rem);
            line-height: 0.96;
            max-width: 720px;
        }

        .hero-copy {
            margin-top: 24px;
            max-width: 660px;
            color: #cbd5e1;
            line-height: 1.8;
            font-size: 1.05rem;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-top: 32px;
        }

        .button-primary,
        .button-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 16px 24px;
            border-radius: 999px;
            border: 1px solid transparent;
            font-weight: 600;
            transition: transform 160ms ease, background-color 160ms ease, border-color 160ms ease;
            cursor: pointer;
        }

        .button-primary {
            background: #7c3aed;
            color: #ffffff;
        }

        .button-primary:hover {
            background: #8b5cf6;
            transform: translateY(-1px);
        }

        .button-secondary {
            background: rgba(255, 255, 255, 0.04);
            color: #e2e8f0;
            border-color: rgba(148, 163, 184, 0.16);
        }

        .button-secondary:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-1px);
        }

        .hero-panel {
            display: grid;
            gap: 22px;
            margin-top: 40px;
            padding: 32px;
            border-radius: 32px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(15, 23, 42, 0.76);
            backdrop-filter: blur(22px);
        }

        .hero-panel-item {
            padding: 26px;
            border-radius: 26px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .hero-panel-item h3 {
            margin: 0;
            font-size: 1.4rem;
            color: #ffffff;
        }

        .hero-panel-item p {
            margin: 12px 0 0;
            color: #94a3b8;
            line-height: 1.8;
        }

        .section {
            padding: 72px 24px;
            max-width: 1240px;
            margin: 0 auto;
        }

        .section-alt {
            background: rgba(255, 255, 255, 0.02);
        }

        .section-header {
            display: grid;
            gap: 18px;
            max-width: 660px;
        }

        .section-tag {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.6rem 1rem;
            border-radius: 999px;
            background: rgba(124, 58, 237, 0.16);
            color: #d8b4fe;
            font-size: 0.8rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            font-weight: 700;
        }

        .section-title {
            margin: 0;
            font-size: clamp(2.2rem, 4vw, 3rem);
            line-height: 1.05;
            color: #f8fafc;
        }

        .section-copy {
            margin: 0;
            color: #cbd5e1;
            line-height: 1.8;
            font-size: 1.03rem;
        }

        .feature-grid,
        .three-grid {
            display: grid;
            gap: 24px;
        }

        .feature-card,
        .step-card {
            padding: 32px;
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            background: rgba(15, 23, 42, 0.74);
            backdrop-filter: blur(16px);
        }

        .feature-card h3,
        .step-card h3 {
            margin: 18px 0 0;
            color: #ffffff;
            font-size: 1.5rem;
        }

        .feature-card p,
        .step-card p {
            margin: 0;
            color: #94a3b8;
            line-height: 1.8;
        }

        .feature-label,
        .step-number {
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #a78bfa;
        }

        .step-number {
            width: 52px;
            height: 52px;
            display: grid;
            place-items: center;
            border-radius: 1rem;
            background: rgba(124, 58, 237, 0.18);
            font-size: 1.1rem;
            color: #ede9fe;
        }

        .footer {
            padding: 48px 24px 32px;
            background: rgba(255, 255, 255, 0.02);
        }

        .footer-top {
            display: grid;
            gap: 32px;
            max-width: 1240px;
            margin: 0 auto;
        }

        .footer-brand {
            display: grid;
            gap: 16px;
            max-width: 520px;
        }

        .footer-links {
            display: grid;
            gap: 12px;
        }

        .footer-links a {
            color: #94a3b8;
            transition: color 160ms ease;
        }

        .footer-links a:hover {
            color: #ffffff;
        }

        .copy-row {
            margin-top: 28px;
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            justify-content: space-between;
            color: #94a3b8;
            font-size: 0.92rem;
        }

        @media (min-width: 768px) {
            .hero-grid {
                grid-template-columns: 1.2fr 0.8fr;
                align-items: center;
            }
            .feature-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .three-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .footer-top {
                grid-template-columns: 1.4fr 0.8fr;
            }
        }

        @media (min-width: 1024px) {
            .nav-wrap {
                padding: 40px 32px 0;
            }
            .hero {
                gap: 72px;
            }
            .section {
                padding-top: 96px;
                padding-bottom: 96px;
            }
        }
    </style>
</head>
<body>
    <div class="gradient-circle"></div>
    <header>
        <div class="nav-wrap">
            <a href="/" class="brand">
                <span class="brand-mark">LC</span>
                <span class="brand-label">
                    <strong>LegalCase</strong>
                    <small>Legal case management</small>
                </span>
            </a>
            <div style="display: flex; gap: 14px; align-items: center;">
                <a href="/login" class="button-secondary">Login</a>
                <a href="#features" class="button-primary">Learn More</a>
            </div>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="hero-grid">
                <div>
                    <span class="section-tag">Philippine legal workflow</span>
                    <h1 class="hero-heading">Modern legal case management for lawyers, clients, and compliant billing.</h1>
                    <p class="hero-copy">LegalCase unifies case tracking, secure document sharing, invoices, appointments, and messaging into one polished platform designed for Philippine law practices.</p>
                    <div class="hero-actions">
                        <a href="/login" class="button-primary">Login to Your Account</a>
                        <a href="#how-it-works" class="button-secondary">See How It Works</a>
                    </div>
                    <div class="hero-panel">
                        <div class="hero-panel-item">
                            <h3>Secure case dashboard</h3>
                            <p>Monitor case status, deadlines, and client communication from one secure home screen.</p>
                        </div>
                        <div class="hero-panel-item">
                            <h3>Compliant invoices</h3>
                            <p>Generate invoices and official receipt numbers while preserving payment transparency.</p>
                        </div>
                        <div class="hero-panel-item">
                            <h3>Client collaboration</h3>
                            <p>Share documents, messages, and schedules directly within each case file.</p>
                        </div>
                    </div>
                </div>
                <div style="position: relative;">
                    <div style="position:absolute; inset: 0; background: radial-gradient(circle at top left, rgba(124, 58, 237, 0.22), transparent 32%); filter: blur(60px);"></div>
                    <div style="position: relative; border-radius: 2rem; overflow: hidden; border: 1px solid rgba(255,255,255,0.08); background: rgba(15, 23, 42, 0.95); box-shadow: 0 40px 120px rgba(15, 23, 42, 0.45);">
                        <div style="padding: 32px;">
                            <div style="display: flex; justify-content: space-between; gap: 18px; align-items: flex-start; margin-bottom: 28px;">
                                <div>
                                    <p style="text-transform: uppercase; letter-spacing: 0.18em; font-size: 0.8rem; color: #94a3b8; margin: 0;">Client portal</p>
                                    <h2 style="margin: 10px 0 0; font-size: 2rem; color: #ffffff;">Secure case dashboard</h2>
                                </div>
                                <span style="padding: 10px 16px; border-radius: 18px; background: rgba(124, 58, 237, 0.2); color: #ede9fe; font-size: 0.85rem;">Live</span>
                            </div>
                            <div style="display: grid; gap: 18px;">
                                <div style="background: rgba(255,255,255,0.04); padding: 22px; border-radius: 2rem; border: 1px solid rgba(255,255,255,0.06);">
                                    <p style="margin:0; color:#94a3b8;">Open cases</p>
                                    <p style="margin:12px 0 0; font-size:1.8rem; font-weight:700; color:#fff;">42</p>
                                </div>
                                <div style="background: rgba(255,255,255,0.04); padding: 22px; border-radius: 2rem; border: 1px solid rgba(255,255,255,0.06);">
                                    <p style="margin:0; color:#94a3b8;">Invoices paid</p>
                                    <p style="margin:12px 0 0; font-size:1.8rem; font-weight:700; color:#fff;">128</p>
                                </div>
                                <div style="background: rgba(255,255,255,0.04); padding: 22px; border-radius: 2rem; border: 1px solid rgba(255,255,255,0.06);">
                                    <p style="margin:0; color:#94a3b8;">Appointments</p>
                                    <p style="margin:12px 0 0; font-size:1.8rem; font-weight:700; color:#fff;">17</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="features" class="section section-alt">
            <div class="section-header">
                <span class="section-tag">Key Features</span>
                <h2 class="section-title">Everything your legal team needs in one platform.</h2>
                <p class="section-copy">Streamline case intake, documents, billing, communications, and court scheduling with a unified legal management system.</p>
            </div>
            <div class="feature-grid" style="margin-top: 40px; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
                <article class="feature-card">
                    <div class="feature-label">Case management</div>
                    <h3>Centralized matter tracking</h3>
                    <p>Keep every case update, status change, and filing record in one place.</p>
                </article>
                <article class="feature-card">
                    <div class="feature-label">Billing</div>
                    <h3>Compliant invoices and receipts</h3>
                    <p>Generate invoices, monitor payments, and preserve Philippine receipt compliance data.</p>
                </article>
                <article class="feature-card">
                    <div class="feature-label">Documents</div>
                    <h3>Secure file management</h3>
                    <p>Upload, share, and version documents while keeping access under control.</p>
                </article>
                <article class="feature-card">
                    <div class="feature-label">Messaging</div>
                    <h3>Secure client communication</h3>
                    <p>Keep case conversations private and directly linked to the correct matter.</p>
                </article>
                <article class="feature-card">
                    <div class="feature-label">Calendar</div>
                    <h3>Appointments and deadlines</h3>
                    <p>Schedule hearings and consultations with clear reminders for every milestone.</p>
                </article>
                <article class="feature-card">
                    <div class="feature-label">Access control</div>
                    <h3>Role-based security</h3>
                    <p>Deliver tailored dashboards for clients, lawyers, and administrators.</p>
                </article>
            </div>
        </section>

        <section id="how-it-works" class="section">
            <div class="section-header">
                <span class="section-tag">How it works</span>
                <h2 class="section-title">A clear workflow from intake to case resolution.</h2>
                <p class="section-copy">Clients and lawyers work together through registration, collaboration, document submission, and payment tracking.</p>
            </div>
            <div class="three-grid" style="margin-top: 40px; gap: 24px;">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h3>Create your account</h3>
                    <p>Clients sign up and lawyers access their secure dashboards to begin case work.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h3>Collaborate on the case</h3>
                    <p>Share documents, send messages, and coordinate scheduling all in one place.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h3>Track progress and payments</h3>
                    <p>Monitor invoices, receipts, appointments, and case milestones until closure.</p>
                </div>
            </div>
        </section>

        <footer class="footer">
            <div class="footer-top">
                <div class="footer-brand">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <span class="brand-mark">LC</span>
                        <div>
                            <strong style="display:block;">LegalCase</strong>
                            <small>Legal case management</small>
                        </div>
                    </div>
                    <p style="margin: 0; color: #cbd5e1; line-height: 1.8;">A secure case management platform for law firms and clients across the Philippines. Work faster, stay compliant, and keep every interaction documented.</p>
                </div>
                <div class="footer-links">
                    <a href="/login">Login</a>
                    <a href="#features">Features</a>
                    <a href="#how-it-works">How it works</a>
                    <a href="#">Privacy</a>
                </div>
            </div>
            <div class="copy-row">
                <p>© 2026 Legal Case Management System™</p>
                <p>Built for legal professionals and clients.</p>
            </div>
        </footer>
    </main>
</body>
</html>
