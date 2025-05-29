<?php
require_once 'bootstrap.php';

try {
    $categoryModel = new Category();
    $categories = $categoryModel->getAllCategories();
    
    echo "Categories found in database:\n";
    if (!empty($categories)) {
        foreach ($categories as $category) {
            echo "ID: " . $category['id'] . " - Name: " . $category['name'] . " - Description: " . $category['description'] . "\n";
        }
    } else {
        echo "No categories found in database.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
