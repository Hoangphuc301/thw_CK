<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $item = [
        'id'   => intval($_POST['id']),
        'name' => $_POST['name'],
        'price'=> floatval($_POST['price']),
        'img'  => $_POST['img'],
        'qty'  => 1
    ];

    $found = false;
    foreach ($_SESSION['cart'] as &$c) {
        if ($c['id'] === $item['id']) {
            $c['qty'] += 1;
            $found = true;
            break;
        }
    }

    if (!$found) {
        $_SESSION['cart'][] = $item;
    }

    header("Location: cart.php");
    exit();
}

header("Location: index.php");
exit();
