<?php
// 1. SUPPRESS HTML ERRORS
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
require_once 'db.php';
header('Content-Type: application/json');

function sendError($msg) {
    echo json_encode(['status' => 'error', 'message' => $msg]);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    sendError('Unauthorized');
}

$action = $_GET['action'] ?? '';
$uid = $_SESSION['user_id'];
$role = $_SESSION['user_role'];

try {
    switch ($action) {
        // Dashboard
        case 'get_dashboard_stats': getStats($pdo, $uid, $role); break;
        case 'get_chart_data': getChartData($pdo, $uid, $role); break;
        case 'get_notifications': getNotifications($pdo, $uid); break;

        // Customers
        case 'get_customers': getCustomers($pdo, $uid, $role); break;
        case 'get_customer': getSingleItem($pdo, 'customers', $_GET['id']); break;
        case 'add_customer': addCustomer($pdo, $uid); break;
        case 'update_customer': updateCustomer($pdo); break;
        case 'get_customer_360': getCustomer360($pdo); break;

        // Deals
        case 'get_deals': getDeals($pdo, $uid, $role); break;
        case 'get_deal': getSingleItem($pdo, 'deals', $_GET['id']); break;
        case 'add_deal': addDeal($pdo, $uid); break;
        case 'update_deal': updateDeal($pdo, $uid); break;
        case 'update_deal_stage': updateDealStage($pdo, $uid, $role); break;

        // Products
        case 'get_products': getProducts($pdo); break;
        case 'get_product': getSingleItem($pdo, 'products', $_GET['id']); break;
        case 'add_product': addProduct($pdo); break;
        case 'update_product': updateProduct($pdo); break;

        // Tasks
        case 'get_tasks': getTasks($pdo, $uid, $role); break;
        case 'get_task': getSingleItem($pdo, 'tasks', $_GET['id']); break;
        case 'add_task': addTask($pdo, $uid); break;
        case 'update_task': updateTask($pdo); break;

        // Utils
        case 'get_audit_history': getAuditHistory($pdo); break;
        case 'get_users': getUsers($pdo); break;
        case 'add_user': addUser($pdo, $role); break;
        case 'delete_item': deleteItem($pdo, $role); break;
        case 'export_data': exportData($pdo, $uid, $role); break;
        case 'get_notes': getNotes($pdo); break;
        case 'add_note': addNote($pdo, $uid); break;
        
        // Files
        case 'upload_file': uploadFile($pdo, $uid); break;
        case 'delete_file': deleteFile($pdo); break;
        case 'replace_file': replaceFile($pdo, $uid); break;
        
        // Email
        case 'get_email_templates': getEmailTemplates($pdo); break;
        case 'generate_email_preview': generateEmailPreview($pdo, $uid); break;
        case 'send_email_mock': sendEmailMock($pdo, $uid); break;

        case 'upload_avatar': uploadAvatar($pdo, $uid); break;
case 'upload_avatar': uploadAvatar($pdo, $uid); break;
case 'delete_avatar': deleteAvatar($pdo, $uid); break;

case 'global_search': globalSearch($pdo); break;

case 'upload_customer_avatar': uploadCustomerAvatar($pdo); break;

        default: sendError('Invalid action');
    }
} catch (Exception $e) {
    sendError($e->getMessage());
}

// --- FUNCTIONS ---

function getStats($pdo, $uid, $role) {
    $where = ($role === 'sales_rep') ? " AND assigned_to = $uid" : "";
    $c = $pdo->query("SELECT COUNT(*) FROM customers WHERE deleted_at IS NULL $where")->fetchColumn();
    $r = $pdo->query("SELECT SUM(value) FROM deals WHERE stage='Closed' AND deleted_at IS NULL $where")->fetchColumn();
    echo json_encode(['status' => 'success', 'data' => ['customers' => $c, 'revenue' => $r ?: 0]]);
}

