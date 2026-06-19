<?php
// file: api/auth_helper.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Ensures a valid admin session exists.
 */
function verifyAdminSession(): void {
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden: Admin session required']);
        exit;
    }
}

/**
 * Validates if the current local time is within shift constraints.
 * Supports overnight shifts spanning midnight.
 */
function isWithinShift(?string $start, ?string $end): bool {
    if (!$start || !$end || $start === '00:00:00' && $end === '00:00:00' || $start === '' || $end === '') {
        return true; // Unrestricted hours
    }

    date_default_timezone_set('Asia/Colombo');
    $current = date('H:i:s');

    if ($start <= $end) {
        return ($current >= $start && $current <= $end);
    } else {
        // Overnight shift (crosses midnight)
        return ($current >= $start || $current <= $end);
    }
}

/**
 * Enforces that the logged-in admin is within shift hours.
 * Super Admins are exempt from shift constraints.
 */
function checkShiftConstraint(): void {
    verifyAdminSession();

    $username = $_SESSION['username'] ?? '';
    $admin_role = $_SESSION['admin_role'] ?? '';
    if (strtolower($username) === 'admin' || $admin_role === 'super_admin') {
        return; // Exempt
    }

    $start = $_SESSION['shift_start'] ?? null;
    $end = $_SESSION['shift_end'] ?? null;

    if (!isWithinShift($start, $end)) {
        http_response_code(403);
        echo json_encode([
            'error' => 'Access denied: You are outside your shift hours.',
            'shift_error' => true,
            'shift_start' => $start,
            'shift_end' => $end
        ]);
        exit;
    }
}

/**
 * Enforces both shift hours and specific role permissions.
 * Allowed roles list can be empty, meaning only super_admin can access.
 */
function checkRolePermission(array $allowed_roles): void {
    checkShiftConstraint();

    $username = $_SESSION['username'] ?? '';
    $admin_role = $_SESSION['admin_role'] ?? '';
    if (strtolower($username) === 'admin' || $admin_role === 'super_admin') {
        return; // Super admin always allowed
    }

    if (!in_array($admin_role, $allowed_roles)) {
        http_response_code(403);
        echo json_encode([
            'error' => 'Permission denied: Your role does not allow this action.'
        ]);
        exit;
    }
}
