<?php
// Language translations for ClassKart

$translations = [
    'en' => [
        'home' => 'Home',
        'shop' => 'Shop',
        'about' => 'About',
        'contact' => 'Contact',
        'login' => 'Login',
        'logout' => 'Logout',
        'cart' => 'Cart',
        'hero_title' => 'Empowering Learning Through Access',
        'hero_subtitle' => 'Quality educational materials for students, teachers, and parents. Browse our collection of books, stationery, and digital resources.',
        'shop_now' => 'Shop Now',
        'shop_by_category' => 'Shop by Category',
        'books' => 'Books',
        'books_desc' => 'Textbooks, workbooks, and reference materials for all grades',
        'stationery' => 'Stationery',
        'stationery_desc' => 'Quality pens, pencils, notebooks, and office supplies',
        'courses' => 'Courses',
        'courses_desc' => 'Digital learning resources and online courses',
        'featured_products' => 'Featured Products',
        'add_to_cart' => 'Add to Cart',
        'quick_links' => 'Quick Links',
        'connect_with_us' => 'Connect With Us',
        'footer_text' => 'Empowering Learning Through Access. Quality educational materials for students, teachers, and parents.',
        'all_rights_reserved' => 'All rights reserved',
    ],
    'fr' => [
        'home' => 'Accueil',
        'shop' => 'Boutique',
        'about' => 'À propos',
        'contact' => 'Contact',
        'login' => 'Connexion',
        'logout' => 'Déconnexion',
        'cart' => 'Panier',
        'hero_title' => 'Autonomiser l\'apprentissage par l\'accès',
        'hero_subtitle' => 'Matériel pédagogique de qualité pour les étudiants, les enseignants et les parents. Parcourez notre collection de livres, de papeterie et de ressources numériques.',
        'shop_now' => 'Acheter maintenant',
        'shop_by_category' => 'Acheter par catégorie',
        'books' => 'Livres',
        'books_desc' => 'Manuels scolaires, cahiers d\'exercices et matériel de référence pour tous les niveaux',
        'stationery' => 'Papeterie',
        'stationery_desc' => 'Stylos, crayons, cahiers et fournitures de bureau de qualité',
        'courses' => 'Cours',
        'courses_desc' => 'Ressources d\'apprentissage numérique et cours en ligne',
        'featured_products' => 'Produits en vedette',
        'add_to_cart' => 'Ajouter au panier',
        'quick_links' => 'Liens rapides',
        'connect_with_us' => 'Connectez-vous avec nous',
        'footer_text' => 'Autonomiser l\'apprentissage par l\'accès. Matériel pédagogique de qualité pour les étudiants, les enseignants et les parents.',
        'all_rights_reserved' => 'Tous droits réservés',
    ],
    'rw' => [
        'home' => 'Ahabanza',
        'shop' => 'Gura',
        'about' => 'Abo turi',
        'contact' => 'Twandikire',
        'login' => 'Injira',
        'logout' => 'Sohoka',
        'cart' => 'Agasanduku',
        'hero_title' => 'Gutera inkunga kwiga binyuze mu kubona',
        'hero_subtitle' => 'Ibikoresho by\'uburezi by\'ireme ku banyeshuri, abarimu, n\'ababyeyi. Reba ikusanyirizo ry\'ibitabo, ibikoresho byo kwandika, n\'ibikoresho bya digitale.',
        'shop_now' => 'Gura Ubu',
        'shop_by_category' => 'Gura ukurikije icyiciro',
        'books' => 'Ibitabo',
        'books_desc' => 'Ibitabo by\'ishuri, ibitabo by\'imyitozo n\'ibikoresho byo kureba ku byiciro byose',
        'stationery' => 'Ibikoresho byo kwandika',
        'stationery_desc' => 'Ikaramu, ikaramu, ibitabo n\'ibikoresho bya biro by\'ireme',
        'courses' => 'Amasomo',
        'courses_desc' => 'Ibikoresho byo kwiga bya digitale n\'amasomo kuri interineti',
        'featured_products' => 'Ibicuruzwa byiza',
        'add_to_cart' => 'Shyira muri Agasanduku',
        'quick_links' => 'Ihuza ryihuse',
        'connect_with_us' => 'Duhuze',
        'footer_text' => 'Gutera inkunga kwiga binyuze mu kubona. Ibikoresho by\'uburezi by\'ireme ku banyeshuri, abarimu, n\'ababyeyi.',
        'all_rights_reserved' => 'Uburenganzira bwose burahagaritswe',
    ]
];

// Get current language from session or default to English
function get_language() {
    if (!isset($_SESSION['language'])) {
        $_SESSION['language'] = $_GET['lang'] ?? 'en';
    }
    return $_SESSION['language'];
}

// Get translation for a key
function translate($key) {
    global $translations;
    $lang = get_language();
    
    if (isset($translations[$lang][$key])) {
        return $translations[$lang][$key];
    }
    
    // Fallback to English if translation not found
    return $translations['en'][$key] ?? $key;
}

// Alias for translate function
function t($key) {
    return translate($key);
}

// Change language
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'fr', 'rw'])) {
    $_SESSION['language'] = $_GET['lang'];
}
?>