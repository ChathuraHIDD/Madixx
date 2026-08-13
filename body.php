<?php

declare(strict_types=1);

if (empty($_GET['category'])) {
    $_GET['category'] = 'body-care';
}

require __DIR__ . '/shop.php';