function getChartData($pdo, $uid, $role) {
    $where = ($role === 'sales_rep') ? " AND assigned_to = $uid" : "";
    $stages = $pdo->query("SELECT stage, COUNT(*) as c FROM deals WHERE deleted_at IS NULL $where GROUP BY stage")->fetchAll(PDO::FETCH_KEY_PAIR);
    $trend = $pdo->query("SELECT DATE_FORMAT(created_at, '%b') as m, SUM(value) as t FROM deals WHERE stage='Closed' AND deleted_at IS NULL $where AND created_at > DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY m ORDER BY created_at")->fetchAll();
    echo json_encode(['status' => 'success', 'stages' => $stages, 'trend' => $trend]);
}

function updateDeal($pdo, $uid) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    $id = $_POST['deal_id'];
    $val = $_POST['deal_value'];
    
    $stmt = $pdo->prepare("SELECT * FROM deals WHERE id = ?");
    $stmt->execute([$id]);
    $old = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$old) { sendError('Deal not found'); }

    $cost = isset($old['cost']) ? $old['cost'] : 0;
    $newProfit = $val - $cost;

    logChange($pdo, $uid, 'deal', $id, 'Title', $old['title'], $_POST['deal_title']);
    logChange($pdo, $uid, 'deal', $id, 'Value', $old['value'], $val);
    
    $stmt = $pdo->prepare("UPDATE deals SET title=?, value=?, profit=?, stage=?, due_date=?, assigned_to=? WHERE id=?");
    $stmt->execute([$_POST['deal_title'], $val, $newProfit, $_POST['deal_stage'], $_POST['deal_date'], $_POST['assigned_to'], $id]);
    echo json_encode(['status' => 'success']);
}

function updateDealStage($pdo, $uid, $role) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    $id = $_POST['deal_id'];
    $newStage = $_POST['new_stage'];
    
    $old = $pdo->query("SELECT stage, customer_id FROM deals WHERE id=$id")->fetch(PDO::FETCH_ASSOC);
    if(!$old) sendError("Deal not found");

    if ($old['stage'] !== $newStage) {
        logChange($pdo, $uid, 'deal', $id, 'Stage', $old['stage'], $newStage);
    }
    
    $pdo->prepare("UPDATE deals SET stage = ? WHERE id = ?")->execute([$newStage, $id]);
    
    if ($newStage === 'Closed' && $old['customer_id']) {
        $pdo->prepare("UPDATE customers SET status='Active' WHERE id=?")->execute([$old['customer_id']]);
    }
    
    if ($old['customer_id']) updateCustomerScore($pdo, $old['customer_id']);
    echo json_encode(['status' => 'success']);
}

function getCustomer360($pdo) {
    $id = $_GET['id'];
    if(!$id) sendError('Missing ID');
    $c = $pdo->query("SELECT * FROM customers WHERE id=$id")->fetch(PDO::FETCH_ASSOC);
    if(!$c) sendError('Customer not found');
    $fin = $pdo->query("SELECT SUM(value) r, SUM(profit) p FROM deals WHERE customer_id=$id AND stage='Closed' AND deleted_at IS NULL")->fetch();
    $c['ltv'] = $fin['r'] ?: 0;
    $c['total_profit'] = $fin['p'] ?: 0;
    $deals = $pdo->query("SELECT * FROM deals WHERE customer_id=$id AND deleted_at IS NULL")->fetchAll();
    $tasks = $pdo->query("SELECT * FROM tasks WHERE related_to='customer' AND related_id=$id AND deleted_at IS NULL")->fetchAll();
    $notes = $pdo->query("SELECT n.*, u.full_name, DATE_FORMAT(n.created_at,'%b %d') date FROM notes n JOIN users u ON n.created_by=u.id WHERE related_to='customer' AND related_id=$id ORDER BY created_at DESC")->fetchAll();
    $files = $pdo->query("SELECT * FROM files WHERE related_to='customer' AND related_id=$id ORDER BY created_at DESC")->fetchAll();
    echo json_encode(['status'=>'success','customer'=>$c,'deals'=>$deals,'tasks'=>$tasks,'notes'=>$notes,'files'=>$files]);
}

