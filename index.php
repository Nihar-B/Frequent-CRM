<?php
require_once 'functions.php';
require_once 'db.php';
start_secure_session();

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

// --- FORCE REFRESH USER DATA ---
$stmt = $pdo->prepare("SELECT full_name, email, role, avatar FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

if ($currentUser) {
    $_SESSION['user_name'] = $currentUser['full_name'];
    $_SESSION['user_role'] = $currentUser['role'];
    $_SESSION['user_avatar'] = $currentUser['avatar'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sarder Solutions | Dashboard</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    <style>
        /* --- NEO-BRUTALIST VARIABLES --- */
        :root { 
            --bg-body: #ffffff;
            --text-main: #000000;
            --accent: #b084fc; /* The Purple Pop */
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
            display: flex; 
            min-height: 100vh;
            overflow-x: hidden; /* Prevent horizontal scroll on body */
        }

        h1, h2, h3, h4, h5, h6, .brand-font, .stat-value {
            font-family: 'Space Mono', monospace;
            font-weight: 700;
            letter-spacing: -0.03em;
        }

        /* --- SIDEBAR --- */
        .sidebar { 
            width: 260px; 
            background: #fff; 
            height: 100vh; 
            position: fixed; 
            top: 0;
            left: 0;
            border-right: var(--border-width) solid #000;
            padding-top: 24px; 
            z-index: 1050; 
            transition: transform 0.3s ease-in-out;
            overflow-y: auto;
        }
        
        .sidebar h4 {
            margin: 0 20px 30px;
            font-size: 1.2rem;
            display: flex; align-items: center; gap: 10px;
        }

        .nav-link { 
            color: #000; 
            font-weight: 600;
            padding: 12px 20px; 
            margin: 8px 16px; 
            border: var(--border-width) solid transparent;
            border-radius: var(--radius);
            transition: 0.1s;
            cursor: pointer; 
            font-size: 0.95rem;
        }
        
        .nav-link:hover, .nav-link.active { 
            background: var(--accent); 
            border-color: #000;
            box-shadow: 2px 2px 0 #000;
            transform: translate(-1px, -1px);
        }
        
        .sidebar i { width: 24px; margin-right: 8px; text-align: center; }

        /* --- MOBILE OVERLAY --- */
        .sidebar-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
            display: none;
        }
        .sidebar-overlay.active { display: block; }

        /* --- MAIN CONTENT --- */
        .main-content { 
            margin-left: 260px; 
            width: calc(100% - 260px); 
            padding: 32px; 
            transition: margin-left 0.3s ease-in-out, width 0.3s ease-in-out;
        }

        /* --- HEADER --- */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            border: var(--border-width) solid #000;
            padding: 16px 24px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-hard);
            margin-bottom: 32px;
            flex-wrap: wrap; /* Allow wrapping on small screens */
            gap: 15px;
        }

        .menu-toggle-btn {
            display: none; /* Hidden on desktop */
            background: #fff;
            border: 2px solid #000;
            border-radius: 4px;
            padding: 5px 10px;
            box-shadow: 3px 3px 0 #000;
            margin-right: 15px;
        }
        .menu-toggle-btn:active { box-shadow: none; transform: translate(2px, 2px); }

        .search-container {
            position: relative;
            flex-grow: 1;
            max-width: 400px;
        }

        .search-input {
            background: #f3f4f6;
            border: var(--border-width) solid #000;
            border-radius: 99px;
            padding: 8px 16px;
            width: 100%; /* Responsive width */
            font-weight: 500;
        }
        .search-input:focus {
            outline: none;
            background: #fff;
            box-shadow: 3px 3px 0 #000;
        }

        /* --- CARDS --- */
        .card-neo { 
            background: #fff; 
            border: var(--border-width) solid #000; 
            border-radius: var(--radius); 
            box-shadow: var(--shadow-hard); 
            padding: 24px; 
            margin-bottom: 24px; 
            transition: 0.1s;
        }
        
        .card-hover:hover { 
            transform: translate(-2px, -2px); 
            box-shadow: var(--shadow-hover); 
            cursor: pointer;
        }

        .stat-value { 
            font-size: 2rem; 
            margin-top: 8px;
            word-break: break-all; /* Prevent overflow of large numbers */
        }

        /* --- BUTTONS --- */
        .btn {
            border-radius: var(--radius) !important;
            border: var(--border-width) solid #000 !important;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.85rem;
            padding: 10px 20px;
            box-shadow: 3px 3px 0 #000;
            transition: 0.1s;
        }
        
        .btn:hover {
            transform: translate(-2px, -2px);
            box-shadow: 5px 5px 0 #000;
        }
        
        .btn:active {
            transform: translate(0, 0);
            box-shadow: none;
        }

        .btn-primary { background: #000; color: #fff; }
        .btn-primary:hover { background: #333; color: #fff; }
        
        .btn-outline-primary { color: #000; background: #fff; }
        .btn-outline-primary:hover { background: var(--accent); color: #000; }

        /* --- PIPELINE --- */
        .pipeline-col { 
            background: #f3f4f6; 
            border: var(--border-width) solid #000;
            border-radius: var(--radius);
            padding: 16px; 
            min-height: 500px;
        }
        
        .pipeline-card { 
            background: #fff; 
            padding: 16px; 
            border: var(--border-width) solid #000; 
            border-radius: var(--radius);
            box-shadow: 3px 3px 0 #000; 
            margin-bottom: 16px; 
            cursor: grab; 
            transition: 0.1s;
        }
        
        .pipeline-card:hover {
            transform: translate(-2px, -2px);
            box-shadow: 5px 5px 0 #000;
        }

        /* --- TABLES --- */
        .table-responsive {
            border: var(--border-width) solid #000;
            border-radius: var(--radius);
            margin-bottom: 24px;
        }
        .table { border: none; margin-bottom: 0; white-space: nowrap; } /* Prevent wrapping in tables */
        .table thead { background: #000; color: #fff; }
        .table thead th { 
            border-bottom: var(--border-width) solid #000; 
            font-family: 'Space Mono', monospace; 
            text-transform: uppercase; 
            font-size: 0.85rem; 
            padding: 12px 16px; 
        }
        .table td { 
            border-bottom: 1px solid #000; 
            padding: 16px; 
            vertical-align: middle; 
            font-weight: 500;
        }

        /* --- BADGES --- */
        .badge {
            border: 1px solid #000;
            color: #000;
            border-radius: 99px;
            padding: 5px 10px;
        }
        .bg-success { background: #4ade80 !important; }
        .bg-warning { background: #facc15 !important; }
        .bg-secondary { background: #e5e7eb !important; }

        /* --- MODALS --- */
        .modal-content {
            border: var(--border-width) solid #000;
            border-radius: var(--radius);
            box-shadow: 10px 10px 0 #000;
        }
        .modal-header { border-bottom: var(--border-width) solid #000; }
        .form-control, .form-select {
            border: var(--border-width) solid #000;
            border-radius: var(--radius);
            padding: 12px;
            font-weight: 500;
        }
        .form-control:focus, .form-select:focus {
            box-shadow: 4px 4px 0 #000;
            border-color: #000;
        }

        /* --- TOAST --- */
        #toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .toast-neo {
            min-width: 300px;
            background: #fff;
            border: 3px solid #000;
            padding: 15px;
            font-family: 'Space Mono', monospace;
            font-weight: 700;
            box-shadow: 6px 6px 0 #000;
            animation: slideIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .toast-success { background: #4ade80; color: #000; }
        .toast-error { background: #ff4d4d; color: #fff; border-color: #000; }
        .toast-info { background: #fff; color: #000; }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* --- RESPONSIVE MEDIA QUERIES --- */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%); /* Hide sidebar */
            }
            .sidebar.active {
                transform: translateX(0); /* Show sidebar */
            }
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }
            .menu-toggle-btn {
                display: block; /* Show hamburger */
            }
            .top-bar {
                padding: 15px;
                gap: 10px;
            }
            .search-container {
                order: 3; /* Move search to bottom on very small screens if needed */
                width: 100%;
                max-width: 100%;
                margin-top: 10px;
            }
            .header-controls {
                margin-left: auto;
            }
            
            /* Pipeline/Kanban Stacking */
            .pipeline-col {
                min-height: 200px; /* Reduced height when stacked */
                margin-bottom: 20px;
            }
            
            /* User Profile Hide Name on Mobile */
            .user-profile-name {
                display: none !important;
            }
        }

        @media (min-width: 992px) {
            .sidebar-overlay { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <div class="sidebar" id="sidebar">
        <h4>
            <i class="bi bi-box-seam-fill"></i> FrequentCRM
            <i class="bi bi-x-lg d-lg-none ms-auto" style="cursor: pointer;" onclick="toggleSidebar()"></i>
        </h4>
        <div onclick="nav('dashboard')" class="nav-link active"><i class="bi bi-grid-fill"></i> Dashboard</div>
        <div onclick="nav('customers')" class="nav-link"><i class="bi bi-people-fill"></i> Customers</div>
        <div onclick="nav('sales')" class="nav-link"><i class="bi bi-bar-chart-fill"></i> Pipeline</div>
        <div onclick="nav('products')" class="nav-link"><i class="bi bi-box-fill"></i> Products</div>
        <div onclick="nav('tasks')" class="nav-link"><i class="bi bi-check-square-fill"></i> Tasks</div>
        <div onclick="nav('calendar')" class="nav-link"><i class="bi bi-calendar-event-fill"></i> Calendar</div>
        <div onclick="nav('team')" class="nav-link" id="nav-team"><i class="bi bi-person-badge-fill"></i> Team</div>
        <div onclick="nav('archive')" class="nav-link" id="nav-archive" style="display:none;">
    <i class="bi bi-archive-fill"></i> Archive
</div>
        <div class="mt-auto p-3">
            <a href="logout.php" class="btn btn-outline-primary w-100">LOGOUT</a>
        </div>
    </div>

    <div class="main-content">
        
        <div class="top-bar">
            <div class="d-flex align-items-center">
                <button class="menu-toggle-btn" onclick="toggleSidebar()">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <h2 id="page-title" class="m-0 fs-4">DASHBOARD</h2>
            </div>

            <div class="search-container">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3"></i>
                <input type="text" id="globalSearch" class="search-input ps-5" placeholder="Search database...">
            </div>

            <div class="d-flex align-items-center gap-3 header-controls">
                <div class="dropdown">
                    <button class="btn btn-outline-primary position-relative border-0 shadow-none px-2" data-bs-toggle="dropdown" style="box-shadow: none !important;">
                        <i class="bi bi-bell-fill fs-5"></i>
                        <span id="notif-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none; border:none;">0</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-2 border-dark shadow p-0 mt-2" style="border-radius: 8px;">
                        <li><h6 class="dropdown-header bg-light border-bottom border-dark fw-bold">Alerts</h6></li>
                        <li><a class="dropdown-item py-2 fw-bold" href="#" onclick="nav('tasks')"><span id="notif-text">No alerts</span></a></li>
                    </ul>
                </div>

                <a href="profile.php" class="text-decoration-none text-dark d-flex align-items-center gap-2">
                    <?php 
                    $userAvatar = isset($_SESSION['user_avatar']) ? $_SESSION['user_avatar'] : '';
                    if (!empty($userAvatar)): 
                    ?>
                        <img src="<?php echo htmlspecialchars($userAvatar); ?>?t=<?php echo time(); ?>" 
                             alt="Profile" 
                             class="rounded-circle border border-2 border-dark" 
                             style="width: 40px; height: 40px; object-fit: cover; box-shadow: 2px 2px 0 #000;">
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center border-2 border-dark border bg-white rounded-circle" 
                             style="width: 40px; height: 40px; font-weight: 700; box-shadow: 2px 2px 0 #000;">
                            <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-none d-md-block user-profile-name">
                        <div class="fw-bold" style="font-size: 0.9rem;"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                    </div>
                </a>
            </div>
        </div>

        <div id="view-dashboard" class="view-section">
            <div class="row mb-4">
                <div class="col-12 col-md-6">
                    <div class="card-neo card-hover" onclick="nav('customers')">
                        <div class="text-muted fw-bold text-uppercase small">Total Customers</div>
                        <div class="stat-value" id="dash-customers">...</div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="card-neo card-hover" onclick="nav('sales')">
                        <div class="text-muted fw-bold text-uppercase small">Revenue (Closed)</div>
                        <div class="stat-value text-success" id="dash-revenue">...</div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-lg-8 mb-4">
                    <div class="card-neo h-100">
                        <h5 class="mb-4">Revenue Trend</h5>
                        <div class="chart-container" style="position: relative; height:300px; width:100%">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4 mb-4">
                    <div class="card-neo h-100">
                        <h5 class="mb-4">Pipeline Ratio</h5>
                        <div class="chart-container" style="position: relative; height:300px; width:100%">
                            <canvas id="productsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="view-search" class="view-section" style="display:none;">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 border-bottom border-dark pb-3 gap-2">
                <h4><i class="bi bi-search me-2"></i> RESULTS FOR: <span id="search-term-display" class="text-primary bg-light px-2 border border-dark"></span></h4>
                <button class="btn btn-sm btn-outline-danger fw-bold" onclick="document.getElementById('globalSearch').value=''; nav('dashboard');">CLEAR & EXIT</button>
            </div>

            <div class="row">
                <div class="col-12 col-md-6 mb-4">
                    <div class="card-neo h-100">
                        <h5 class="mb-3 border-bottom border-dark pb-2">CUSTOMERS</h5>
                        <div id="search-results-customers" class="d-flex flex-column gap-2"></div>
                    </div>
                </div>

                <div class="col-12 col-md-6 mb-4">
                    <div class="card-neo h-100">
                        <h5 class="mb-3 border-bottom border-dark pb-2">DEALS</h5>
                        <div id="search-results-deals" class="d-flex flex-column gap-2"></div>
                    </div>
                </div>

                <div class="col-12 col-md-6 mb-4">
                    <div class="card-neo h-100">
                        <h5 class="mb-3 border-bottom border-dark pb-2">TASKS</h5>
                        <div id="search-results-tasks" class="d-flex flex-column gap-2"></div>
                    </div>
                </div>

                <div class="col-12 col-md-6 mb-4">
                    <div class="card-neo h-100">
                        <h5 class="mb-3 border-bottom border-dark pb-2">PRODUCTS</h5>
                        <div id="search-results-products" class="d-flex flex-column gap-2"></div>
                    </div>
                </div>
            </div>
        </div>

        <div id="view-customers" class="view-section" style="display:none;">
            <div class="d-flex flex-wrap justify-content-end mb-3 gap-2">
                <button class="btn btn-outline-primary" onclick="showWebFormModal()"><i class="bi bi-code-slash"></i> FORM</button>
                <button class="btn btn-outline-primary" onclick="exportData('customers')">CSV</button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCustomer">NEW CUSTOMER</button>
            </div>
            <div class="card-neo p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead><tr><th>Name</th><th>Company</th><th>Email</th><th>Score</th><th>Status</th><th>Owner</th><th>Action</th></tr></thead>
                        <tbody id="customer-table"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="view-sales" class="view-section" style="display:none;">
            <div class="d-flex justify-content-end mb-3 gap-2">
                <button class="btn btn-outline-primary" onclick="exportData('deals')">EXPORT</button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalDeal">NEW DEAL</button>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 col-xl-3 mb-3">
                    <h6 class="text-uppercase fw-bold border-bottom border-dark pb-2 mb-3">Lead</h6>
                    <div class="pipeline-col" id="col-lead"></div>
                </div>
                <div class="col-12 col-md-6 col-xl-3 mb-3">
                    <h6 class="text-uppercase fw-bold border-bottom border-dark pb-2 mb-3">Proposal</h6>
                    <div class="pipeline-col" id="col-proposal"></div>
                </div>
                <div class="col-12 col-md-6 col-xl-3 mb-3">
                    <h6 class="text-uppercase fw-bold border-bottom border-dark pb-2 mb-3">Negotiation</h6>
                    <div class="pipeline-col" id="col-negotiation"></div>
                </div>
                <div class="col-12 col-md-6 col-xl-3 mb-3">
                    <h6 class="text-uppercase fw-bold border-bottom border-dark pb-2 mb-3">Closed</h6>
                    <div class="pipeline-col" id="col-closed"></div>
                </div>
            </div>
        </div>

        <div id="view-products" class="view-section" style="display:none;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>CATALOG</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProduct">ADD ITEM</button>
            </div>
            <div class="card-neo p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead><tr><th>Name</th><th>Type</th><th>Price</th><th>Desc</th><th>Action</th></tr></thead>
                        <tbody id="product-table"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="view-tasks" class="view-section" style="display:none;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>TASKS</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">NEW TASK</button>
            </div>
            <div class="card-neo p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead><tr><th>Task</th><th>Due</th><th>Owner</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody id="task-table"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="view-calendar" class="view-section" style="display:none;">
            <div class="card-neo">
                <div id="calendar"></div>
            </div>
        </div>

        <div id="view-team" class="view-section" style="display:none;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>TEAM ROSTER</h4>
                <button class="btn btn-neo" id="btnAddUser" data-bs-toggle="modal" data-bs-target="#modalUser">
                    <i class="bi bi-person-plus-fill me-2"></i> ADD MEMBER
                </button>
            </div>
            <div class="row" id="team-grid"></div>
        </div>
                        <div id="view-archive" class="view-section" style="display:none;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="bi bi-trash3 me-2"></i> ARCHIVED DEALS</h4>
        <span class="badge bg-warning text-dark border border-dark">ADMIN ACCESS ONLY</span>
    </div>
    
    <div class="card-neo p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-dark text-white">
                    <tr>
                        <th>Deal Title</th>
                        <th>Value</th>
                        <th>Client</th>
                        <th>Archived Date</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody id="archive-table">
                    </tbody>
            </table>
        </div>
    </div>
</div>
    </div>

    <div class="modal fade" id="modalCustomer" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">ADD CUSTOMER</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <form id="formCustomer">
                <div class="row mb-3"><div class="col"><input type="text" name="first_name" class="form-control" placeholder="First Name" required></div><div class="col"><input type="text" name="last_name" class="form-control" placeholder="Last Name" required></div></div>
                <div class="mb-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                <div class="mb-3"><input type="text" name="company" class="form-control" placeholder="Company"></div>
                <div class="mb-3"><label class="form-label small fw-bold">Est. Value ($)</label><input type="number" name="potential_value" class="form-control" placeholder="0.00"></div>
                <div class="mb-3"><select name="status" class="form-select"><option value="Lead">Lead</option><option value="Active">Active</option></select></div>
                <div class="mb-3"><input type="text" name="source" class="form-control" placeholder="Source (e.g. Website)"></div>
                <button type="submit" class="btn btn-primary w-100">SAVE RECORD</button>
            </form>
        </div>
    </div></div></div>

    <div class="modal fade" id="editCustomerModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">EDIT CUSTOMER</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <form id="formEditCustomer">
                <input type="hidden" name="id" id="edit_cust_id">
                <div class="row mb-3"><div class="col"><input type="text" name="first_name" id="edit_cust_fname" class="form-control" required></div><div class="col"><input type="text" name="last_name" id="edit_cust_lname" class="form-control" required></div></div>
                <div class="mb-3"><input type="email" name="email" id="edit_cust_email" class="form-control" required></div>
                <div class="mb-3"><input type="text" name="company" id="edit_cust_company" class="form-control"></div>
                <div class="mb-3"><input type="number" name="potential_value" id="edit_cust_val" class="form-control"></div>
                <div class="mb-3"><select name="status" id="edit_cust_status" class="form-select"><option value="Lead">Lead</option><option value="Active">Active</option></select></div>
                <button type="submit" class="btn btn-primary w-100">UPDATE RECORD</button>
            </form>
        </div>
    </div></div></div>

    <div class="modal fade" id="modalProduct" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">ADD PRODUCT</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <form id="formProduct">
                <div class="mb-3"><input type="text" name="prod_name" class="form-control" placeholder="Name" required></div>
                <div class="row mb-3">
                    <div class="col"><select name="prod_type" class="form-select"><option value="Service">Service</option><option value="Product">Product</option></select></div>
                    <div class="col"><input type="number" name="prod_price" class="form-control" placeholder="Price" required></div>
                    <div class="col"><input type="number" name="prod_cost" class="form-control" placeholder="Cost"></div>
                </div>
                <div class="mb-3"><textarea name="prod_desc" class="form-control" placeholder="Description"></textarea></div>
                <button type="submit" class="btn btn-primary w-100">SAVE ITEM</button>
            </form>
        </div>
    </div></div></div>

    <div class="modal fade" id="editProductModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">EDIT PRODUCT</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <form id="formEditProduct">
                <input type="hidden" name="prod_id" id="edit_prod_id">
                <div class="mb-3"><input type="text" name="prod_name" id="edit_prod_name" class="form-control" required></div>
                <div class="row mb-3">
                    <div class="col"><select name="prod_type" id="edit_prod_type" class="form-select"><option value="Service">Service</option><option value="Product">Product</option></select></div>
                    <div class="col"><input type="number" name="prod_price" id="edit_prod_price" class="form-control" required></div>
                </div>
                <div class="mb-3"><textarea name="prod_desc" id="edit_prod_desc" class="form-control"></textarea></div>
                <button type="submit" class="btn btn-primary w-100">UPDATE ITEM</button>
            </form>
        </div>
    </div></div></div>

    <div class="modal fade" id="modalDeal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">ADD DEAL</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <form id="formDeal">
                <div class="mb-3">
                    <label class="form-label fw-bold">CLIENT</label>
                    <select name="customer_id" id="dealCustomerSelect" class="form-select" required>
                        <option value="">SELECT...</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">PRODUCT (OPTIONAL)</label>
                    <select name="product_id" id="dealProductSelect" class="form-select" onchange="autoFillDeal(this)">
                        <option value="">-- MANUAL ENTRY --</option>
                    </select>
                </div>
                <div class="mb-3"><input type="text" name="deal_title" id="deal_title" class="form-control" placeholder="Deal Title" required></div>
                <div class="mb-3"><input type="number" name="deal_value" id="deal_value" class="form-control" placeholder="Value ($)" required></div>
                <div class="row mb-3">
                    <div class="col"><select name="deal_stage" class="form-select"><option value="Lead">Lead</option><option value="Proposal">Proposal</option><option value="Negotiation">Negotiation</option><option value="Closed">Closed</option></select></div>
                    <div class="col"><input type="date" name="deal_date" class="form-control" required></div>
                </div>
                <div class="mb-3"><select name="assigned_to" id="dealAssignSelect" class="form-select mb-3"></select></div>
                <button type="submit" class="btn btn-primary w-100">SAVE DEAL</button>
            </form>
        </div>
    </div></div></div>

    <div class="modal fade" id="editDealModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">EDIT DEAL</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <ul class="nav nav-tabs mb-3 border-black" id="dealTabs">
                <li class="nav-item"><a class="nav-link active text-dark rounded-0 fw-bold border-black" data-bs-toggle="tab" href="#deal-edit">DETAILS</a></li>
                <li class="nav-item"><a class="nav-link text-dark rounded-0 fw-bold border-black" data-bs-toggle="tab" href="#deal-history" onclick="loadDealHistory()">HISTORY</a></li>
            </ul>
            
            <div class="tab-content">
                <div class="tab-pane fade show active" id="deal-edit">
                    <form id="formEditDeal">
                        <input type="hidden" name="deal_id" id="edit_deal_id">
                        <div class="mb-3"><input type="text" name="deal_title" id="edit_deal_title" class="form-control" required></div>
                        <div class="mb-3"><input type="number" name="deal_value" id="edit_deal_value" class="form-control" required></div>
                        <div class="row mb-3">
                            <div class="col"><select name="deal_stage" id="edit_deal_stage" class="form-select"><option value="Lead">Lead</option><option value="Proposal">Proposal</option><option value="Negotiation">Negotiation</option><option value="Closed">Closed</option></select></div>
                            <div class="col"><input type="date" name="deal_date" id="edit_deal_date" class="form-control"></div>
                        </div>
                        <div class="mb-3"><select name="assigned_to" id="editDealAssignSelect" class="form-select mb-3"></select></div>
                        <button type="submit" class="btn btn-primary w-100">UPDATE DEAL</button>
                    </form>
                </div>
                <div class="tab-pane fade" id="deal-history">
                    <div id="history-list" class="small text-muted" style="max-height: 300px; overflow-y: auto;">
                        <p class="text-center">Loading history...</p>
                    </div>
                </div>
            </div>
        </div>
    </div></div></div>

    <div class="modal fade" id="addTaskModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">ADD TASK</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <form id="formTask">
                <div class="mb-3"><input type="text" name="task_title" class="form-control" required placeholder="Subject"></div>
                <div class="mb-3"><textarea name="task_desc" class="form-control" rows="2" placeholder="Description"></textarea></div>
                <div class="row mb-3">
                    <div class="col"><input type="date" name="task_date" class="form-control" required></div>
                    <div class="col"><select name="assigned_to" id="taskAssignSelect" class="form-select"></select></div>
                </div>
                <button type="submit" class="btn btn-primary w-100">CREATE TASK</button>
            </form>
        </div>
    </div></div></div>

    <div class="modal fade" id="editTaskModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">UPDATE TASK</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <form id="formEditTask">
                <input type="hidden" name="task_id" id="edit_task_id">
                <div class="mb-3"><select name="task_status" id="edit_task_status" class="form-select fw-bold"><option value="Pending">PENDING</option><option value="In Progress">IN PROGRESS</option><option value="Completed">COMPLETED</option></select></div>
                <div class="mb-3"><input type="text" name="task_title" id="edit_task_title" class="form-control" required></div>
                <div class="mb-3"><textarea name="task_desc" id="edit_task_desc" class="form-control" rows="3"></textarea></div>
                <div class="row mb-3">
                    <div class="col"><input type="date" name="task_date" id="edit_task_date" class="form-control" required></div>
                    <div class="col"><select name="assigned_to" id="editTaskAssignSelect" class="form-select"></select></div>
                </div>
                <button type="submit" class="btn btn-primary w-100">SAVE CHANGES</button>
            </form>
        </div>
    </div></div></div>

    <div class="modal fade" id="modalUser" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">ADD MEMBER</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <form id="formUser">
                <div class="mb-3"><input type="text" name="full_name" class="form-control" placeholder="Full Name" required></div>
                <div class="mb-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
                <div class="mb-3">
    <label class="form-label small fw-bold">ROLE</label>
    <select name="role" class="form-select">
        <option value="sales_rep">Sales Rep</option>
        <option value="manager">Manager</option>
        <option value="admin">Admin</option>
        
        <option value="super_admin">Super Admin</option>
    </select>
</div>
<button type="submit" class="btn btn-primary w-100">CREATE ACCOUNT</button>
            </form>
        </div>
    </div></div></div>

    <div class="modal fade" id="notesModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">NOTES</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div id="notes-list" class="mb-3 border border-dark p-2" style="max-height: 300px; overflow-y: auto; background: #fff;"></div>
            <form id="formAddNote">
                <input type="hidden" name="related_to" id="note_related_to">
                <input type="hidden" name="related_id" id="note_related_id">
                <div class="input-group">
                    <input type="text" name="note" class="form-control" placeholder="Type a note..." required>
                    <button class="btn btn-primary" type="submit">SEND</button>
                </div>
            </form>
        </div>
    </div></div></div>

    <div class="modal fade" id="webFormModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">WEB-TO-LEAD</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <p class="small fw-bold">COPY THIS CODE:</p>
            <textarea class="form-control bg-light" rows="10" readonly id="embedCode"></textarea>
            <button class="btn btn-sm btn-secondary mt-2 w-100" onclick="navigator.clipboard.writeText(document.getElementById('embedCode').value); showToast('COPIED TO CLIPBOARD', 'success');">COPY TO CLIPBOARD</button>
        </div>
    </div></div></div>
<div class="modal fade" id="modalChangeRole" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">CHANGE USER ROLE</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formChangeRole">
                    <input type="hidden" name="user_id" id="role_user_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">SELECT NEW ROLE</label>
                        <select name="new_role" id="role_select" class="form-select">
                            </select>
                    </div>

                    <div class="alert alert-warning small">
                        <i class="bi bi-exclamation-triangle-fill"></i> 
                        Changing a role will immediately update the user's permissions.
                    </div>

                    <button type="submit" class="btn btn-primary w-100">UPDATE ACCESS</button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="viewTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom border-2 border-dark" style="background: #f3f4f6;">
                <h5 class="modal-title text-uppercase fw-bold">
                    <i class="bi bi-eye-fill me-2"></i> Task Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <span id="view_task_status" class="badge rounded-0 border border-dark text-uppercase px-3 py-2 fs-6">Loading...</span>
                    </div>
                    <div class="text-end">
                        <small class="text-muted fw-bold text-uppercase d-block">Due Date</small>
                        <span id="view_task_date" class="fs-5 fw-bold font-monospace">--/--/----</span>
                    </div>
                </div>

                <h3 id="view_task_title" class="fw-bold mb-4 text-break brand-font">...</h3>

                <div class="mb-4">
                    <label class="small text-muted fw-bold text-uppercase mb-1">Description</label>
                    <div class="p-3 border border-2 border-dark rounded bg-light" style="min-height: 80px;">
                        <p id="view_task_desc" class="mb-0 text-break" style="white-space: pre-wrap;">No description provided.</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="border border-dark p-2 rounded d-flex align-items-center gap-2">
                            <div id="view_task_avatar">
                                </div>
                            <div class="overflow-hidden">
                                <small class="text-muted fw-bold text-uppercase d-block" style="font-size: 0.7rem;">Assigned To</small>
                                <div id="view_task_assignee" class="fw-bold text-truncate">...</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="border border-dark p-2 rounded">
                            <small class="text-muted fw-bold text-uppercase d-block" style="font-size: 0.7rem;">Related To</small>
                            <div id="view_task_related" class="fw-bold text-truncate">None</div>
                        </div>
                    </div>
                </div>

            </div>
            
            <div class="modal-footer border-top border-2 border-dark bg-white">
                <input type="hidden" id="view_task_id">
                <button type="button" class="btn btn-outline-dark fw-bold" data-bs-dismiss="modal">CLOSE</button>
                <button type="button" class="btn btn-primary fw-bold" onclick="switchToEditTask()">
                    <i class="bi bi-pencil-fill me-1"></i> EDIT TASK
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-3 border-dark shadow-hard" style="box-shadow: 10px 10px 0 #000;">
            <div class="modal-header bg-danger text-white border-bottom border-2 border-dark">
                <h5 class="modal-title fw-bold font-monospace">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>CONFIRM DELETE
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 fw-bold">
                Are you sure you want to delete this item?
                <div class="text-danger mt-2 small font-monospace">THIS ACTION IS PERMANENT.</div>
            </div>
            <div class="modal-footer bg-light border-top border-2 border-dark">
                <button type="button" class="btn btn-outline-dark fw-bold border-2" data-bs-dismiss="modal">CANCEL</button>
                <button type="button" class="btn btn-danger fw-bold border-2 border-dark shadow-sm" id="btnConfirmDeleteAction">
                    YES, DELETE IT
                </button>
            </div>
        </div>
    </div>
</div>
    <div id="toast-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const currentUser = { id: <?php echo $_SESSION['user_id']; ?>, role: '<?php echo $_SESSION['user_role']; ?>', name: '<?php echo htmlspecialchars($_SESSION['user_name']); ?>' };
        let calendarInstance = null;

        // Mobile Sidebar Toggle
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('sidebarOverlay').classList.toggle('active');
        }

        document.addEventListener('DOMContentLoaded', () => {
            applyRBAC();
            loadAllData();
            initCharts();
            setInterval(() => { checkNotifications(); refreshActiveView(); }, 5000); 
            checkNotifications();
            
            const searchInput = document.getElementById('globalSearch');
            if (searchInput) {
                searchInput.addEventListener('keyup', function(e) {
                    if (e.key === 'Enter') {
                        const query = this.value.trim();
                        if(query.length > 1) {
                            performGlobalSearch(query);
                        } else {
                            showToast('Type at least 2 characters', 'error');
                        }
                    }
                });
            }
document.getElementById('btnConfirmDeleteAction').addEventListener('click', function() {
        if (!pendingDelete) return;

        const fd = new FormData(); 
        fd.append('type', pendingDelete.type); 
        fd.append('id', pendingDelete.id);
        
        // Disable button to prevent double clicks
        this.disabled = true;
        this.innerText = "DELETING...";

        fetch('api.php?action=delete_item', {method: 'POST', body: fd})
        .then(r => r.json())
        .then(d => {
            // Reset Button
            this.disabled = false;
            this.innerText = "YES, DELETE IT";

            // Hide Modal
            const modalEl = document.getElementById('deleteConfirmModal');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            modalInstance.hide();

            if (d.status === 'success') {
                // SHOW TOAST (Not Alert)
                showToast(d.msg || 'Item deleted successfully', 'success');
                
                // Refresh Data
                loadAllData();
            } else {
                showToast(d.message || 'Delete failed', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            this.disabled = false;
            this.innerText = "YES, DELETE IT";
            showToast('Network Error', 'error');
        });
    });
            async function performGlobalSearch(query) {
                // 1. Switch View
                nav('search'); 
                document.getElementById('search-term-display').innerText = query.toUpperCase();
                
                // 2. Clear previous results
                ['customers', 'deals', 'tasks', 'products'].forEach(t => {
                    document.getElementById(`search-results-${t}`).innerHTML = '<div class="text-center text-muted py-3"><div class="spinner-border spinner-border-sm"></div></div>';
                });

                // 3. Fetch Data
                const res = await fetch('api.php?action=global_search&q=' + encodeURIComponent(query));
                const d = await res.json();

                if(d.status === 'success') {
                    renderSearchResults('customers', d.data.customers, (item) => `
                        <div class="d-flex justify-content-between align-items-center bg-light p-2 border border-dark rounded">
                            <div>
                                <div class="fw-bold">${item.first_name} ${item.last_name}</div>
                                <small class="text-muted">${item.company}</small>
                            </div>
                            <button class="btn btn-sm btn-dark" onclick="nav('customers'); loadCustomers('${item.email}')">VIEW</button>
                        </div>
                    `);

                    renderSearchResults('deals', d.data.deals, (item) => `
                        <div class="d-flex justify-content-between align-items-center bg-light p-2 border border-dark rounded" onclick="openEditDeal(${item.id})" style="cursor:pointer">
                            <div>
                                <div class="fw-bold">${item.title}</div>
                                <span class="badge bg-white text-dark border border-dark">${item.stage}</span>
                            </div>
                            <div class="fw-bold text-success">$${parseFloat(item.value).toLocaleString()}</div>
                        </div>
                    `);

                    renderSearchResults('tasks', d.data.tasks, (item) => `
                        <div class="d-flex justify-content-between align-items-center bg-light p-2 border border-dark rounded" onclick="openEditTask(${item.id})" style="cursor:pointer">
                            <div>
                                <div class="fw-bold">${item.title}</div>
                                <small class="text-muted">Due: ${item.due_date}</small>
                            </div>
                            <span class="badge ${item.status === 'Completed' ? 'bg-success' : 'bg-warning'} text-dark border border-dark">${item.status}</span>
                        </div>
                    `);

                    renderSearchResults('products', d.data.products, (item) => `
                        <div class="d-flex justify-content-between align-items-center bg-light p-2 border border-dark rounded">
                            <div class="fw-bold">${item.name}</div>
                            <div class="fw-bold">$${item.price}</div>
                        </div>
                    `);
                }
            }

            function renderSearchResults(type, data, templateFn) {
                const container = document.getElementById(`search-results-${type}`);
                if(data && data.length > 0) {
                    container.innerHTML = data.map(templateFn).join('');
                } else {
                    container.innerHTML = '<div class="text-muted small text-center fst-italic py-2">No matches found.</div>';
                }
            }
        });

        function loadAllData() { loadStats(); loadCustomers(); loadPipeline(); loadProducts(); loadTasks(); loadTeam(); loadTeamMembers(); loadCustomersForDropdown(); initKanban(); }
        
        function refreshActiveView() {
            if (document.getElementById('view-tasks').style.display !== 'none') loadTasks();
            if (document.getElementById('view-sales').style.display !== 'none') loadPipeline();
            if (document.getElementById('view-dashboard').style.display !== 'none') loadStats();
            if (document.getElementById('view-products').style.display !== 'none') loadProducts();
            if (document.getElementById('view-archive').style.display !== 'none') loadArchivedDeals();
            if (document.getElementById('view-calendar').style.display !== 'none' && calendarInstance) calendarInstance.refetchEvents();
        }

        function nav(section) {
            document.querySelectorAll('.view-section').forEach(el => el.style.display = 'none');
            document.getElementById('view-' + section).style.display = 'block';
            document.getElementById('page-title').innerText = section.toUpperCase();
            document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
            if(window.event && window.event.currentTarget.classList.contains('nav-link')) event.currentTarget.classList.add('active');
            
            // Close sidebar on mobile when navigating
            if (window.innerWidth < 992) {
                document.getElementById('sidebar').classList.remove('active');
                document.getElementById('sidebarOverlay').classList.remove('active');
            }
            
            if (section === 'calendar') {
                if(!calendarInstance) initCalendar(); else calendarInstance.render();
            }
        }

function applyRBAC() {
    // 1. Hide "Team" Menu for Sales Reps
    if (currentUser.role === 'sales_rep') {
        const teamNav = document.getElementById('nav-team');
        if (teamNav) teamNav.style.display = 'none';
    }

    // 2. Modify "Add User" Modal Role Dropdown
    const roleSelect = document.querySelector('select[name="role"]');
    
    if (roleSelect) {
        // Clear existing hardcoded options
        roleSelect.innerHTML = '';

        if (currentUser.role === 'super_admin') {
            // Super Admin sees everything
            roleSelect.innerHTML = `
                <option value="sales_rep">Sales Rep</option>
                <option value="manager">Manager</option>
                <option value="admin">Admin</option>
                <option value="super_admin">Super Admin</option>
            `;
        } 

        else if (currentUser.role === 'admin') {
            // Admin sees Manager and Sales Rep
            roleSelect.innerHTML = `
                <option value="sales_rep">Sales Rep</option>
                <option value="manager">Manager</option>
            `;
        } 
        else if (currentUser.role === 'manager') {
            // Manager sees ONLY Sales Rep
            roleSelect.innerHTML = `
                <option value="sales_rep">Sales Rep</option>
            `;
        }
    }
    
    // 3. Hide "Add Member" button completely if Sales Rep (Double check)
    if (currentUser.role === 'sales_rep') {
        const btnAddUser = document.getElementById('btnAddUser');
        if (btnAddUser) btnAddUser.style.display = 'none';
    }
    if (currentUser.role === 'admin' || currentUser.role === 'super_admin') {
        const archiveNav = document.getElementById('nav-archive');
        if (archiveNav) archiveNav.style.display = 'block';
    }
}

        async function loadStats() {
            const res = await fetch('api.php?action=get_dashboard_stats');
            const d = await res.json();
            if(d.status === 'success') {
                document.getElementById('dash-customers').innerText = d.data.customers;
                document.getElementById('dash-revenue').innerText = '$' + parseFloat(d.data.revenue).toLocaleString();
            }
        }
// Fetch Archived Deals
async function loadArchivedDeals() {
    const res = await fetch('api.php?action=get_archived_deals');
    const d = await res.json();
    let html = '';

    if (d.data && d.data.length > 0) {
        d.data.forEach(deal => {
            html += `
            <tr class="bg-light">
                <td>
                    <div class="fw-bold text-decoration-line-through text-muted">${deal.title}</div>
                    <small class="text-muted">${deal.stage}</small>
                </td>
                <td class="text-muted text-decoration-line-through">$${parseFloat(deal.value).toLocaleString()}</td>
                <td>${deal.customer_name || 'Unknown'}</td>
                <td class="small font-monospace">${deal.archived_date}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-success fw-bold border-dark shadow-sm" 
                            onclick="restoreItem('deals', ${deal.id})">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> RESTORE
                    </button>
                </td>
            </tr>`;
        });
    } else {
        html = '<tr><td colspan="5" class="text-center text-muted py-4 fst-italic">No archived deals found.</td></tr>';
    }
    document.getElementById('archive-table').innerHTML = html;
}

// Restore Action
function restoreItem(type, id) {
    if (!confirm("Are you sure you want to restore this deal to the active pipeline?")) return;

    const fd = new FormData();
    fd.append('type', type);
    fd.append('id', id);

    fetch('api.php?action=restore_item', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.status === 'success') {
                showToast(d.msg, 'success');
                loadArchivedDeals(); // Refresh list
            } else {
                showToast(d.message || 'Restore failed', 'error');
            }
        })
        .catch(err => showToast('Network error', 'error'));
}
        async function loadCustomers(search = '') {
            const res = await fetch('api.php?action=get_customers' + (search ? '&search=' + encodeURIComponent(search) : ''));
            const d = await res.json();
            let html = '';
            if(d.data) {
                d.data.forEach(c => {
                    let btn = (currentUser.role !== 'sales_rep') ? `<button class="btn btn-sm btn-outline-primary fw-bold ms-1" style="color:red; border-color:red;" onclick="deleteItem('customers', ${c.id})">X</button>` : '';
                    let scoreColor = c.score > 50 ? 'bg-success' : 'bg-warning';
                    html += `<tr>
                        <td>
                            <div class="fw-bold">
                                <a href="customer_details.php?id=${c.id}" class="text-decoration-none text-dark hover-primary">
                                    ${c.first_name} ${c.last_name}
                                </a>
                            </div>
                        </td>
                        <td>${c.company}</td><td>${c.email}</td>
                        <td><span class="badge ${scoreColor} rounded-pill border border-dark">${c.score || 0}</span></td>
                        <td><span class="badge bg-white text-dark border border-dark">${c.status}</span></td>
                        <td class="small text-muted">${c.owner_name||'-'}</td>
                        <td>
                            <div class="d-flex">
                                <button class="btn btn-sm btn-outline-primary me-1" onclick="openEditCustomer(${c.id})"><i class="bi bi-pencil-fill"></i></button>
                                <button class="btn btn-sm btn-outline-primary me-1" onclick="openNotes('customer', ${c.id})"><i class="bi bi-chat-fill"></i></button>
                                ${btn}
                            </div>
                        </td>
                    </tr>`;
                });
            }
            document.getElementById('customer-table').innerHTML = html || '<tr><td colspan="7" class="text-center text-muted">NO DATA FOUND</td></tr>';
        }

        function loadCustomersForDropdown() {
            fetch('api.php?action=get_customers').then(r=>r.json()).then(d => {
                let opts = '<option value="">SELECT...</option>';
                d.data.forEach(c => { opts += `<option value="${c.id}" data-company="${c.company}">${c.first_name} ${c.last_name} (${c.company})</option>`; });
                if(document.getElementById('dealCustomerSelect')) document.getElementById('dealCustomerSelect').innerHTML = opts;
            });
        }

async function loadPipeline() {
    const res = await fetch('api.php?action=get_deals');
    const d = await res.json();
    
    const stages = ['lead', 'proposal', 'negotiation', 'closed'];
    let totals = { lead: { count: 0, val: 0 }, proposal: { count: 0, val: 0 }, negotiation: { count: 0, val: 0 }, closed: { count: 0, val: 0 } };

    // Reset columns
    stages.forEach(stage => {
        const col = document.getElementById('col-' + stage);
        if (!col) return;
        col.innerHTML = ''; 
        let headerId = 'stats-' + stage;
        let header = document.getElementById(headerId);
        if (!header) {
            header = document.createElement('div');
            header.id = headerId;
            header.className = 'd-flex justify-content-between mb-2 font-monospace small fw-bold px-1';
            header.style.fontSize = '0.8rem';
            col.parentNode.insertBefore(header, col); 
        }
        header.innerHTML = '<span class="text-muted">0 DEALS</span> <span class="text-muted">$0</span>';
    });

    if (d.data) {
        d.data.forEach(deal => {
            const stageKey = deal.stage.toLowerCase();
            if (totals[stageKey]) {
                totals[stageKey].count++;
                totals[stageKey].val += parseFloat(deal.value || 0);
            }

            // --- PERMISSION LOGIC FOR DELETE BUTTON ---
            let showDelete = false;

            // 1. Super Admin & Admin: Always Show
            if (currentUser.role === 'super_admin' || currentUser.role === 'admin') {
                showDelete = true;
            }
            // 2. Sales Rep: Show ONLY if they own the deal
            else if (currentUser.role === 'sales_rep' && deal.assigned_to == currentUser.id) {
                showDelete = true;
            }

            let delBtn = showDelete 
                ? `<i class="bi bi-x-lg float-end text-danger" style="cursor:pointer;" onclick="event.stopPropagation(); deleteItem('deals', ${deal.id})"></i>` 
                : '';
            // ------------------------------------------

            let card = `
                <div class="pipeline-card" data-id="${deal.id}" onclick="openEditDeal(${deal.id})">
                    ${delBtn}
                    <div class="fw-bold text-uppercase pe-3">${deal.title}</div>
                    <div class="text-success fw-bold mt-1" style="font-size: 1.1rem;">$${parseFloat(deal.value).toLocaleString()}</div>
                    
                    <div class="d-flex justify-content-between mt-2 border-top border-dark pt-2">
                        <small class="text-muted text-truncate" style="max-width: 140px;">
                            <i class="bi bi-person-fill"></i> ${deal.customer_name || 'Unknown'}
                        </small>
                        <small class="text-primary fw-bold" style="cursor:pointer;" onclick="event.stopPropagation(); openNotes('deal', ${deal.id})">NOTES</small>
                    </div>
                </div>`;
            const colContainer = document.getElementById('col-' + stageKey);
            if (colContainer) colContainer.innerHTML += card;
        });

        // Update Totals headers
        stages.forEach(stage => {
            const t = totals[stage];
            if (t) {
                const money = t.val.toLocaleString('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 });
                const header = document.getElementById('stats-' + stage);
                if (header) {
                    header.innerHTML = `<span>${t.count} DEAL${t.count !== 1 ? 'S' : ''}</span> <span>${money}</span>`;
                }
            }
        });
    }
}

        async function loadProducts() {
            const res = await fetch('api.php?action=get_products');
            const d = await res.json();
            let html = '';
            const select = document.getElementById('dealProductSelect');
            select.innerHTML = '<option value="">-- MANUAL ENTRY --</option>';
            if(d.data) {
                d.data.forEach(p => {
                    let btn = (currentUser.role !== 'sales_rep') ? `<button class="btn btn-sm btn-outline-danger fw-bold ms-1" style="color:red; border-color:red;" onclick="deleteItem('products', ${p.id})">X</button>` : '';
                    html += `<tr><td><div class="fw-bold">${p.name}</div></td><td><span class="badge bg-white text-dark border border-dark">${p.type}</span></td><td class="text-success fw-bold">$${p.price}</td><td>${p.description||'-'}</td>
                    <td><div class="d-flex"><button class="btn btn-sm btn-outline-primary me-1" onclick="openEditProduct(${p.id})"><i class="bi bi-pencil-fill"></i></button>${btn}</div></td></tr>`;
                    select.innerHTML += `<option value="${p.id}" data-price="${p.price}" data-name="${p.name}">${p.name} ($${p.price})</option>`;
                });
            }
            document.getElementById('product-table').innerHTML = html;
        }

async function loadTasks() {
    const res = await fetch('api.php?action=get_tasks');
    const d = await res.json();
    let html = '';
    
    if(d.data && d.data.length > 0) {
        d.data.forEach(t => {
            // Delete Permission Check
            let del = (currentUser.role !== 'sales_rep') 
                ? `<button class="btn btn-sm btn-outline-danger" onclick="deleteItem('tasks', ${t.id})" title="Delete"><i class="bi bi-trash-fill"></i></button>` 
                : '';

            // Status Badge Logic
            let badgeClass = 'bg-secondary';
            if (t.status === 'Completed') badgeClass = 'bg-success';
            else if (t.status === 'In Progress') badgeClass = 'bg-warning text-dark';

            html += `<tr>
                <td>
                    <div class="fw-bold" style="cursor:pointer" onclick="openViewTask(${t.id})">
                        ${t.status === 'Completed' ? '<s class="text-muted">' + t.title + '</s>' : t.title}
                    </div>
                    <div class="small text-muted text-truncate" style="max-width:200px">${t.description||''}</div>
                </td>
                <td>${t.due_date}</td>
                <td>${t.assigned_name}</td>
                <td>
                    <span class="badge ${badgeClass} border border-dark rounded-pill">${t.status.toUpperCase()}</span>
                </td>
                <td>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline-dark" onclick="openViewTask(${t.id})" title="View Details">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary" onclick="openEditTask(${t.id})" title="Update Task">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                        ${del}
                    </div>
                </td>
            </tr>`;
        });
    } else {
        html = '<tr><td colspan="5" class="text-center text-muted py-3">No tasks found.</td></tr>';
    }
    
    document.getElementById('task-table').innerHTML = html;
}

async function loadTeam() {
    const res = await fetch('api.php?action=get_users');
    const d = await res.json();
    let html = '';
    
    if (d.data) {
        d.data.forEach(u => {
            const initials = u.full_name ? u.full_name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() : '??';
            const roleRaw = u.role || 'Unknown';
            const roleDisplay = roleRaw.replace('_', ' ').toUpperCase();
            
            // Badge Styling
            let badgeClass = 'bg-white text-dark';
            if (roleRaw === 'super_admin') badgeClass = 'bg-danger text-white border-danger';
            else if (roleRaw === 'admin') badgeClass = 'bg-dark text-white border-dark';
            else if (roleRaw === 'manager') badgeClass = 'bg-warning text-dark border-dark';

            // Avatar Logic
            let avatarHtml = u.avatar 
                ? `<img src="${u.avatar}" class="rounded-circle border border-2 border-dark" style="width: 80px; height: 80px; object-fit: cover;">`
                : `<div class="d-flex align-items-center justify-content-center border border-2 border-dark bg-white rounded-circle" style="width: 80px; height: 80px; font-weight: 700;">${initials}</div>`;

            // --- PERMISSION LOGIC START ---
            let actions = '';
            
            // Prevent editing yourself
            if (u.id != currentUser.id) {
                let canEdit = false;

                // 1. Super Admin: Controls Everyone
                if (currentUser.role === 'super_admin') {
                    canEdit = true;
                } 
                // 2. Admin: Controls Managers & Sales Reps
                else if (currentUser.role === 'admin') {
                    if (u.role !== 'super_admin' && u.role !== 'admin') {
                        canEdit = true;
                    }
                }
                // 3. Manager: Controls Sales Reps ONLY (Added this block)
                else if (currentUser.role === 'manager') {
                    if (u.role === 'sales_rep') {
                        canEdit = true;
                    }
                }

                if (canEdit) {
                    // Note: Managers usually don't need the "Change Role" button since 
                    // they can only assign 'Sales Rep' anyway. 
                    // So for Managers, we can optionally hide the Role button or show it.
                    // This code shows both buttons if they have permission.
                    
                    actions = `
                        <div class="d-flex gap-2 mt-3">
                            ${currentUser.role !== 'manager' ? 
                            `<button class="btn btn-sm btn-outline-dark flex-grow-1 fw-bold" onclick="openRoleModal(${u.id}, '${u.role}')">
                                <i class="bi bi-shield-lock"></i> ROLE
                            </button>` : ''}
                            
                            <button class="btn btn-sm btn-outline-danger fw-bold flex-grow-1" onclick="deleteItem('users', ${u.id})">
                                REMOVE ACCESS
                            </button>
                        </div>
                    `;
                }
            }
            // --- PERMISSION LOGIC END ---

            html += `
            <div class="col-12 col-md-6 col-lg-4 mb-4">
                <div class="card-neo h-100 text-center p-4">
                    <div class="d-flex justify-content-center mb-3">${avatarHtml}</div>
                    <h5 class="fw-bold text-uppercase mb-1 brand-font">${u.full_name}</h5>
                    <div class="text-muted small font-monospace mb-3 text-break">${u.email}</div>
                    <div><span class="badge ${badgeClass} border border-2 rounded-0 px-3 py-2 fw-bold small">${roleDisplay}</span></div>
                    ${actions}
                </div>
            </div>`;
        });
    }
    document.getElementById('team-grid').innerHTML = html || '<p class="text-center">No team found.</p>';
}
        function loadTeamMembers() {
            const dropdowns = ['taskAssignSelect','dealAssignSelect','editTaskAssignSelect','editDealAssignSelect'];
            fetch('api.php?action=get_users').then(r=>r.json()).then(d => {
                let opts = d.data.map(u => `<option value="${u.id}">${u.full_name}</option>`).join('');
                dropdowns.forEach(id => { if(document.getElementById(id)) document.getElementById(id).innerHTML = opts; });
            });
        }

        function autoFillDeal(select) {
            const price = select.options[select.selectedIndex].getAttribute('data-price');
            const prodName = select.options[select.selectedIndex].getAttribute('data-name');
            const custSelect = document.getElementById('dealCustomerSelect');
            const custName = custSelect.options[custSelect.selectedIndex].text.split('(')[0].trim();
            if(price) {
                document.getElementById('deal_value').value = price;
                document.getElementById('deal_title').value = `${prodName} - ${custName}`;
            }
        }

        function checkNotifications() {
            fetch('api.php?action=get_notifications').then(r=>r.json()).then(d => {
                if(d.status === 'success') {
                    if(d.count > 0) { document.getElementById('notif-count').innerText = d.count; document.getElementById('notif-count').style.display='flex'; document.getElementById('notif-text').innerText=d.count+" URGENT"; }
                    else { document.getElementById('notif-count').style.display='none'; document.getElementById('notif-text').innerText="NO ALERTS"; }
                }
            });
        }

        function initCharts() {
            fetch('api.php?action=get_chart_data').then(r=>r.json()).then(d => {
                if(d.status === 'success') {
                    Chart.defaults.font.family = "'Courier New', monospace";
                    Chart.defaults.color = '#000';
                    Chart.defaults.maintainAspectRatio = false; // Make charts responsive
                    
                    new Chart(document.getElementById('salesChart'), { 
                        type: 'line', 
                        data: { 
                            labels: d.trend.map(x=>x.m), 
                            datasets: [{ 
                                label: 'REVENUE ($)', 
                                data: d.trend.map(x=>x.t), 
                                borderColor: '#000', 
                                backgroundColor: '#000',
                                borderWidth: 3,
                                pointBackgroundColor: '#fff',
                                pointBorderColor: '#000',
                                pointBorderWidth: 2,
                                pointRadius: 5,
                                tension: 0 
                            }] 
                        },
                        options: { 
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: { x: { grid: { color: '#ccc' } }, y: { grid: { color: '#ccc', borderDash: [5, 5] } } } 
                        }
                    });
                    
                    new Chart(document.getElementById('productsChart'), { 
                        type: 'doughnut', 
                        data: { 
                            labels: Object.keys(d.stages), 
                            datasets: [{ 
                                data: Object.values(d.stages), 
                                backgroundColor: ['#000', '#666', '#ccc', '#fff'],
                                borderColor: '#000',
                                borderWidth: 3
                            }] 
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    });
                }
            });
        }

        function initCalendar() {
            calendarInstance = new FullCalendar.Calendar(document.getElementById('calendar'), {
                initialView: 'dayGridMonth', 
                headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listWeek' },
                themeSystem: 'standard',
                height: 'auto', // Responsive height
                events: function(info, success) {
                    fetch('api.php?action=get_tasks').then(r => r.json()).then(d => {
                        success(d.data.map(t => ({ 
                            title: t.title, 
                            start: t.due_date, 
                            backgroundColor: t.status=='Completed' ? '#000' : '#fff',
                            textColor: t.status=='Completed' ? '#fff' : '#000',
                            borderColor: '#000'
                        })));
                    });
                }
            });
            calendarInstance.render();
        }

        function initKanban() {
            ['lead','proposal','negotiation','closed'].forEach(s => {
                new Sortable(document.getElementById('col-'+s), {
                    group: 'deals', animation: 150, onEnd: function(evt) {
                        const dealId = evt.item.getAttribute('data-id');
                        const newStage = evt.to.id.split('-')[1].charAt(0).toUpperCase() + evt.to.id.split('-')[1].slice(1);
                        const fd = new FormData(); fd.append('deal_id', dealId); fd.append('new_stage', newStage);
                        fetch('api.php?action=update_deal_stage', {method:'POST', body:fd}).then(()=>loadStats()); 
                    }
                });
            });
        }

        function openEditCustomer(id) {
            fetch(`api.php?action=get_customer&id=${id}`).then(r=>r.json()).then(d => {
                const c = d.data;
                document.getElementById('edit_cust_id').value = c.id;
                document.getElementById('edit_cust_fname').value = c.first_name;
                document.getElementById('edit_cust_lname').value = c.last_name;
                document.getElementById('edit_cust_email').value = c.email;
                document.getElementById('edit_cust_company').value = c.company;
                document.getElementById('edit_cust_val').value = c.potential_value;
                document.getElementById('edit_cust_status').value = c.status;
                new bootstrap.Modal(document.getElementById('editCustomerModal')).show();
            });
        }

        function openEditProduct(id) {
            fetch(`api.php?action=get_product&id=${id}`).then(r=>r.json()).then(d => {
                const p = d.data;
                document.getElementById('edit_prod_id').value = p.id;
                document.getElementById('edit_prod_name').value = p.name;
                document.getElementById('edit_prod_type').value = p.type;
                document.getElementById('edit_prod_price').value = p.price;
                document.getElementById('edit_prod_desc').value = p.description;
                new bootstrap.Modal(document.getElementById('editProductModal')).show();
            });
        }

        function openEditTask(id) {
            fetch(`api.php?action=get_task&id=${id}`).then(r=>r.json()).then(d => {
                const t = d.data;
                document.getElementById('edit_task_id').value = t.id;
                document.getElementById('edit_task_title').value = t.title;
                document.getElementById('edit_task_desc').value = t.description;
                document.getElementById('edit_task_date').value = t.due_date;
                document.getElementById('edit_task_status').value = t.status;
                document.getElementById('editTaskAssignSelect').value = t.assigned_to;
                new bootstrap.Modal(document.getElementById('editTaskModal')).show();
            });
        }

        function openEditDeal(id) {
            fetch(`api.php?action=get_deal&id=${id}`).then(r=>r.json()).then(d => {
                const deal = d.data;
                document.getElementById('edit_deal_id').value = deal.id;
                document.getElementById('edit_deal_title').value = deal.title;
                document.getElementById('edit_deal_value').value = deal.value;
                document.getElementById('edit_deal_stage').value = deal.stage;
                document.getElementById('edit_deal_date').value = deal.due_date;
                document.getElementById('editDealAssignSelect').value = deal.assigned_to;
                new bootstrap.Modal(document.getElementById('editDealModal')).show();
                document.getElementById('history-list').innerHTML = '<p class="text-center text-muted">Loading...</p>';
            });
        }

        function loadDealHistory() {
            const id = document.getElementById('edit_deal_id').value;
            fetch(`api.php?action=get_audit_history&type=deal&id=${id}`).then(r=>r.json()).then(d => {
                let h = ''; 
                if(d.data && d.data.length > 0) {
                    d.data.forEach(l => {
                        h += `<div class="border-bottom border-dark py-2">
                            <b>${l.full_name}</b> changed <b>${l.field_name}</b> 
                            from <span class="text-decoration-line-through">${l.old_value}</span> 
                            to <span class="fw-bold">${l.new_value}</span> 
                            <small class="text-muted d-block">${l.date}</small>
                        </div>`;
                    });
                } else {
                    h = '<p class="text-center text-muted py-3">NO HISTORY</p>';
                }
                document.getElementById('history-list').innerHTML = h;
            });
        }

        function quickUpdateStatus(id, status) {
            const fd = new FormData(); fd.append('task_id', id); fd.append('task_status', status);
            openEditTask(id);
        }
let pendingDelete = null;
function deleteItem(type, id) {
    // Store data for the "Yes" button to use
    pendingDelete = { type: type, id: id };
    
    // Open the Custom Modal
    new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
}

        function exportData(type) { window.location.href = `api.php?action=export_data&type=${type}`; }
        function showWebFormModal() { new bootstrap.Modal(document.getElementById('webFormModal')).show(); document.getElementById('embedCode').value = `<form action="public_api.php" method="POST"><input type="hidden" name="api_key" value="crm_secret_key_123"><input name="email" required><button>Submit</button></form>`; }
        
        function openNotes(type, id) {
            document.getElementById('note_related_to').value = type; document.getElementById('note_related_id').value = id;
            fetch(`api.php?action=get_notes&type=${type}&id=${id}`).then(r=>r.json()).then(d => {
                let h = ''; if(d.data) d.data.forEach(n => h+=`<div class="note-item"><b>${n.note}</b><div class="note-meta">${n.full_name} • ${n.nice_date}</div></div>`);
                document.getElementById('notes-list').innerHTML = h || 'NO NOTES';
            });
            new bootstrap.Modal(document.getElementById('notesModal')).show();
        }

function setupForm(id, action, modal, refresh) {
    const form = document.getElementById(id);
    if(!form) return; // Safety check

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        
        fetch('api.php?action=' + action, {method: 'POST', body: fd})
        .then(r => r.json())
        .then(d => {
            if(d.status === 'success') { 
                // Close Modal
                if(modal) {
                    const modalEl = document.getElementById(modal);
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if(modalInstance) modalInstance.hide();
                }

                // Refresh Data
                if(id === 'formAddNote') { 
                    this.reset(); 
                    // Assuming openNotes handles the refresh logic internally or logic exists elsewhere
                    const relatedTo = document.getElementById('note_related_to').value;
                    const relatedId = document.getElementById('note_related_id').value;
                    // If you have a specific function to reload notes, call it here
                    openNotes(relatedTo, relatedId); 
                } else { 
                    this.reset(); 
                    if(refresh) refresh(); 
                }

                // SHOW SUCCESS TOAST
                showToast('Action successful', 'success');

            } else { 
                // SHOW ERROR TOAST (Instead of Alert)
                showToast(d.message || 'An error occurred', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Connection failed', 'error');
        });
    });
}

        setupForm('formCustomer', 'add_customer', 'modalCustomer', loadCustomers);
        setupForm('formEditCustomer', 'update_customer', 'editCustomerModal', loadCustomers);
        setupForm('formProduct', 'add_product', 'modalProduct', loadProducts);
        setupForm('formEditProduct', 'update_product', 'editProductModal', loadProducts);
        setupForm('formDeal', 'add_deal', 'modalDeal', loadPipeline);
        setupForm('formEditDeal', 'update_deal', 'editDealModal', loadPipeline);
        setupForm('formTask', 'add_task', 'addTaskModal', loadTasks);
        setupForm('formEditTask', 'update_task', 'editTaskModal', loadTasks);
        setupForm('formUser', 'add_user', 'modalUser', loadTeam);
        setupForm('formAddNote', 'add_note', null, null); 

        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            let icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
            toast.className = `toast-neo toast-${type}`;
            toast.innerHTML = `
                <div class="d-flex align-items-center gap-2">
                    <i class="bi ${icon}"></i> 
                    <span>${message}</span>
                </div>
                <button onclick="this.parentElement.remove()" style="background:none; border:none; font-weight:bold; font-size:1.2rem;">&times;</button>
            `;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
        // Open Modal and Populate Options based on WHO is logged in
function openRoleModal(userId, currentRole) {
    document.getElementById('role_user_id').value = userId;
    const select = document.getElementById('role_select');
    select.innerHTML = '';

    // Options available to set
    let options = [
        {val: 'sales_rep', txt: 'Sales Rep'},
        {val: 'manager', txt: 'Manager'}
    ];

    // Only Super Admin or Admin can see 'Admin' option
    if (currentUser.role === 'super_admin' || currentUser.role === 'admin') {
        options.push({val: 'admin', txt: 'Admin'});
    }

    // Only Super Admin can see 'Super Admin' option
    if (currentUser.role === 'super_admin') {
        options.push({val: 'super_admin', txt: 'Super Admin'});
    }

    // Build HTML
    options.forEach(opt => {
        let sel = (opt.val === currentRole) ? 'selected' : '';
        select.innerHTML += `<option value="${opt.val}" ${sel}>${opt.txt}</option>`;
    });

    new bootstrap.Modal(document.getElementById('modalChangeRole')).show();
}
function openViewTask(id) {
    // 1. Fetch Details via API
    fetch(`api.php?action=get_task_detail&id=${id}`)
        .then(r => r.json())
        .then(d => {
            if(d.status === 'success') {
                const t = d.data;

                // 2. Populate Fields
                document.getElementById('view_task_id').value = t.id;
                document.getElementById('view_task_title').innerText = t.title;
                document.getElementById('view_task_date').innerText = t.due_date;
                document.getElementById('view_task_desc').innerText = t.description || 'No description provided.';
                
                // 3. Status Styling
                const statusEl = document.getElementById('view_task_status');
                statusEl.innerText = t.status;
                statusEl.className = 'badge rounded-0 border border-dark text-uppercase px-3 py-2 fs-6 ';
                if(t.status === 'Completed') statusEl.classList.add('bg-success', 'text-white');
                else if(t.status === 'In Progress') statusEl.classList.add('bg-warning', 'text-dark');
                else statusEl.classList.add('bg-secondary', 'text-white');

                // 4. Assignee Avatar Logic
                const avatarContainer = document.getElementById('view_task_avatar');
                if (t.assigned_avatar) {
                    avatarContainer.innerHTML = `<img src="${t.assigned_avatar}" class="rounded-circle border border-dark" style="width: 35px; height: 35px; object-fit: cover;">`;
                } else {
                    const initials = t.assigned_name ? t.assigned_name.substring(0, 2).toUpperCase() : '??';
                    avatarContainer.innerHTML = `<div class="d-flex align-items-center justify-content-center border border-dark bg-white rounded-circle fw-bold" style="width: 35px; height: 35px; font-size:0.8rem;">${initials}</div>`;
                }
                document.getElementById('view_task_assignee').innerText = t.assigned_name || 'Unassigned';

                // 5. Related To Logic
                let relatedText = 'None';
                if(t.related_to && t.related_id) {
                    const type = t.related_to.charAt(0).toUpperCase() + t.related_to.slice(1); // Capitalize
                    relatedText = `${type}: ${t.related_name}`;
                }
                document.getElementById('view_task_related').innerText = relatedText;

                // 6. Show Modal
                new bootstrap.Modal(document.getElementById('viewTaskModal')).show();
            }
        });
}

// Helper to switch from View -> Edit
function switchToEditTask() {
    const id = document.getElementById('view_task_id').value;
    // Close View Modal
    bootstrap.Modal.getInstance(document.getElementById('viewTaskModal')).hide();
    // Open Edit Modal (uses your existing function)
    openEditTask(id);
}
// Attach Form Listener
setupForm('formChangeRole', 'update_user_role', 'modalChangeRole', loadTeam);
    </script>
</body>
</html>