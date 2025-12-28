<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sarder Solutions | The No-Nonsense CRM</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        /* --- SHARED CRM VARIABLES --- */
        :root { 
            --bg-body: #ffffff;
            --text-main: #000000;
            --accent: #b084fc; /* Purple */
            --accent-green: #4ade80;
            --accent-yellow: #facc15;
            --border-width: 2px;
            --radius: 8px;
            --shadow-hard: 4px 4px 0 #000000;
            --shadow-hover: 6px 6px 0 #000000;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-body); 
            background-image: radial-gradient(#e5e7eb 1px, transparent 1px);
            background-size: 24px 24px;
            color: var(--text-main);
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6, .brand-font, .mono-font {
            font-family: 'Space Mono', monospace;
            font-weight: 700;
            letter-spacing: -0.03em;
        }

        /* --- NAVBAR --- */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            border-bottom: var(--border-width) solid #000;
            padding: 20px 0;
            z-index: 1000;
        }

        .btn-nav {
            background: #fff;
            color: #000;
            border: var(--border-width) solid #000;
            box-shadow: 3px 3px 0 #000;
            font-weight: 700;
            border-radius: var(--radius);
            transition: 0.1s;
        }
        .btn-nav:hover {
            transform: translate(-2px, -2px);
            box-shadow: 5px 5px 0 #000;
            background: var(--accent);
        }

        /* --- HERO --- */
        .hero-section {
            padding: 100px 0 60px;
        }

        .hero-badge {
            background: var(--accent-green);
            border: var(--border-width) solid #000;
            padding: 8px 16px;
            display: inline-block;
            margin-bottom: 20px;
            box-shadow: 3px 3px 0 #000;
            font-family: 'Space Mono', monospace;
            font-size: 0.9rem;
        }

        .hero-title {
            font-size: 3.5rem;
            line-height: 1.1;
            margin-bottom: 24px;
        }

        /* --- BROWSER MOCKUP (Showing your Dashboard UI) --- */
        .browser-mockup {
            border: var(--border-width) solid #000;
            border-radius: var(--radius);
            background: #fff;
            box-shadow: 8px 8px 0 #000;
            overflow: hidden;
            margin-top: 40px;
        }
        .browser-header {
            background: #f3f4f6;
            border-bottom: var(--border-width) solid #000;
            padding: 12px 20px;
            display: flex;
            gap: 8px;
        }
        .dot { width: 12px; height: 12px; border-radius: 50%; border: 2px solid #000; }
        .dot.red { background: #ff4d4d; }
        .dot.yellow { background: #facc15; }
        .dot.green { background: #4ade80; }

        .mockup-body {
            padding: 0;
            display: flex;
            min-height: 400px;
        }
        .mockup-sidebar {
            width: 200px;
            border-right: var(--border-width) solid #000;
            padding: 20px;
            background: #fff;
        }
        .mockup-content {
            flex: 1;
            padding: 20px;
            background: #f8f9fa;
        }
        
        /* Simulated UI Elements */
        .fake-nav-item {
            height: 30px;
            margin-bottom: 10px;
            border: 2px solid transparent;
            border-radius: 6px;
            display: flex; align-items: center; gap: 10px;
            padding: 0 10px;
            font-family: 'Space Mono'; font-size: 0.8rem;
        }
        .fake-nav-item.active { background: var(--accent); border-color: #000; box-shadow: 2px 2px 0 #000; }
        
        .fake-card {
            background: #fff; border: 2px solid #000; border-radius: 6px; 
            padding: 15px; box-shadow: 4px 4px 0 #000; margin-bottom: 20px;
        }

        /* --- FEATURES --- */
        .feature-box {
            background: #fff;
            border: var(--border-width) solid #000;
            border-radius: var(--radius);
            padding: 30px;
            height: 100%;
            transition: 0.2s;
        }
        .feature-box:hover {
            transform: translate(-3px, -3px);
            box-shadow: 6px 6px 0 #000;
        }
        .feature-icon {
            width: 50px; height: 50px;
            background: var(--accent);
            border: 2px solid #000;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 20px;
            box-shadow: 3px 3px 0 #000;
            font-size: 1.5rem;
        }

        /* --- FORM --- */
        .lead-form-card {
            background: #fff;
            border: var(--border-width) solid #000;
            border-radius: var(--radius);
            padding: 40px;
            box-shadow: 8px 8px 0 #000;
        }
        .form-control, .form-select {
            border: 2px solid #000;
            border-radius: 6px;
            padding: 12px;
            font-weight: 500;
            box-shadow: 3px 3px 0 transparent;
            transition: 0.2s;
        }
        .form-control:focus {
            border-color: #000;
            box-shadow: 4px 4px 0 #000;
            transform: translate(-2px, -2px);
        }
        .btn-cta {
            background: #000; color: #fff;
            width: 100%; padding: 15px;
            font-weight: 700; text-transform: uppercase;
            border: 2px solid #000;
            box-shadow: 5px 5px 0 var(--accent);
            transition: 0.1s;
        }
        .btn-cta:hover {
            transform: translate(-2px, -2px);
            box-shadow: 7px 7px 0 var(--accent);
            background: #222; color: #fff;
        }

        /* --- FOOTER --- */
        footer {
            border-top: 2px solid #000;
            background: #fff;
            padding: 60px 0;
            margin-top: 100px;
        }

        @media(max-width: 768px) {
            .hero-title { font-size: 2.5rem; }
            .mockup-sidebar { display: none; }
        }
    </style>
</head>
<body>

    <nav class="navbar fixed-top">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand brand-font fs-4 d-flex align-items-center gap-2" href="#">
                <i class="bi bi-box-seam-fill"></i> FrequentCRM
            </a>
            <div>
                <a href="#features" class="text-decoration-none text-dark fw-bold me-4 d-none d-md-inline">Features</a>
                <a href="login.php" class="btn btn-nav">
                    LOGIN <i class="bi bi-arrow-right-short"></i>
                </a>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-badge">
                        <i class="bi bi-rocket-takeoff-fill"></i> v2.5 DEPLOYED
                    </div>
                    <h1 class="hero-title">
                        Stop managing sales<br>in spreadsheets.
                    </h1>
                    <p class="lead mb-4" style="max-width: 500px;">
                        The CRM built for speed. Pipeline tracking, lead scoring, and team management without the enterprise bloat.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#get-access" class="btn btn-dark border-2 border-dark rounded fw-bold px-4 py-3 shadow-lg" style="box-shadow: 4px 4px 0 var(--accent) !important;">
                            GET ACCESS
                        </a>
                        <a href="public_api.php" class="btn btn-outline-dark border-2 border-dark rounded fw-bold px-4 py-3" style="box-shadow: 4px 4px 0 #000 !important;">
                            API DOCS
                        </a>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="browser-mockup">
                        <div class="browser-header">
                            <div class="dot red"></div><div class="dot yellow"></div><div class="dot green"></div>
                            <div class="ms-2 font-monospace small text-muted">dashboard.php</div>
                        </div>
                        <div class="mockup-body">
                            <div class="mockup-sidebar">
                                <div class="fake-nav-item active"><i class="bi bi-grid-fill"></i> Dashboard</div>
                                <div class="fake-nav-item"><i class="bi bi-people-fill"></i> Customers</div>
                                <div class="fake-nav-item"><i class="bi bi-bar-chart-fill"></i> Pipeline</div>
                                <div class="fake-nav-item"><i class="bi bi-check-square-fill"></i> Tasks</div>
                            </div>
                            <div class="mockup-content">
                                <div class="d-flex gap-3 mb-3">
                                    <div class="fake-card w-50">
                                        <small class="text-muted fw-bold">REVENUE</small>
                                        <div class="fs-2 fw-bold mono-font text-success">$14,250</div>
                                    </div>
                                    <div class="fake-card w-50">
                                        <small class="text-muted fw-bold">CUSTOMERS</small>
                                        <div class="fs-2 fw-bold mono-font">342</div>
                                    </div>
                                </div>
                                <div class="fake-card">
                                    <div class="d-flex justify-content-between border-bottom border-dark pb-2 mb-2">
                                        <span class="fw-bold mono-font">ACTIVE DEALS</span>
                                        <span class="badge bg-warning text-dark border border-dark rounded-pill">KANBAN</span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <div class="bg-white border border-dark p-2 w-25 small text-center">Lead</div>
                                        <div class="bg-white border border-dark p-2 w-25 small text-center">Proposal</div>
                                        <div class="bg-white border border-dark p-2 w-25 small text-center">Negot.</div>
                                        <div class="bg-white border border-dark p-2 w-25 small text-center">Closed</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8">
                    <h2 class="display-5 fw-bold">Core Modules</h2>
                    <p class="lead">Everything you see in the code, available out of the box.</p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon"><i class="bi bi-people-fill"></i></div>
                        <h4>Customer 360°</h4>
                        <p>Complete view of interactions. Emails, Tasks, Deals, and Files all linked to a single profile. Includes <b>Smart Lead Scoring</b>.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon" style="background:var(--accent-yellow)"><i class="bi bi-kanban"></i></div>
                        <h4>Visual Pipeline</h4>
                        <p>Drag-and-drop Kanban board to move deals from Lead to Closed. Automatically updates revenue forecasts.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon" style="background:var(--accent-green)"><i class="bi bi-shield-lock-fill"></i></div>
                        <h4>Role-Based Access</h4>
                        <p>Granular permissions for Admins, Managers, and Sales Reps. Reps only see their assigned data.</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="feature-box">
                        <div class="feature-icon" style="background:#fff"><i class="bi bi-search"></i></div>
                        <h4>Global Search</h4>
                        <p>Instantly find any Customer, Deal, Task, or Product across the entire database with a single query.</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="feature-box">
                        <div class="feature-icon" style="background:#ff9999"><i class="bi bi-clock-history"></i></div>
                        <h4>Audit Trails</h4>
                        <p>Track every change. See exactly who changed a deal value or updated a customer status and when.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="get-access" class="py-5 bg-light border-top border-bottom border-dark border-2">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-5 mb-5 mb-lg-0">
                    <h2 class="display-4 fw-bold mb-3">Initialize Sequence.</h2>
                    <p class="lead mb-4">
                        Fill out the form to create a lead directly in our system (Yes, this connects to the <code>customers</code> table).
                    </p>
                    <ul class="list-unstyled">
                        <li class="mb-3 d-flex align-items-center gap-3">
                            <i class="bi bi-check-circle-fill fs-4 text-success"></i>
                            <span class="fw-bold">Free Setup</span>
                        </li>
                        <li class="mb-3 d-flex align-items-center gap-3">
                            <i class="bi bi-check-circle-fill fs-4 text-success"></i>
                            <span class="fw-bold">Unlimited Users</span>
                        </li>
                        <li class="mb-3 d-flex align-items-center gap-3">
                            <i class="bi bi-check-circle-fill fs-4 text-success"></i>
                            <span class="fw-bold">Self-Hosted</span>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-6 offset-lg-1">
                    <div class="lead-form-card">
                        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-dark pb-3">
                            <h4 class="m-0">NEW ACCOUNT</h4>
                            <div class="dot red"></div>
                        </div>

                        <form id="landingForm">
                            <input type="hidden" name="source" value="Landing Page">
                            <input type="hidden" name="status" value="Lead">
                            
                            <input type="hidden" name="potential_value" value="1000"> 

                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="fw-bold small">FIRST NAME</label>
                                    <input type="text" name="first_name" class="form-control" placeholder="Jane" required>
                                </div>
                                <div class="col-6">
                                    <label class="fw-bold small">LAST NAME</label>
                                    <input type="text" name="last_name" class="form-control" placeholder="Doe" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold small">EMAIL ADDRESS</label>
                                <input type="email" name="email" class="form-control" placeholder="jane@example.com" required>
                            </div>

                            <div class="mb-4">
                                <label class="fw-bold small">COMPANY</label>
                                <input type="text" name="company" class="form-control" placeholder="Acme Inc.">
                            </div>

                            <button type="submit" class="btn btn-cta">
                                <span id="btnText">SUBMIT REQUEST</span>
                                <div id="btnLoader" class="spinner-border spinner-border-sm ms-2" style="display:none;"></div>
                            </button>
                            
                            <div id="msgSuccess" class="mt-3 p-3 bg-success text-white border border-dark rounded fw-bold" style="display:none;">
                                <i class="bi bi-check-lg"></i> DATA TRANSMITTED.
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container text-center">
            <h5 class="brand-font mb-3"><i class="bi bi-box-seam-fill"></i> FrequentCRM</h5>
            <div class="d-flex justify-content-center gap-4 mb-4 fw-bold">
                <a href="login.php" class="text-dark text-decoration-none">LOGIN</a>
                <a href="#" class="text-dark text-decoration-none">DOCS</a>
                <a href="#" class="text-dark text-decoration-none">SUPPORT</a>
            </div>
            <p class="small text-muted font-monospace">&copy; 2025 Thai Jashe SOLUTIONS. SYSTEM OPERATIONAL.</p>
        </div>
    </footer>

    <script>
        // Simple logic to simulate connecting to your backend
        document.getElementById('landingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = document.querySelector('button[type="submit"]');
            const loader = document.getElementById('btnLoader');
            const btnText = document.getElementById('btnText');
            const msg = document.getElementById('msgSuccess');

            btn.disabled = true;
            btnText.innerText = "PROCESSING...";
            loader.style.display = "inline-block";

            // Normally you would fetch('api.php?action=add_customer') here
            // But since this is a landing page, we simulate the success based on your logic
            setTimeout(() => {
                loader.style.display = "none";
                btnText.innerText = "SUBMITTED";
                msg.style.display = "block";
                
                // Optional: Redirect to login
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 2000);
            }, 1500);
        });
    </script>
</body>
</html>