function getSingleItem($pdo, $t, $id) {
    $allowed = ['customers', 'deals', 'products', 'tasks'];
    if(!in_array($t, $allowed)) return;
    $stmt = $pdo->prepare("SELECT * FROM $t WHERE id=?"); $stmt->execute([$id]);
    echo json_encode(['status'=>'success', 'data'=>$stmt->fetch(PDO::FETCH_ASSOC)]);
}

function getCustomers($pdo, $uid, $role) {
    $where = ($role === 'sales_rep') ? " AND c.assigned_to = $uid" : "";
    $sql = "SELECT c.*, u.full_name as owner_name FROM customers c LEFT JOIN users u ON c.assigned_to = u.id WHERE c.deleted_at IS NULL $where";
    if (!empty($_GET['search'])) {
        $term = "%".$_GET['search']."%";
        $sql .= " AND (c.first_name LIKE ? OR c.last_name LIKE ? OR c.company LIKE ?)";
        $stmt = $pdo->prepare($sql." ORDER BY c.created_at DESC"); 
        $stmt->execute([$term,$term,$term]);
    } else { 
        $stmt = $pdo->query($sql." ORDER BY c.created_at DESC"); 
    }
    echo json_encode(['status'=>'success', 'data'=>$stmt->fetchAll()]);
}

function addCustomer($pdo, $uid) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    $score = 10;
    $stmt = $pdo->prepare("INSERT INTO customers (first_name, last_name, email, company, status, source, potential_value, score, assigned_to) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['company'], $_POST['status'], $_POST['source'], $_POST['potential_value'], $score, $uid]);
    echo json_encode(['status'=>'success']);
}

function updateCustomer($pdo) {
    $stmt=$pdo->prepare("UPDATE customers SET first_name=?, last_name=?, email=?, company=?, status=?, potential_value=? WHERE id=?");
    $stmt->execute([$_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['company'], $_POST['status'], $_POST['potential_value'], $_POST['id']]);
    echo json_encode(['status'=>'success']);
}

function getDeals($pdo, $uid, $role) {
    $where = ($role === 'sales_rep') ? " AND d.assigned_to = $uid" : "";
    $sql = "SELECT d.*, c.company as customer_name, u.full_name as owner_name FROM deals d LEFT JOIN customers c ON d.customer_id = c.id LEFT JOIN users u ON d.assigned_to = u.id WHERE d.deleted_at IS NULL $where";
    echo json_encode(['status'=>'success', 'data'=>$pdo->query($sql)->fetchAll()]);
}

function addDeal($pdo, $uid) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    $pdo->beginTransaction();
    $prodId = $_POST['product_id'] ?? 0;
    $profit = 0;
    $cost = 0;
    if($prodId) {
        $p = $pdo->prepare("SELECT price, cost FROM products WHERE id=?"); $p->execute([$prodId]);
        $prod = $p->fetch();
        if($prod) { $profit = $prod['price'] - $prod['cost']; $cost = $prod['cost']; }
    }
    $assigned = $_POST['assigned_to'] ?: $uid;
    $stmt = $pdo->prepare("INSERT INTO deals (customer_id, title, value, cost, profit, stage, due_date, assigned_to) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->execute([$_POST['customer_id'], $_POST['deal_title'], $_POST['deal_value'], $cost, $profit, 'Lead', $_POST['deal_date'], $assigned]);
    $pdo->prepare("INSERT INTO tasks (title, description, due_date, status, assigned_to, related_to, related_id) VALUES (?, ?, ?, 'Pending', ?, 'deal', ?)")
        ->execute(["Close: ".$_POST['deal_title'], "System Generated", $_POST['deal_date'], $assigned, $pdo->lastInsertId()]);
    $pdo->commit();
    updateCustomerScore($pdo, $_POST['customer_id']);
    echo json_encode(['status'=>'success']);
}

