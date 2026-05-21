<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Product.php';

class HomeController extends Controller
{
    public function index(): void
    {
        $categoryModel = new Category();
        $productModel = new Product();

        $categories = $categoryModel->all();
        $featured = $productModel->featured();

        // Featured restaurants
        $featuredRestaurants = db_fetch_all(
            "SELECT r.id, r.name, r.logo_path AS profile_picture FROM featured_restaurants fr JOIN restaurants r ON r.id = fr.restaurant_id WHERE r.is_approved = 1 ORDER BY fr.priority LIMIT 6"
        );

        $this->render('home/index.php', [
            'categories' => $categories,
            'featured' => $featured,
            'featuredRestaurants' => $featuredRestaurants,
            'currentUser' => getCurrentUser(),
        ]);
    }
}
