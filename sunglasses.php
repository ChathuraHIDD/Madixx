<?php

declare(strict_types=1);

if (empty($_GET['category'])) {
    $_GET['category'] = 'sunglasses';
}

require __DIR__ . '/shop.php';
