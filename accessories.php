<?php

declare(strict_types=1);

if (empty($_GET['category'])) {
    $_GET['category'] = 'accessories';
}

require __DIR__ . '/shop.php';