function getProducts($pdo) { echo json_encode(['status'=>'success', 'data'=>$pdo->query("SELECT * FROM products WHERE deleted_at IS NULL ORDER BY name ASC")->fetchAll()]); }
function addProduct($pdo) { $pdo->prepare("INSERT INTO products (name,type,price,cost,description) VALUES (?,?,?,?,?)")->execute([$_POST['prod_name'],$_POST['prod_type'],$_POST['prod_price'],$_POST['prod_cost'],$_POST['prod_desc']]); echo json_encode(['status'=>'success']); }
function updateProduct($pdo) { $pdo->prepare("UPDATE products SET name=?, type=?, price=?, description=? WHERE id=?")->execute([$_POST['prod_name'],$_POST['prod_type'],$_POST['prod_price'],$_POST['prod_desc'],$_POST['prod_id']]); echo json_encode(['status'=>'success']); }

function getTasks($pdo, $uid, $role) {
    $where = ($role === 'sales_rep') ? " AND t.assigned_to = $uid" : "";
    $sql = "SELECT t.*, u.full_name as assigned_name FROM tasks t LEFT JOIN users u ON t.assigned_to=u.id WHERE t.deleted_at IS NULL $where";
    echo json_encode(['status'=>'success', 'data'=>$pdo->query($sql." ORDER BY t.due_date ASC")->fetchAll()]);
}

function addTask($pdo, $uid) {
    $relatedTo = $_POST['related_to'] ?? null;
    $relatedId = $_POST['related_id'] ?? null;
    $assigned  = $_POST['assigned_to'] ?: $uid;
    $sql = "INSERT INTO tasks (title, description, due_date, status, assigned_to, related_to, related_id) VALUES (?, ?, ?, 'Pending', ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$_POST['task_title'], $_POST['task_desc'] ?? '', $_POST['task_date'], $assigned, $relatedTo, $relatedId])) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Insert Failed']);
    }
}

function updateTask($pdo) { 
    $pdo->prepare("UPDATE tasks SET title=?, description=?, due_date=?, status=?, assigned_to=? WHERE id=?")
        ->execute([$_POST['task_title'], $_POST['task_desc'], $_POST['task_date'], $_POST['task_status'], $_POST['assigned_to'], $_POST['task_id']]); 
    echo json_encode(['status'=>'success']); 
}

function getAuditHistory($pdo) {
    $stmt = $pdo->prepare("SELECT a.*, u.full_name, DATE_FORMAT(a.created_at, '%b %d %h:%i%p') as date FROM audit_logs a JOIN users u ON a.user_id = u.id WHERE entity_type=? AND entity_id=? ORDER BY created_at DESC");
    $stmt->execute([$_GET['type'], $_GET['id']]);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
}

function logChange($pdo, $uid, $type, $id, $field, $old, $new) {
    if (trim((string)$old) !== trim((string)$new)) {
        $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, entity_type, entity_id, field_name, old_value, new_value) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$uid, $type, $id, $field, $old, $new]);
    }
}

