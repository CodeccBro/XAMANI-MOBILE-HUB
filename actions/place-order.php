<?php
session_start();

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../checkout.php');
    exit;
}

// Verify CSRF token
if (
    empty($_SESSION['checkout_csrf']) ||
    !hash_equals(
        $_SESSION['checkout_csrf'],
        (string)($_POST['csrf_token'] ?? '')
    )
) {
    http_response_code(403);
    exit('Invalid checkout request. Please reload checkout.');
}

// Customer details
$name = trim($_POST['customer_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$paymentMethod = $_POST['payment_method'] ?? '';

$errors = [];

if ($name === '' || strlen($name) > 150) {
    $errors[] = 'Please enter a valid full name.';
}

if (
    !preg_match('/^\+?[0-9][0-9\s-]{8,23}$/', $phone) ||
    strlen($phone) > 25
) {
    $errors[] = 'Please enter a valid phone number.';
}

if ($address === '' || strlen($address) > 1000) {
    $errors[] = 'Please enter a valid delivery address.';
}

if (!in_array(
    $paymentMethod,
    ['cash_on_delivery', 'bank_transfer'],
    true
)) {
    $errors[] = 'Please select a payment method.';
}

// Validate cart
$cart = $_SESSION['cart'] ?? [];
$cartItems = [];

foreach ($cart as $id => $quantity) {
    $validId = filter_var($id, FILTER_VALIDATE_INT);
    $validQuantity = filter_var($quantity, FILTER_VALIDATE_INT);

    if (!$validId || $validId < 1 || !$validQuantity || $validQuantity < 1) {
        $errors[] = 'Your cart contains an invalid item.';
        break;
    }

    $cartItems[(int)$validId] = (int)$validQuantity;
}

if (!$cartItems) {
    $errors[] = 'Your shopping cart is empty.';
}

function checkoutError($errors, $name, $phone, $address, $paymentMethod)
{
    $_SESSION['checkout_errors'] = $errors;

    $_SESSION['checkout_old'] = [
        'customer_name' => $name,
        'phone' => $phone,
        'address' => $address,
        'payment_method' => $paymentMethod
    ];

    header('Location: ../checkout.php');
    exit;
}

if ($errors) {
    checkoutError($errors, $name, $phone, $address, $paymentMethod);
}

try {
    $pdo->beginTransaction();

    $ids = array_keys($cartItems);
    sort($ids, SORT_NUMERIC);

    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    // Lock product rows during order creation
    $stmt = $pdo->prepare("
        SELECT id, name, price, stock, status
        FROM products
        WHERE id IN ($placeholders)
        ORDER BY id
        FOR UPDATE
    ");

    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($products) !== count($ids)) {
        throw new RuntimeException(
            'One or more products are no longer available.'
        );
    }

    $orderItems = [];
    $totalCents = 0;

    foreach ($products as $product) {
        $id = (int)$product['id'];
        $quantity = $cartItems[$id];

        if (
            $product['status'] !== 'active' ||
            (int)$product['stock'] < $quantity
        ) {
            throw new RuntimeException(
                $product['name'] . ' does not have enough available stock.'
            );
        }

        // Convert DECIMAL prices to integer cents for calculation
        $priceParts = explode('.', (string)$product['price']);
        $unitCents = ((int)$priceParts[0] * 100)
            + (int)str_pad($priceParts[1] ?? '0', 2, '0');

        $lineCents = $unitCents * $quantity;
        $totalCents += $lineCents;

        $orderItems[] = [
            'product_id' => $id,
            'product_name' => $product['name'],
            'quantity' => $quantity,
            'unit_price' => number_format($unitCents / 100, 2, '.', ''),
            'subtotal' => number_format($lineCents / 100, 2, '.', '')
        ];
    }

    if ($totalCents <= 0) {
        throw new RuntimeException('The order total is invalid.');
    }

    $totalAmount = number_format($totalCents / 100, 2, '.', '');

    // Insert order
    $orderStmt = $pdo->prepare("
        INSERT INTO orders (
            customer_name,
            phone,
            address,
            total_amount,
            payment_method,
            status,
            created_at
        )
        VALUES (
            :customer_name,
            :phone,
            :address,
            :total_amount,
            :payment_method,
            'pending',
            NOW()
        )
    ");

    $orderStmt->execute([
        'customer_name' => $name,
        'phone' => $phone,
        'address' => $address,
        'total_amount' => $totalAmount,
        'payment_method' => $paymentMethod
    ]);

    $orderId = (int)$pdo->lastInsertId();

    // Insert order items
    $itemStmt = $pdo->prepare("
        INSERT INTO order_items (
            order_id,
            product_id,
            product_name,
            unit_price,
            quantity,
            subtotal
        )
        VALUES (
            :order_id,
            :product_id,
            :product_name,
            :unit_price,
            :quantity,
            :subtotal
        )
    ");

    // Reduce available stock
    $stockStmt = $pdo->prepare("
        UPDATE products
        SET stock = stock - :quantity
        WHERE id = :product_id
          AND stock >= :minimum_stock
    ");

    foreach ($orderItems as $item) {
        $itemStmt->execute([
            'order_id' => $orderId,
            'product_id' => $item['product_id'],
            'product_name' => $item['product_name'],
            'unit_price' => $item['unit_price'],
            'quantity' => $item['quantity'],
            'subtotal' => $item['subtotal']
        ]);

        $stockStmt->execute([
            'quantity' => $item['quantity'],
            'product_id' => $item['product_id'],
            'minimum_stock' => $item['quantity']
        ]);

        if ($stockStmt->rowCount() !== 1) {
            throw new RuntimeException(
                'Unable to reserve the requested stock.'
            );
        }
    }

    $pdo->commit();

    // Clear cart after successful database transaction
    unset($_SESSION['cart']);

    // Allow access to confirmation page
    $_SESSION['last_order_id'] = $orderId;

    // Rotate checkout token
    unset($_SESSION['checkout_csrf']);

    header(
        'Location: ../order-success.php?order_id=' . $orderId,
        true,
        303
    );
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Checkout error: ' . $e->getMessage());

    checkoutError(
        ['Unable to place your order. Please review your cart and try again.'],
        $name,
        $phone,
        $address,
        $paymentMethod
    );
}


// $orderId must be the ID of the order just inserted into MySQL.

// Clear the shopping cart after successful checkout.
unset($_SESSION['cart']);

// Allow this session to view the successful order.
$_SESSION['last_order_id'] = (int) $orderId;

// Redirect to the confirmation page.
header('Location: ../order-success.php?order_id=' . (int) $orderId);
exit;