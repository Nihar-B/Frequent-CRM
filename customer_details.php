<?php
require_once 'functions.php';
require_once 'db.php';
start_secure_session();

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ERROR: MISSING_ID. <a href='index.php'>RETURN</a>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sarder Solutions | Customer File</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        /* --- NEO-BRUTALIST VARIABLES --- */
        :root {
            --bg-body: #ffffff;
            --text-main: #000000;
            --accent: #b084fc;
            --border-width: 2px;
            --radius: 8px;
            --shadow-hard: 4px 4px 0 #000000;
            --shadow-hover: 6px 6px 0 #000000;
        }

        /* --- LAYOUT: FULL HEIGHT (Desktop Default) --- */
        html, body {
            height: 100%;
            overflow: hidden; /* Desktop: Prevent body scroll, handle inside cols */
        }

        body { 
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            background-image: radial-gradient(#e5e7eb 1px, transparent 1px);
            background-size: 24px 24px;
            color: var(--text-main);
            display: flex;
            flex-direction: column;
        }

        h1, h2, h3, h4, h5, h6 { font-family: 'Space Mono', monospace; font-weight: 700; letter-spacing: -0.03em; }

        /* Fixed Header */
        .page-header { 
            background: #fff; 
            padding: 20px 30px; 
            border-bottom: var(--border-width) solid #000; 
            flex: 0 0 auto; /* Don't shrink */
            z-index: 10;
        }

        /* Main Content Wrapper */
        .content-wrapper {
            flex: 1 1 auto;
            overflow: hidden;
            padding: 20px 24px 0;
        }

        /* Columns scroll independently on desktop */
        .scroll-col {
            height: 100%;
            overflow-y: auto;
            padding-bottom: 40px; 
            padding-right: 10px; 
        }

        /* Activity Card stretches to full height on desktop */
        .card-neo.full-height {
            height: 98%; 
            display: flex;
            flex-direction: column;
            margin-bottom: 0;
        }

        /* Feed fills remaining card space */
        #activity_feed {
            flex-grow: 1;
            overflow-y: auto;
            max-height: none !important;
        }

        /* --- CUSTOM SCROLLBAR --- */
        ::-webkit-scrollbar { width: 12px; }
        ::-webkit-scrollbar-track { background: #fff; border-left: 2px solid #000; }
        ::-webkit-scrollbar-thumb { background: #000; border: 2px solid #fff; border-radius: 0; }
        ::-webkit-scrollbar-thumb:hover { background: var(--accent); }

        .back-link { text-decoration: none; color: #000; font-weight: 700; text-transform: uppercase; border: var(--border-width) solid #000; padding: 8px 16px; background: #fff; border-radius: var(--radius); box-shadow: 3px 3px 0 #000; display: inline-block; margin-bottom: 20px; font-size: 0.8rem; transition: 0.1s; }
        .back-link:hover { transform: translate(-2px, -2px); box-shadow: 5px 5px 0 #000; background: var(--accent); }
        
        /* --- PROFILE PICTURE --- */
        .profile-container { 
            width: 70px; 
            height: 70px; 
            min-width: 70px; 
            border: 2px solid #000; 
            border-radius: 50%; 
            overflow: hidden; 
            box-shadow: 4px 4px 0 var(--accent);
            cursor: pointer;
            transition: transform 0.1s;
            background: #fff;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .profile-container:hover { transform: scale(1.05); }
        
        .profile-initials { 
            width: 100%; height: 100%; 
            display: flex; align-items: center; justify-content: center; 
            font-family: 'Space Mono'; font-weight: 700; font-size: 1.5rem; 
            background: #fff; color: #000; 
        }
        
        .profile-img { 
            width: 100%; height: 100%; 
            object-fit: cover; object-position: center; 
            display: block;
        }

        .stat-box { border: var(--border-width) solid #000; border-radius: var(--radius); background: #fff; padding: 15px; margin-top: 15px; box-shadow: 3px 3px 0 #000; position: relative; overflow: hidden; height: 100%; }
        .stat-label { font-family: 'Space Mono', monospace; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; color: #555; margin-bottom: 5px; }
        .stat-val { font-weight: 700; font-size: 1.1rem; transition: color 0.3s; word-break: break-all; }
        
        .score-update { animation: flashGreen 1s ease-out; }
        @keyframes flashGreen { 0% { background-color: #4ade80; } 100% { background-color: #fff; } }

        .card-neo { background: #fff; border: var(--border-width) solid #000; border-radius: var(--radius); box-shadow: var(--shadow-hard); padding: 0; margin-bottom: 30px; overflow: hidden; }
        .card-header-custom { display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; border-bottom: var(--border-width) solid #000; background: #f9fafb; flex-shrink: 0; }
        .card-title { font-family: 'Space Mono', monospace; font-weight: 700; text-transform: uppercase; font-size: 1rem; }

        .table { margin-bottom: 0; }
        .table thead th { background: #000; color: #fff; font-family: 'Space Mono', monospace; font-weight: 700; text-transform: uppercase; font-size: 0.8rem; padding: 12px 15px; border: none; }
        .table td { border-bottom: 1px solid #000; padding: 15px; vertical-align: middle; font-weight: 500; }
        /* Responsive Table Wrapper */
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }

        .btn-neo { background: #000; color: #fff; border: var(--border-width) solid #000; border-radius: var(--radius); font-weight: 700; text-transform: uppercase; font-size: 0.85rem; padding: 8px 16px; box-shadow: 3px 3px 0 var(--accent); transition: 0.1s; }
        .btn-neo:hover { transform: translate(-2px, -2px); box-shadow: 5px 5px 0 var(--accent); background: #222; color: #fff; }
        .btn-outline-neo { background: #fff; color: #000; border: var(--border-width) solid #000; border-radius: var(--radius); font-weight: 700; text-transform: uppercase; font-size: 0.85rem; padding: 8px 16px; box-shadow: 3px 3px 0 #000; transition: 0.1s; }
        .btn-outline-neo:hover { transform: translate(-2px, -2px); box-shadow: 5px 5px 0 #000; background: var(--accent); }

        .timeline-item { border: var(--border-width) solid #000; border-radius: var(--radius); background: #fff; padding: 15px; margin-bottom: 15px; box-shadow: 3px 3px 0 #ccc; animation: slideIn 0.3s; }
        
        .form-control, .form-select { border: var(--border-width) solid #000; border-radius: var(--radius); box-shadow: 3px 3px 0 #e5e7eb; font-weight: 500; padding: 10px; }
        .form-control:focus, .form-select:focus { box-shadow: 4px 4px 0 #000; border-color: #000; outline: none; }

        .modal-content { border: var(--border-width) solid #000; border-radius: var(--radius); box-shadow: 10px 10px 0 #000; }
        .modal-header { background: var(--accent); border-bottom: var(--border-width) solid #000; }
        .modal-title { font-family: 'Space Mono', monospace; font-weight: 700; }
        
        .btn-action { border: none; background: transparent; padding: 4px 8px; transition: 0.2s; border-radius: 4px; }
        .btn-action:hover { background: #eee; transform: scale(1.1); }
        
        .new-entry { animation: highlight 2s ease-out; }
        @keyframes highlight { 0% { background-color: #fff9c4; } 100% { background-color: #fff; } }

        #toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; }
        .toast-neo { min-width: 300px; background: #fff; border: 3px solid #000; padding: 15px; font-family: 'Space Mono', monospace; font-weight: 700; box-shadow: 6px 6px 0 #000; animation: slideIn 0.3s; display: flex; align-items: center; justify-content: space-between; }
        .toast-success { background: #4ade80; color: #000; }
        .toast-error { background: #ff4d4d; color: #fff; border-color: #000; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

       /* --- NEO-TABS STYLING --- */
        .neo-tabs { border-bottom: 2px solid #000; padding: 10px 10px 0 10px; background: #f9fafb; gap: 4px; overflow-x: auto; flex-wrap: nowrap; }
        .neo-tabs .nav-link { border: 2px solid #000; border-bottom: none; color: #000; font-family: 'Space Mono', monospace; font-weight: 700; text-transform: uppercase; border-radius: 8px 8px 0 0; margin-bottom: -2px; background: #fff; transition: transform 0.2s, background 0.2s; font-size: 0.85rem; white-space: nowrap; }
        .neo-tabs .nav-link:hover { background: var(--accent); transform: translateY(-2px); }
        .neo-tabs .nav-link.active { background: #000; color: #fff; transform: translateY(-2px); z-index: 2; }
        .tab-action-bar { display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; border-bottom: 2px solid #000; background: #fff; }

        /* --- RESPONSIVE MEDIA QUERIES --- */
        @media (max-width: 991.98px) {
            /* Unlock the fixed height on mobile so content scrolls naturally */
            html, body {
                height: auto;
                overflow-y: auto;
                overflow-x: hidden;
            }
            
            .content-wrapper {
                padding: 15px;
                overflow: visible; /* Allow overflow */
                flex: none; /* Disable flex growing/shrinking */
            }

            .scroll-col {
                height: auto;
                overflow: visible;
                padding-bottom: 20px;
                padding-right: 0;
            }

            .card-neo.full-height {
                height: 500px; /* Give activity log a fixed height on mobile, rather than screen height */
                margin-top: 20px;
            }

            /* Wrap header elements */
            .page-header {
                padding: 15px;
            }
            .page-header .d-flex.justify-content-between {
                flex-wrap: wrap;
                gap: 15px;
            }
            .page-header .d-flex.gap-2.align-items-start {
                width: 100%;
                justify-content: flex-start;
            }
        }

        @media (max-width: 576px) {
            /* Stack buttons on very small screens */
            .page-header .d-flex.gap-2.align-items-start {
                flex-wrap: wrap;
            }
            .btn-neo, .btn-outline-neo {
                flex-grow: 1;
                text-align: center;
            }
        }
    </style>
</head>
<body>

    <div class="page-header">
        <div class="container-fluid px-0">
            <div class="d-flex justify-content-between align-items-start">
                <div style="width: 100%;">
                    <a href="index.php" class="back-link"><i class="bi bi-arrow-left me-1"></i> Dashboard</a>
                    <div class="d-flex align-items-center gap-3 mt-2">
                        
                        <div class="profile-container" onclick="document.getElementById('customerAvatarInput').click()" title="Click to upload photo">
                            <div id="c_avatar_display" style="width:100%; height:100%;"></div>
                        </div>
                        <input type="file" id="customerAvatarInput" style="display:none;" accept="image/*" onchange="uploadCustomerAvatar()">
                        
                        <div style="min-width: 0;"> <h2 class="m-0 text-break" id="c_name">LOADING...</h2>
                            <span class="badge bg-white text-dark border border-2 border-dark rounded-0 px-2 py-1 mt-1" id="c_company" style="font-family: 'Space Mono';">...</span>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex gap-2 align-items-start mt-3 mt-lg-0">
                    <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] !== 'sales_rep'): ?>
                        <button class="btn btn-danger fw-bold border-2 border-dark" onclick="deleteCustomer()" title="Delete Customer">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    <?php endif; ?>
                    <button class="btn btn-outline-neo" data-bs-toggle="modal" data-bs-target="#emailModal">
                        <i class="bi bi-envelope-fill me-1"></i> Email
                    </button>
                    <button class="btn btn-neo" data-bs-toggle="modal" data-bs-target="#callModal">
                        <i class="bi bi-telephone-fill me-1"></i> Log Call
                    </button>
                </div>
            </div>
            
            <div class="row mt-4 g-3">
                <div class="col-6 col-md-4 col-lg-2"><div class="stat-box"><div class="stat-label">STATUS</div><div class="stat-val" id="c_status">-</div></div></div>
                <div class="col-6 col-md-4 col-lg-2"><div class="stat-box" id="score-box"><div class="stat-label">LEAD SCORE</div><div class="stat-val text-primary" id="c_score">-</div></div></div>
                <div class="col-12 col-md-4 col-lg-4"><div class="stat-box"><div class="stat-label">EMAIL</div><div class="stat-val text-break" style="font-size: 0.95rem;" id="c_email">-</div></div></div>
                <div class="col-6 col-md-6 col-lg-2"><div class="stat-box"><div class="stat-label">LIFETIME REV</div><div class="stat-val text-success" id="c_ltv">...</div></div></div>
                <div class="col-6 col-md-6 col-lg-2"><div class="stat-box"><div class="stat-label">NET PROFIT</div><div class="stat-val text-success" id="c_profit">...</div></div></div>
            </div>
        </div>
    </div>

    <div class="content-wrapper">
        <div class="row h-100">
            
            <div class="col-12 col-lg-8 h-100">
                <div class="scroll-col">
                    
                    <div class="card-neo" style="min-height: 500px;">
                        <ul class="nav nav-tabs neo-tabs" id="customerTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="deals-tab" data-bs-toggle="tab" data-bs-target="#tab-deals" type="button" role="tab">
                                    <i class="bi bi-coin me-1"></i> Opportunities
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tab-tasks" type="button" role="tab">
                                    <i class="bi bi-list-check me-1"></i> Tasks
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="files-tab" data-bs-toggle="tab" data-bs-target="#tab-files" type="button" role="tab">
                                    <i class="bi bi-paperclip me-1"></i> Files
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="customerTabsContent">
                            
                            <div class="tab-pane fade show active" id="tab-deals" role="tabpanel">
                                <div class="tab-action-bar">
                                    <div class="fw-bold">ACTIVE DEALS</div>
                                    <button class="btn btn-sm btn-neo" onclick="window.location.href='index.php'">+ NEW DEAL</button>
                                </div>
                                <div class="p-0 table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead><tr><th>TITLE</th><th>VALUE</th><th>STAGE</th><th>PROFIT</th></tr></thead>
                                        <tbody id="list_deals">
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab-tasks" role="tabpanel">
                                <div class="tab-action-bar">
                                    <div class="fw-bold">PENDING ACTIONS</div>
                                    <button class="btn btn-sm btn-outline-neo" data-bs-toggle="modal" data-bs-target="#addTaskModal">+ ADD TASK</button>
                                </div>
                                <div id="list_tasks" class="p-0">
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab-files" role="tabpanel">
                                <div class="tab-action-bar">
                                    <div class="fw-bold">ATTACHMENTS</div>
                                    <div>
                                        <button class="btn btn-sm btn-neo" onclick="triggerFileUpload()">UPLOAD FILE</button> 
                                        <input type="file" id="fileInput" style="display:none;" onchange="uploadFile()">
                                        <input type="file" id="replaceFileInput" style="display:none;" onchange="executeFileReplace()">
                                        <input type="hidden" id="replaceFileId">
                                    </div>
                                </div>
                                <div class="p-3">
                                    <div class="row" id="list_files">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            <div class="col-12 col-lg-4 h-100">
                <div class="card-neo full-height">
                    <div class="card-header-custom" style="background: #000; color: #fff;">
                        <div class="card-title text-white">ACTIVITY LOG</div>
                    </div>
                    <div class="p-3 bg-light border-bottom border-2 border-dark flex-shrink-0">
                        <textarea id="new_note" class="form-control mb-2" rows="3" placeholder="TYPE NOTE HERE..."></textarea>
                        <button class="btn btn-sm btn-neo w-100" onclick="postNote()">POST ENTRY >></button>
                    </div>
                    <div id="activity_feed" class="p-3"></div>
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="addTaskModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">CREATE TASK</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><label class="fw-bold text-uppercase mb-1">Subject</label><input type="text" id="t_title" class="form-control mb-3" placeholder="Follow up..."><label class="fw-bold text-uppercase mb-1">Due Date</label><input type="date" id="t_date" class="form-control mb-3"><button class="btn btn-neo w-100" onclick="createTask()">SAVE TASK</button></div></div></div></div>
    
    <div class="modal fade" id="emailModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">COMPOSE EMAIL</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="row mb-3"><div class="col-md-4"><label class="fw-bold text-uppercase mb-1">Template</label><select id="emailTemplate" class="form-select" onchange="loadTemplate()"><option value="">-- SELECT --</option></select></div><div class="col-md-8"><label class="fw-bold text-uppercase mb-1">Subject Line</label><input type="text" id="emailSubject" class="form-control"></div></div><div class="mb-3"><label class="fw-bold text-uppercase mb-1">Message Body</label><textarea id="emailBody" class="form-control" rows="10"></textarea></div></div><div class="modal-footer border-top border-2 border-dark bg-light"><button class="btn btn-outline-neo" data-bs-dismiss="modal">CANCEL</button><button class="btn btn-neo" onclick="sendEmail()">TRANSMIT >></button></div></div></div></div>
    
    <div class="modal fade" id="callModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">LOG PHONE CALL</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="mb-3"><label class="fw-bold text-uppercase mb-1">Call Outcome</label><select id="callOutcome" class="form-select"><option value="Connected">Connected</option><option value="Voicemail">Left Voicemail</option><option value="No Answer">No Answer</option><option value="Wrong Number">Wrong Number</option></select></div><div class="mb-3"><label class="fw-bold text-uppercase mb-1">Call Notes</label><textarea id="callNotes" class="form-control" rows="3" placeholder="Summary of conversation..."></textarea></div><button class="btn btn-neo w-100" onclick="saveCallLog()">SAVE LOG</button></div></div></div></div>

    <div id="toast-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const customerId = <?php echo $_GET['id']; ?>;
        
        document.addEventListener('DOMContentLoaded', () => { loadData(); loadEmailTemplates(); });

        async function loadData() {
            try {
                const res = await fetch(`api.php?action=get_customer_360&id=${customerId}`);
                const d = await res.json();
                
                if(d.status !== 'success') { 
                    showToast('Customer not found', 'error'); 
                    return; 
                }

                const c = d.customer;
                const formatMoney = (amount) => '$' + parseFloat(amount || 0).toLocaleString(undefined, {minimumFractionDigits: 2});

                // --- AVATAR LOGIC ---
                const avatarContainer = document.getElementById('c_avatar_display');
                if (c.avatar && c.avatar !== "") {
                    avatarContainer.innerHTML = `<img src="${c.avatar}?t=${new Date().getTime()}" class="profile-img">`;
                } else {
                    const initials = (c.first_name[0] + c.last_name[0]).toUpperCase();
                    avatarContainer.innerHTML = `<div class="profile-initials">${initials}</div>`;
                }

                // Stats
                document.getElementById('c_name').innerText = c.first_name + ' ' + c.last_name;
                document.getElementById('c_company').innerText = c.company || 'UNKNOWN';
                document.getElementById('c_status').innerHTML = `<span class="badge bg-dark text-white rounded-0 border border-dark">${c.status}</span>`;
                
                const scoreEl = document.getElementById('c_score');
                const oldScore = scoreEl.innerText;
                if(oldScore !== c.score.toString() && oldScore !== '-') {
                    document.getElementById('score-box').classList.add('score-update');
                    setTimeout(() => document.getElementById('score-box').classList.remove('score-update'), 1000);
                }
                scoreEl.innerText = c.score;
                document.getElementById('c_email').innerText = c.email;
                document.getElementById('c_ltv').innerText = formatMoney(c.ltv);
                document.getElementById('c_profit').innerText = formatMoney(c.total_profit);

                // Deals
                const dealList = document.getElementById('list_deals');
                dealList.innerHTML = '';
                if(d.deals && d.deals.length > 0) {
                    d.deals.forEach(deal => {
                        const isClosed = deal.stage === 'Closed';
                        const stageSelect = `<select class="form-select form-select-sm fw-bold ${isClosed ? 'bg-success text-white' : ''}" style="border: 2px solid #000; border-radius: 4px; min-width: 140px;" onchange="changeDealStage(${deal.id}, this.value)"><option value="Lead" ${deal.stage==='Lead'?'selected':''}>LEAD</option><option value="Proposal" ${deal.stage==='Proposal'?'selected':''}>PROPOSAL</option><option value="Negotiation" ${deal.stage==='Negotiation'?'selected':''}>NEGOTIATION</option><option value="Closed" ${deal.stage==='Closed'?'selected':''}>CLOSED</option></select>`;
                        dealList.innerHTML += `<tr><td class="fw-bold">${deal.title.toUpperCase()}</td><td>${formatMoney(deal.value)}</td><td style="width:180px">${stageSelect}</td><td class="text-success fw-bold">${formatMoney(deal.profit)}</td></tr>`;
                    });
                } else { dealList.innerHTML = '<tr><td colspan="4" class="text-center fw-bold text-muted py-3">NO DATA AVAILABLE</td></tr>'; }

                // Tasks
                const taskList = document.getElementById('list_tasks');
                taskList.innerHTML = '';
                if (d.tasks && d.tasks.length > 0) {
                    d.tasks.forEach(t => {
                        taskList.innerHTML += `<div class="d-flex align-items-center justify-content-between p-2 border-bottom border-dark"><div><i class="bi bi-check-square me-2"></i> ${t.title}</div><span class="badge bg-danger text-white rounded-0 border border-dark">${t.due_date}</span></div>`;
                    });
                } else { taskList.innerHTML = '<div class="text-center text-muted small fst-italic p-3">No pending tasks.</div>'; }

                // Files
                const fileList = document.getElementById('list_files');
                fileList.innerHTML = '';
                if (d.files && d.files.length > 0) {
                    d.files.forEach(f => {
                        fileList.innerHTML += `<div class="col-12 mb-2"><div class="border border-2 border-dark p-2 bg-white d-flex align-items-center justify-content-between shadow-sm" style="border-radius: 6px;"><div class="d-flex align-items-center text-truncate"><i class="bi bi-file-earmark-text-fill me-2 fs-4 text-primary"></i> <a href="${f.filepath}" target="_blank" class="text-dark fw-bold text-decoration-none text-uppercase text-truncate">${f.filename}</a></div><div class="d-flex gap-1"><button class="btn-action text-dark" onclick="triggerReplace(${f.id})" title="Replace"><i class="bi bi-arrow-repeat"></i></button><button class="btn-action text-danger" onclick="deleteFile(${f.id})" title="Delete"><i class="bi bi-trash-fill"></i></button></div></div></div>`;
                    });
                } else { fileList.innerHTML = '<div class="text-center text-muted w-100 small fst-italic">No files uploaded.</div>'; }

                // Feed
                const feed = document.getElementById('activity_feed');
                feed.innerHTML = '';
                if (d.notes) {
                    d.notes.forEach((n, index) => {
                        let icon = 'bi-chat-left-text-fill'; let color = 'bg-white';
                        if(n.type === 'Email') { icon = 'bi-envelope-fill'; color = 'bg-warning-subtle'; }
                        let highlightClass = (index === 0 && window.freshEntry) ? 'new-entry' : '';
                        feed.innerHTML += `<div class="timeline-item ${color} ${highlightClass}"><div class="d-flex justify-content-between border-bottom border-dark pb-2 mb-2"><strong><i class="bi ${icon} me-1"></i> ${n.full_name}</strong><small class="text-muted">${n.date}</small></div><div class="text-break" style="font-size: 0.95rem;">${n.note}</div></div>`;
                    });
                    
                    if(window.freshEntry) {
                        feed.scrollTop = 0; 
                        window.freshEntry = false; 
                    }
                }
            } catch (err) { console.error("Load Data Error:", err); }
        }

        // --- ACTIONS ---
        function deleteFile(fileId) { 
            if(!confirm("Are you sure?")) return; 
            const fd = new FormData(); fd.append('file_id', fileId); 
            fetch('api.php?action=delete_file', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(d => { 
                if(d.status === 'success') {
                    showToast('File deleted successfully', 'success');
                    loadData(); 
                } else {
                    showToast('Error deleting file', 'error');
                }
            }); 
        }

        function triggerReplace(fileId) { document.getElementById('replaceFileId').value = fileId; document.getElementById('replaceFileInput').click(); }
        
        function executeFileReplace() { 
            const input = document.getElementById('replaceFileInput'); 
            const fileId = document.getElementById('replaceFileId').value; 
            if(input.files.length === 0) return; 
            const fd = new FormData(); fd.append('file', input.files[0]); fd.append('file_id', fileId); 
            fetch('api.php?action=replace_file', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(d => { 
                if(d.status === 'success') { 
                    showToast('File replaced successfully', 'success');
                    loadData(); 
                    input.value = ''; 
                } else { 
                    showToast("Error: " + d.message, 'error');
                } 
            }); 
        }

        function triggerFileUpload() { document.getElementById('fileInput').click(); }
        
        function uploadFile() { 
            const input = document.getElementById('fileInput'); 
            if(input.files.length === 0) return; 
            const fd = new FormData(); fd.append('file', input.files[0]); fd.append('related_to', 'customer'); fd.append('related_id', customerId); 
            fetch('api.php?action=upload_file', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(d => { 
                if(d.status === 'success') { 
                    showToast('File uploaded successfully', 'success');
                    loadData(); 
                    input.value = ''; 
                } else { 
                    showToast('Upload failed: ' + d.message, 'error');
                } 
            }); 
        }

        function changeDealStage(dealId, newStage) { 
            const fd = new FormData(); fd.append('deal_id', dealId); fd.append('new_stage', newStage); 
            fetch('api.php?action=update_deal_stage', {method: 'POST', body: fd})
            .then(r=>r.json())
            .then(d => { 
                if(d.status === 'success') {
                    showToast('Deal stage updated', 'success');
                    loadData(); 
                }
            }); 
        }
        
        function uploadCustomerAvatar() {
            const input = document.getElementById('customerAvatarInput');
            if(input.files.length === 0) return;
            const fd = new FormData(); fd.append('avatar', input.files[0]); fd.append('customer_id', customerId);
            fetch('api.php?action=upload_customer_avatar', { method: 'POST', body: fd }).then(r => r.json()).then(d => { if(d.status === 'success') { showToast('Avatar Updated', 'success'); loadData(); } else { showToast(d.message, 'error'); } });
        }

        function postNote() { 
            const note = document.getElementById('new_note').value; 
            if(!note) return; 
            const fd = new FormData(); fd.append('related_to', 'customer'); fd.append('related_id', customerId); fd.append('note', note); 
            fetch('api.php?action=add_note', {method:'POST', body:fd}).then(() => { document.getElementById('new_note').value = ''; window.freshEntry = true; loadData(); showToast('Note posted', 'success'); }); 
        }
        
        function createTask() { 
            const title = document.getElementById('t_title').value; 
            const date = document.getElementById('t_date').value; 
            if(!title || !date) { 
                showToast('Please fill all task fields', 'error');
                return; 
            } 
            const fd = new FormData(); fd.append('task_title', title); fd.append('task_date', date); fd.append('related_to', 'customer'); fd.append('related_id', customerId); 
            fetch('api.php?action=add_task', {method:'POST', body:fd})
            .then(r => r.json())
            .then(d => { 
                if(d.status === 'success') { 
                    bootstrap.Modal.getInstance(document.getElementById('addTaskModal')).hide(); 
                    showToast('Task created successfully', 'success');
                    loadData(); 
                } 
            }); 
        }

        function loadEmailTemplates() { fetch('api.php?action=get_email_templates').then(r=>r.json()).then(d => { const sel = document.getElementById('emailTemplate'); if(sel && d.data) d.data.forEach(t => sel.innerHTML += `<option value="${t.id}">${t.name}</option>`); }); }
        
        function loadTemplate() { const tid = document.getElementById('emailTemplate').value; if(!tid) return; const fd = new FormData(); fd.append('template_id', tid); fd.append('customer_id', customerId); fetch('api.php?action=generate_email_preview', {method:'POST', body:fd}).then(r=>r.json()).then(d => { if(d.status === 'success') { document.getElementById('emailSubject').value = d.subject; document.getElementById('emailBody').value = d.body; } }); }
        
        function sendEmail() { 
            const sub = document.getElementById('emailSubject').value; 
            const bod = document.getElementById('emailBody').value; 
            if(!sub || !bod) return showToast('Subject and Body required', 'error');
            
            const fd = new FormData(); fd.append('customer_id', customerId); fd.append('subject', sub); fd.append('body', bod); 
            fetch('api.php?action=send_email_mock', {method:'POST', body:fd})
            .then(r=>r.json())
            .then(d => { 
                bootstrap.Modal.getInstance(document.getElementById('emailModal')).hide(); 
                window.freshEntry = true; 
                loadData(); 
                showToast('TRANSMISSION SUCCESSFUL', 'success');
            }); 
        }
        
        function saveCallLog() {
            const outcome = document.getElementById('callOutcome').value;
            const notes = document.getElementById('callNotes').value;
            if(!notes) { 
                showToast("Please enter call notes", 'error');
                return; 
            }
            const formattedNote = `📞 <strong>Call Logged: ${outcome}</strong><br>${notes}`;
            const fd = new FormData(); fd.append('related_to', 'customer'); fd.append('related_id', customerId); fd.append('note', formattedNote);
            fetch('api.php?action=add_note', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(d => { 
                if(d.status === 'success') { 
                    bootstrap.Modal.getInstance(document.getElementById('callModal')).hide(); 
                    document.getElementById('callNotes').value = ''; 
                    window.freshEntry = true; 
                    loadData(); 
                    showToast('Call logged successfully', 'success');
                } else { 
                    showToast('Error saving log', 'error');
                } 
            });
        }

        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            let icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
            toast.className = `toast-neo toast-${type}`;
            toast.innerHTML = `<div class="d-flex align-items-center gap-2"><i class="bi ${icon}"></i> <span>${message}</span></div><button onclick="this.parentElement.remove()" style="background:none; border:none; font-weight:bold; font-size:1.2rem;">&times;</button>`;
            container.appendChild(toast);
            setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateX(100%)'; setTimeout(() => toast.remove(), 300); }, 4000);
        }

        function deleteCustomer() {
            if (!confirm("⚠️ ARE YOU SURE?\n\nThis will remove the customer and archive their data. This action cannot be undone immediately.")) {
                return;
            }

            const fd = new FormData();
            fd.append('type', 'customers'); 
            fd.append('id', customerId);

            fetch('api.php?action=delete_item', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(d => {
                if (d.status === 'success') {
                    showToast('Customer Deleted Successfully', 'success');
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1500);
                } else {
                    showToast('Error: ' + d.message, 'error');
                }
            })
            .catch(err => {
                console.error("Deletion Error:", err);
                showToast('System Error during deletion', 'error');
            });
        }
    </script>
</body>
</html>