function getUsers($pdo) { 
    // Added 'avatar' to the SELECT statement below
    echo json_encode([
        'status'=>'success', 
        'data'=>$pdo->query("SELECT id, full_name, email, role, avatar FROM users WHERE deleted_at IS NULL")->fetchAll()
    ]); 
}
function addUser($pdo, $role) { 
    // Security: Only Admins can add users
    if($role === 'sales_rep') { 
        sendError('Permission denied'); 
        return; 
    }

    // Default to sales_rep if role is missing
    $newRole = !empty($_POST['role']) ? $_POST['role'] : 'sales_rep';

    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
    
    // Execute with the 4 values
    $stmt->execute([
        $_POST['full_name'],
        $_POST['email'],
        password_hash($_POST['password'], PASSWORD_DEFAULT),
        $newRole // <--- This ensures the role is sent
    ]); 
    
    echo json_encode(['status'=>'success']); 
}
function deleteItem($pdo, $role) { 
    if($role==='sales_rep') { sendError('Permission denied'); return; }
    $pdo->prepare("UPDATE {$_POST['type']} SET deleted_at=NOW() WHERE id=?")->execute([$_POST['id']]); echo json_encode(['status'=>'success']); 
}
function getNotifications($pdo, $uid) { $stmt=$pdo->prepare("SELECT COUNT(*) FROM tasks WHERE assigned_to=? AND status='Pending' AND due_date<=CURDATE() AND deleted_at IS NULL"); $stmt->execute([$uid]); echo json_encode(['status'=>'success', 'count'=>$stmt->fetchColumn()]); }
function getNotes($pdo) { $stmt=$pdo->prepare("SELECT n.*, u.full_name, DATE_FORMAT(n.created_at,'%b %d %h:%i%p') date FROM notes n JOIN users u ON n.created_by=u.id WHERE related_to=? AND related_id=? ORDER BY created_at DESC"); $stmt->execute([$_GET['type'],$_GET['id']]); echo json_encode(['status'=>'success', 'data'=>$stmt->fetchAll()]); }

function addNote($pdo, $uid) { 
    $pdo->prepare("INSERT INTO notes (related_to,related_id,note,created_by,type) VALUES (?,?,?,?,'Note')")->execute([$_POST['related_to'],$_POST['related_id'],$_POST['note'],$uid]); 
    if ($_POST['related_to'] === 'customer') updateCustomerScore($pdo, $_POST['related_id']);
    echo json_encode(['status'=>'success']); 
}

function uploadFile($pdo, $uid) {
    $targetDir = __DIR__ . '/uploads/';
    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) { echo json_encode(['status' => 'error', 'message' => 'File error']); return; }
    $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', basename($_FILES['file']['name']));
    $targetPath = $targetDir . $fileName;
    $dbPath = 'uploads/' . $fileName;
    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
        $stmt = $pdo->prepare("INSERT INTO files (related_to, related_id, filename, filepath, uploaded_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['related_to'], $_POST['related_id'], basename($_FILES['file']['name']), $dbPath, $uid]);
        echo json_encode(['status' => 'success']);
    } else { echo json_encode(['status' => 'error', 'message' => 'Move failed']); }
}

function deleteFile($pdo) {
    $id = $_POST['file_id'];
    $stmt = $pdo->prepare("SELECT filepath FROM files WHERE id = ?"); $stmt->execute([$id]); $file = $stmt->fetch();
    if ($file) {
        $fullPath = __DIR__ . '/' . $file['filepath'];
        if (file_exists($fullPath)) unlink($fullPath);
        $pdo->prepare("DELETE FROM files WHERE id = ?")->execute([$id]);
        echo json_encode(['status' => 'success']);
    } else { echo json_encode(['status' => 'error', 'message' => 'File not found']); }
}

function replaceFile($pdo, $uid) {
    $id = $_POST['file_id'];
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) { echo json_encode(['status' => 'error', 'message' => 'Upload failed']); return; }
    $stmt = $pdo->prepare("SELECT filepath FROM files WHERE id = ?"); $stmt->execute([$id]); $oldFile = $stmt->fetch();
    if ($oldFile) { $oldPath = __DIR__ . '/' . $oldFile['filepath']; if (file_exists($oldPath)) unlink($oldPath); }
    $targetDir = __DIR__ . '/uploads/';
    $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', basename($_FILES['file']['name']));
    $targetPath = $targetDir . $fileName;
    $dbPath = 'uploads/' . $fileName;
    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
        $update = $pdo->prepare("UPDATE files SET filename = ?, filepath = ?, uploaded_at = NOW(), uploaded_by = ? WHERE id = ?");
        $update->execute([basename($_FILES['file']['name']), $dbPath, $uid, $id]);
        echo json_encode(['status' => 'success']);
    } else { echo json_encode(['status' => 'error', 'message' => 'Move failed']); }
}

