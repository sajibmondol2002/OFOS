<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Product.php';

class MenuController extends Controller
{
    public function index(): void
    {
        $categoryId = isset($_GET['category']) ? (int) $_GET['category'] : 0;
        $categoryModel = new Category();
        $productModel = new Product();

        $categories = $categoryModel->all();
        $products = [];

        if ($categoryId > 0) {
            $products = $productModel->all($categoryId);
        } else {
            $products = $productModel->all();
        }

        $this->render('menu/index.php', [
            'categories' => $categories,
            'products' => $products,
            'currentUser' => getCurrentUser(),
            'categoryId' => $categoryId,
        ]);
    }
}