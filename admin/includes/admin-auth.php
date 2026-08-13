<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/init.php';

if (!is_admin()) {
    flash('error', 'Please log in as an administrator to continue.');
    redirect('admin/login.php');
}