function getEmailTemplates($pdo) { echo json_encode(['status' => 'success', 'data' => $pdo->query("SELECT * FROM email_templates")->fetchAll()]); }
function generateEmailPreview($pdo, $uid) {
    $tid = $_POST['template_id']; $cid = $_POST['customer_id'];
    $tpl = $pdo->query("SELECT * FROM email_templates WHERE id = $tid")->fetch();
    $cust = $pdo->query("SELECT * FROM customers WHERE id = $cid")->fetch();
    $user = $pdo->query("SELECT full_name FROM users WHERE id = $uid")->fetch();
    if (!$tpl || !$cust) { echo json_encode(['status'=>'error']); return; }
    $map = ['{First_Name}'=>$cust['first_name'], '{Last_Name}'=>$cust['last_name'], '{Company}'=>$cust['company'], '{Email}'=>$cust['email'], '{Owner_Name}'=>$user['full_name']];
    echo json_encode(['status' => 'success', 'subject' => strtr($tpl['subject'], $map), 'body' => strtr($tpl['body'], $map)]);
}
function sendEmailMock($pdo, $uid) {
    $note = "📧 <strong>Sent Email:</strong> {$_POST['subject']}<br><br><span class='text-muted small'>".nl2br(substr($_POST['body'], 0, 100))."...</span>";
    $pdo->prepare("INSERT INTO notes (related_to, related_id, note, created_by, type) VALUES ('customer', ?, ?, ?, 'Email')")->execute([$_POST['customer_id'], $note, $uid]);
    updateCustomerScore($pdo, $_POST['customer_id']);
    echo json_encode(['status' => 'success']);
}
function exportData($pdo,$uid,$role){ $type=$_GET['type']??'customers'; header('Content-Type: text/csv'); header('Content-Disposition: attachment; filename="'.$type.'.csv"'); $out=fopen('php://output','w'); if($type=='customers'){$sql="SELECT id,first_name,last_name,email,company,status FROM customers WHERE deleted_at IS NULL"; if($role=='sales_rep')$sql.=" AND assigned_to=$uid"; $q=$pdo->query($sql); while($r=$q->fetch(PDO::FETCH_ASSOC)) fputcsv($out,$r);} elseif($type=='deals'){$sql="SELECT id,title,value,stage,due_date FROM deals WHERE deleted_at IS NULL"; if($role=='sales_rep')$sql.=" AND assigned_to=$uid"; $q=$pdo->query($sql); while($r=$q->fetch(PDO::FETCH_ASSOC)) fputcsv($out,$r);} fclose($out); exit; }

