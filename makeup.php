<?php

declare(strict_types=1);

if (empty($_GET['category'])) {
    $_GET['category'] = 'makeup';
}

require __DIR__ . '/shop.php';
