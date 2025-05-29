<?php
require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/json');

$cart = new Cart();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $productId = (int)($_POST['product_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 1);
            
            if ($productId > 0 && $quantity > 0) {
                $cart->addItem($productId, $quantity);
                echo json_encode(['success' => true, 'message' => 'Product added to cart']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
            }
            break;
            
        case 'remove':
            $productId = (int)($_POST['product_id'] ?? 0);
            
            if ($productId > 0) {
                $cart->removeItem($productId);
                echo json_encode(['success' => true, 'message' => 'Product removed from cart']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid product']);
            }
            break;
            
        case 'update':
            $productId = (int)($_POST['product_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 0);
            
            if ($productId > 0) {
                $cart->updateQuantity($productId, $quantity);
                echo json_encode(['success' => true, 'message' => 'Cart updated']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid product']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'count':
            echo json_encode(['count' => $cart->getItemCount()]);
            break;
            
        case 'total':
            echo json_encode(['total' => $cart->getTotal()]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
