<?php
// ============================================================
// core/helpers.php  — Global utility functions
// ============================================================

require_once __DIR__ . '/../config/config.php';

/**
 * Redirect to a URL and exit.
 */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/**
 * Redirect to an app page with optional GET params.
 * redirect_to('appointments', 'list', ['status' => 'pending'])
 */
function redirect_to(string $page, string $action = '', array $params = []): never
{
    $url = BASE_URL . '/index.php?page=' . urlencode($page);
    if ($action) $url .= '&action=' . urlencode($action);
    foreach ($params as $k => $v) {
        $url .= '&' . urlencode($k) . '=' . urlencode($v);
    }
    redirect($url);
}

/**
 * Safely outputs a value escaping HTML special characters.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * Sets a flash message in the session.
 *
 * @param string $type    'success' | 'danger' | 'warning' | 'info'
 * @param string $message Human-readable message
 */
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Sanitizes a string (trim + strip tags).
 */
function sanitize(string $value): string
{
    return trim(strip_tags($value));
}

/**
 * Formats a DATE string as a readable date (e.g. "Mon, 20 Jan 2025").
 */
function formatDate(string $date): string
{
    if (!$date) return '—';
    return date('D, d M Y', strtotime($date));
}

/**
 * Formats a TIME string as 12-hour (e.g. "09:00 AM").
 */
function formatTime(string $time): string
{
    if (!$time) return '—';
    return date('h:i A', strtotime($time));
}

/**
 * Returns a Bootstrap badge class for an appointment status.
 */
function statusBadge(string $status): string
{
    return match($status) {
        'pending'   => 'warning',
        'confirmed' => 'info',
        'completed' => 'success',
        'cancelled' => 'danger',
        default     => 'secondary',
    };
}

/**
 * Returns an icon class for a role.
 */
function roleIcon(string $role): string
{
    return match($role) {
        'admin'   => 'fas fa-user-shield',
        'doctor'  => 'fas fa-user-md',
        'patient' => 'fas fa-user',
        default   => 'fas fa-user',
    };
}

/**
 * Truncates a string to a max length with ellipsis.
 */
function truncate(string $text, int $length = 60): string
{
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '…';
}

/**
 * Returns the avatar URL for a user, or a default placeholder.
 */
function avatarUrl(?string $avatar): string
{
    if ($avatar && file_exists(UPLOAD_AVATARS . $avatar)) {
        return BASE_URL . '/index.php?page=users&action=avatar&file=' . urlencode($avatar);
    }
    return BASE_URL . '/public/assets/adminlte/dist/img/user2-160x160.jpg';
}

/**
 * Returns the doctor photo URL or default.
 */
function doctorPhotoUrl(?string $photo): string
{
    if ($photo && file_exists(UPLOAD_DOCTOR_PHOTOS . $photo)) {
        return BASE_URL . '/index.php?page=doctors&action=photo&file=' . urlencode($photo);
    }
    return BASE_URL . '/public/assets/adminlte/dist/img/user1-128x128.jpg';
}

/**
 * Builds a query string preserving existing GET params and overriding specified ones.
 */
function buildQuery(array $overrides = []): string
{
    $params = array_merge($_GET, $overrides);
    return http_build_query($params);
}
