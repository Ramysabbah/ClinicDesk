<?php
// ============================================================
// index.php  — Front Controller / Router
// ============================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/CSRF.php';
require_once __DIR__ . '/core/helpers.php';

$page   = sanitize($_GET['page']   ?? 'dashboard');
$action = sanitize($_GET['action'] ?? 'index');
$method = $_SERVER['REQUEST_METHOD'];

// ============================================================
// Route table  [page][action] => [ControllerFile, method]
// ============================================================
$routes = [

    'auth' => [
        'login'                  => ['AuthController', 'showLogin'],
        'login_post'             => ['AuthController', 'handleLogin'],
        'logout'                 => ['AuthController', 'handleLogout'],
        'logout_post'            => ['AuthController', 'handleLogout'],
        'change_password'        => ['AuthController', 'showChangePassword'],
        'change_password_post'   => ['AuthController', 'handleChangePassword'],
    ],

    'dashboard' => [
        'index' => ['DashboardController', 'show'],
    ],

    'users' => [
        'index'          => ['UserController', 'index'],
        'create'         => ['UserController', 'create'],
        'store'          => ['UserController', 'store'],
        'edit'           => ['UserController', 'edit'],
        'update'         => ['UserController', 'update'],
        'toggle_active'  => ['UserController', 'toggleActive'],
        'reset_password' => ['UserController', 'resetPassword'],
        'avatar'         => ['UserController', 'serveAvatar'],
    ],

    'doctors' => [
        'index'  => ['DoctorController', 'index'],
        'edit'   => ['DoctorController', 'edit'],
        'update' => ['DoctorController', 'update'],
        'photo'  => ['DoctorController', 'servePhoto'],
    ],

    'appointments' => [
        'book'          => ['AppointmentController', 'book'],
        'store'         => ['AppointmentController', 'store'],
        'my'            => ['AppointmentController', 'myAppointments'],
        'schedule'      => ['AppointmentController', 'schedule'],
        'list'          => ['AppointmentController', 'adminList'],
        'detail'        => ['AppointmentController', 'detail'],
        'update_status' => ['AppointmentController', 'updateStatus'],
        'cancel'        => ['AppointmentController', 'cancel'],
    ],

    'prescriptions' => [
        'add'      => ['PrescriptionController', 'add'],
        'store'    => ['PrescriptionController', 'store'],
        'my'       => ['PrescriptionController', 'myPrescriptions'],
        'download' => ['PrescriptionController', 'download'],
    ],

    'reports' => [
        'index' => ['ReportController', 'index'],
    ],
];

// ============================================================
// For POST requests: prefer action_post variant if defined
// ============================================================
if ($method === 'POST' && isset($routes[$page][$action . '_post'])) {
    $action = $action . '_post';
}

// ============================================================
// Dispatch
// ============================================================
if (isset($routes[$page][$action])) {

    [$controllerClass, $controllerMethod] = $routes[$page][$action];
    $file = __DIR__ . '/controllers/' . $controllerClass . '.php';

    if (file_exists($file)) {
        require_once $file;
        (new $controllerClass())->$controllerMethod();
    } else {
        http_response_code(404);
        require_once __DIR__ . '/views/errors/404.php';
    }

} elseif ($page === '' || ($page === 'dashboard' && $action === 'index')) {
    require_once __DIR__ . '/controllers/DashboardController.php';
    (new DashboardController())->show();

} else {
    // Not found — send to login or 404
    if (!Auth::check()) {
        redirect(BASE_URL . '/index.php?page=auth&action=login');
    }
    http_response_code(404);
    require_once __DIR__ . '/views/errors/404.php';
}
