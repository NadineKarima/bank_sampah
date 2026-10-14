<?php
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect(dashboard_url_for($_SESSION['role']));
} else {
    redirect('auth/login.php');
}