function updateCustomerScore($pdo, $customerId) {
    $score = 10;
    $stmt = $pdo->prepare("SELECT email FROM customers WHERE id = ?"); $stmt->execute([$customerId]); $cust = $stmt->fetch();
    if ($cust && strpos($cust['email'], 'gmail') === false && strpos($cust['email'], 'yahoo') === false) $score += 20;
    $dealCount = $pdo->query("SELECT COUNT(*) FROM deals WHERE customer_id = $customerId AND deleted_at IS NULL")->fetchColumn();
    $score += ($dealCount * 10);
    $wonDeals = $pdo->query("SELECT COUNT(*) FROM deals WHERE customer_id = $customerId AND stage = 'Closed' AND deleted_at IS NULL")->fetchColumn();
    $score += ($wonDeals * 20);
    $notesCount = $pdo->query("SELECT COUNT(*) FROM notes WHERE related_to = 'customer' AND related_id = $customerId")->fetchColumn();
    $score += ($notesCount * 5);
    $pdo->prepare("UPDATE customers SET score = ? WHERE id = ?")->execute([$score, $customerId]);
    return $score;
}
function uploadAvatar($pdo, $uid) {
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'Upload failed']); return;
    }
    
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file type']); return;
    }

    $filename = "avatar_{$uid}_" . time() . ".$ext";
    $path = "uploads/$filename"; // Make sure 'uploads' folder exists

    if (move_uploaded_file($_FILES['avatar']['tmp_name'], __DIR__ . "/$path")) {
        $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?")->execute([$path, $uid]);
        $_SESSION['user_avatar'] = $path; // Update session
        echo json_encode(['status' => 'success', 'path' => $path]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Write permission denied']);
    }
}
function deleteAvatar($pdo, $uid) {
    // 1. Get current avatar path
    $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmt->execute([$uid]);
    $currentAvatar = $stmt->fetchColumn();

    // 2. Delete file if it exists and isn't a placeholder/external link
    // We check if file exists locally to avoid errors
    if ($currentAvatar && file_exists(__DIR__ . '/' . $currentAvatar)) {
        unlink(__DIR__ . '/' . $currentAvatar);
    }

    // 3. Update Database to NULL
    $pdo->prepare("UPDATE users SET avatar = NULL WHERE id = ?")->execute([$uid]);

    // 4. Update Session immediately (so header updates on refresh)
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['user_avatar'] = null;

    echo json_encode(['status' => 'success']);
}
function globalSearch($pdo) {
    // 1. Validate Input
    $q = trim($_GET['q'] ?? '');
    if (strlen($q) < 2) {
        echo json_encode(['status' => 'error', 'message' => 'Query too short']);
        return;
    }

    $term = "%$q%";
    $results = [
        'customers' => [],
        'deals'     => [],
        'tasks'     => [],
        'products'  => []
    ];

    // 2. Search Customers (Name, Company, Email)
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, company, email FROM customers 
                           WHERE (first_name LIKE ? OR last_name LIKE ? OR company LIKE ? OR email LIKE ?) 
                           AND deleted_at IS NULL LIMIT 5");
    $stmt->execute([$term, $term, $term, $term]);
    $results['customers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Search Deals (Title)
    $stmt = $pdo->prepare("SELECT id, title, value, stage FROM deals 
                           WHERE title LIKE ? AND deleted_at IS NULL LIMIT 5");
    $stmt->execute([$term]);
    $results['deals'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Search Tasks (Title, Description)
    $stmt = $pdo->prepare("SELECT id, title, status, due_date FROM tasks 
                           WHERE (title LIKE ? OR description LIKE ?) AND deleted_at IS NULL LIMIT 5");
    $stmt->execute([$term, $term]);
    $results['tasks'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Search Products (Name)
    $stmt = $pdo->prepare("SELECT id, name, price FROM products 
                           WHERE name LIKE ? AND deleted_at IS NULL LIMIT 5");
    $stmt->execute([$term]);
    $results['products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $results]);
}
function uploadCustomerAvatar($pdo) {
    // 1. Validate ID
    if (!isset($_POST['customer_id']) || empty($_POST['customer_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing Customer ID']); 
        return;
    }
    $cid = $_POST['customer_id'];

    // 2. Validate File
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'Upload failed']); 
        return;
    }
    
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file type (JPG, PNG, GIF only)']); 
        return;
    }

    // 3. Prepare Path
    $filename = "cust_{$cid}_" . time() . ".$ext";
    $targetDir = __DIR__ . "/uploads/";
    $dbPath = "uploads/$filename";

    // Ensure folder exists
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // 4. Move File & Update DB
    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetDir . $filename)) {
        
        // Remove old avatar if exists (Optional cleanup)
        $stmt = $pdo->prepare("SELECT avatar FROM customers WHERE id = ?");
        $stmt->execute([$cid]);
        $oldAv = $stmt->fetchColumn();
        if ($oldAv && file_exists(__DIR__ . "/" . $oldAv)) {
            @unlink(__DIR__ . "/" . $oldAv);
        }

        // Update Database
        $update = $pdo->prepare("UPDATE customers SET avatar = ? WHERE id = ?");
        $update->execute([$dbPath, $cid]);
        
        echo json_encode(['status' => 'success', 'path' => $dbPath]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Write permission denied']);
    }
}
?>