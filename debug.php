<?php
// ============================================================
// debug.php — Delete this file after fixing the issue!
// Visit: http://localhost/clinicdesk/debug.php
// ============================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>ClinicDesk — Debug</title>
<style>
  body { font-family: monospace; background:#1a1a2e; color:#e0e0e0; padding:20px; }
  .ok  { color:#22c55e; } .fail { color:#ef4444; } .warn { color:#f59e0b; }
  h2   { color:#4f7df3; border-bottom:1px solid #333; padding-bottom:6px; }
  pre  { background:#0d0d1a; padding:12px; border-radius:8px; }
  .box { background:#0d0d1a; border:1px solid #333; border-radius:8px; padding:14px; margin:10px 0; }
</style>
</head>
<body>
<h1>🔍 ClinicDesk Debug Report</h1>

<h2>1. PHP Version</h2>
<div class="box">
<?php
$v = phpversion();
$ok = version_compare($v, '8.0', '>=');
echo $ok
  ? "<span class='ok'>✓ PHP $v (OK)</span>"
  : "<span class='fail'>✗ PHP $v — Need 8.0+</span>";
?>
</div>

<h2>2. Config File</h2>
<div class="box">
<?php
$cfgPath = __DIR__ . '/config/config.php';
if (file_exists($cfgPath)) {
    echo "<span class='ok'>✓ config/config.php found</span><br>";
    require_once $cfgPath;
    echo "<b>APP_NAME:</b> " . APP_NAME . "<br>";
    echo "<b>BASE_URL:</b> <span style='color:#facc15'>" . BASE_URL . "</span><br>";
    $detected = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
              . '://' . $_SERVER['HTTP_HOST']
              . dirname($_SERVER['SCRIPT_NAME']);
    echo "<b>Detected URL:</b> <span style='color:#38bdf8'>" . $detected . "</span><br>";
    if (rtrim(BASE_URL,'/') !== rtrim($detected,'/')) {
        echo "<br><span class='fail'>⚠ BASE_URL MISMATCH!</span><br>";
        echo "Open <b>config/config.php</b> and change BASE_URL to:<br>";
        echo "<pre>define('BASE_URL', '" . $detected . "');</pre>";
    } else {
        echo "<span class='ok'>✓ BASE_URL matches</span>";
    }
} else {
    echo "<span class='fail'>✗ config/config.php NOT FOUND</span>";
}
?>
</div>

<h2>3. Database Connection</h2>
<div class="box">
<?php
$dbPath = __DIR__ . '/config/database.php';
if (file_exists($dbPath)) {
    require_once $dbPath;
    try {
        $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            echo "<span class='fail'>✗ Connection failed: " . $conn->connect_error . "</span><br><br>";
            echo "Check <b>config/database.php</b>:<br>";
            echo "<pre>DB_HOST = '" . DB_HOST . "'
DB_NAME = '" . DB_NAME . "'
DB_USER = '" . DB_USER . "'
DB_PASS = '" . DB_PASS . "'</pre>";
            if (DB_NAME !== 'clinicdesk_db') {
                echo "<span class='fail'>⚠ DB_NAME is not 'clinicdesk_db'</span>";
            }
        } else {
            echo "<span class='ok'>✓ Database connected: " . DB_NAME . "</span><br>";
            $r = $conn->query("SHOW TABLES");
            $tables = [];
            while($row = $r->fetch_row()) $tables[] = $row[0];
            $required = ['users','specializations','doctors','appointments','prescriptions'];
            foreach ($required as $t) {
                echo in_array($t, $tables)
                  ? "<span class='ok'>  ✓ Table: $t</span><br>"
                  : "<span class='fail'>  ✗ MISSING table: $t — Run clinicdesk_db.sql!</span><br>";
            }
            // Check admin user
            $rr = $conn->query("SELECT email, is_active FROM users WHERE role='admin' LIMIT 1");
            $admin = $rr ? $rr->fetch_assoc() : null;
            echo "<br>";
            echo $admin
              ? "<span class='ok'>✓ Admin user: " . $admin['email'] . "</span>"
              : "<span class='fail'>✗ No admin user found — Run clinicdesk_db.sql again</span>";
        }
    } catch (Exception $e) {
        echo "<span class='fail'>✗ " . $e->getMessage() . "</span>";
    }
} else {
    echo "<span class='fail'>✗ config/database.php NOT FOUND</span>";
}
?>
</div>

<h2>4. Folder Structure</h2>
<div class="box">
<?php
$check = [
    'index.php'                        => 'Main router',
    'config/config.php'                => 'App config',
    'config/database.php'              => 'DB config',
    'core/Database.php'                => 'Database class',
    'core/Auth.php'                    => 'Auth class',
    'controllers/AuthController.php'   => 'Auth controller',
    'controllers/DashboardController.php' => 'Dashboard controller',
    'views/auth/login.php'             => 'Login view',
    'clinicdesk_db.sql'                => 'SQL file',
];
foreach ($check as $file => $label) {
    $exists = file_exists(__DIR__ . '/' . $file);
    echo $exists
      ? "<span class='ok'>✓ $file</span> <span style='color:#555'>($label)</span><br>"
      : "<span class='fail'>✗ $file MISSING!</span> <span style='color:#555'>($label)</span><br>";
}
?>
</div>

<h2>5. Upload Folders (Write Permission)</h2>
<div class="box">
<?php
$dirs = ['public/uploads/avatars','public/uploads/doctor_photos','public/uploads/prescriptions'];
foreach ($dirs as $d) {
    $path = __DIR__ . '/' . $d;
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
        echo "<span class='warn'>⚠ Created: $d</span><br>";
    } elseif (!is_writable($path)) {
        echo "<span class='fail'>✗ Not writable: $d — run chmod 755</span><br>";
    } else {
        echo "<span class='ok'>✓ $d</span><br>";
    }
}
?>
</div>

<h2>6. Session Test</h2>
<div class="box">
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['debug_test'] = 'ok';
echo isset($_SESSION['debug_test'])
  ? "<span class='ok'>✓ Sessions working</span>"
  : "<span class='fail'>✗ Sessions not working</span>";
?>
</div>

<h2>✅ Quick Fix Summary</h2>
<div class="box">
<?php
$allOk = true;

// BASE_URL check
if (defined('BASE_URL')) {
    $detected = (isset($_SERVER['HTTPS']) ? 'https' : 'http')
              . '://' . $_SERVER['HTTP_HOST']
              . dirname($_SERVER['SCRIPT_NAME']);
    if (rtrim(BASE_URL,'/') !== rtrim($detected,'/')) {
        echo "<span class='fail'>1. Fix BASE_URL in config/config.php → change to: $detected</span><br>";
        $allOk = false;
    }
}
if ($allOk) echo "<span class='ok'>Everything looks good! Delete this file and go to: <a href='" . (defined('BASE_URL') ? BASE_URL : '/') . "' style='color:#4f7df3'>" . (defined('BASE_URL') ? BASE_URL : '/') . "</a></span>";
?>
</div>

<p style="color:#555; font-size:12px; margin-top:30px;">
  ⚠ Delete debug.php after you fix the issue — it exposes sensitive information.
</p>
</body>
</html>
