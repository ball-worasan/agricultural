<?php

declare(strict_types=1);

// เริ่มต้น Session (Login/Logout)
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}



// ------------------------------------------------
// ฟังก์ชันและค่าคงที่พื้นฐาน
// ------------------------------------------------
// กำหนด path
if (!defined('APP_PATH')) {
  define('APP_PATH', __DIR__);
}

// ดึงชื่อเว็บจาก ENV หรือกำหนดค่าเริ่มต้น
if (!defined('APP_NAME')) {
  define('APP_NAME', 'สิริณัฐ');
}

// สร้างฟังก์ชัน e() เพื่อความปลอดภัย
if (!function_exists('e')) {
  function e(string $value): string
  {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
  }
}

// สร้างฟังก์ชันบันทึก log
if (!function_exists('app_log')) {
  function app_log(string $name, array $context = []): void
  {
    error_log($name . ': ' . json_encode($context));
  }
}



// ------------------------------------------------
// ระบบตรวจสอบผู้ใช้ (USER SESSION)
// ------------------------------------------------
// ตรวจสอบว่าใน Session มี user_id หรือไม่
if (isset($_SESSION['user_id'])) {
  // กรณี: เข้าสู่ระบบแล้ว (ดึงค่าจาก Session)
  $user = [
    'is_logged_in' => true,
    'id'           => $_SESSION['user_id'],
    'username'     => $_SESSION['username'] ?? '',
    'name'         => $_SESSION['user_name'] ?? 'Unknown',
    'role'         => $_SESSION['user_role'] ?? 'member', // member, admin
  ];
} else {
  // กรณี: ยังไม่เข้าสู่ระบบ (Guest)
  $user = [
    'is_logged_in' => false,
    'id'           => 0,
    'username'     => '',
    'name'         => 'ผู้เยี่ยมชม',
    'role'         => 'guest',
  ];
}



// ------------------------------------------------
// กำหนดการตั้งค่าหน้าเว็บ
// ------------------------------------------------
// รับหน้าเว็บ ?page=home จาก URL
$page = $_GET['page'] ?? 'home';

// ตรวจสอบ Action Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
  // ล้าง Session ทั้งหมด
  session_destroy();
  // ส่งกลับไปหน้า Login หรือ Home
  header('Location: ?page=login');
  exit;
}

// กำหนดหน้าที่อนุญาต
$allowed_pages = ['home', 'detail', 'profile', 'admin_dashboard', 'login', 'register', '404'];

// ถ้าหน้าไม่อยู่ในรายการที่อนุญาต ให้ตั้งค่าเป็นหน้า 404
if (!in_array($page, $allowed_pages)) {
  $page = '404';
}

// ตั้งค่าข้อมูลแต่ละหน้า
$page_config = [
  'home' => [
    'title' => 'หน้าหลัก - บริการเช่าที่นา',
    'view'  => '/views/home.php',
    'css'   => ['/styles/css/pages/home.css'],
    'js'    => ['/styles/js/pages/home.js'],
    'nav'   => false,
    'auth'  => false,
    'roles' => ['guest', 'member', 'admin'],
  ],
  'detail' => [
    'title' => 'รายละเอียดพื้นที่',
    'view'  => '/views/detail.php',
    'css'   => ['/styles/css/pages/detail.css'],
    'js'    => ['/styles/js/pages/detail.js'],
    'nav'   => true,
    'auth'  => false,
    'roles' => ['guest', 'member', 'admin'],
  ],
  'profile' => [
    'title' => 'ข้อมูลส่วนตัว',
    'view'  => '/views/profile.php',
    'css'   => ['/styles/css/pages/profile.css'],
    'js'    => ['/styles/js/pages/profile.js'],
    'nav'   => true,
    'auth'  => true,
    'roles' => ['member', 'admin'],
  ],
  'admin_dashboard' => [
    'title' => 'จัดการระบบ',
    'view'  => '/views/admin/dashboard.php',
    'css'   => ['/styles/css/pages/admin.css'],
    'js'    => ['/styles/js/pages/admin.js'],
    'nav'   => true,
    'auth'  => true,
    'roles' => ['admin'],
  ],
  'login' => [
    'title' => 'เข้าสู่ระบบ',
    'view'  => '/views/login.php',
    'css'   => ['/styles/css/pages/auth.css'],
    'js'    => ['/styles/js/pages/login.js'],
    'nav'   => false,
    'auth'  => false,
    'roles' => ['guest']
  ],
  'register' => [
    'title' => 'สมัครสมาชิก',
    'view'  => '/views/register.php',
    'css'   => ['/styles/css/pages/auth.css'],
    'js'    => ['/styles/js/pages/register.js'],
    'nav'   => false,
    'auth'  => false,
    'roles' => ['guest']
  ],
  '404' => [
    'title' => 'ไม่พบหน้านี้',
    'view'  => '/views/404.php',
    'css'   => ['/styles/css/pages/404.css'],
    'js'    => ['/styles/js/pages/404.js'],
    'nav'   => false,
    'auth'  => false,
    'roles' => [],
  ],
];

// กำหนดค่าปัจจุบันตามหน้าที่เรียก
$current_config = $page_config[$page] ?? $page_config['404'];
$user_role = $user['is_logged_in'] ? $user['role'] : 'guest';
$can_access = in_array($user_role, $current_config['roles'] ?? []);

// ตรวจสอบสิทธิ์การเข้าถึง (Authentication)
if ($current_config['auth'] && !$user['is_logged_in']) {
  // ถ้าหน้าไหนต้อง auth=true แต่ไม่ได้ login -> ไล่ไปหน้า login
  header('Location: ?page=login');
  exit;
}

// ตรวจสอบ Role (Authorization)
if (!$can_access && $page !== '404') {
  // ถ้า login แล้วแต่ role ไม่มีสิทธิ์เข้าถึง -> ส่งไปหน้า 404
  $page = '404';
  $current_config = $page_config['404'];
}

// ประกาศตัวแปรเปล่าๆ
$title   = $current_config['title'];
$pageCss = [];
$pageJs  = [];

// เพิ่มไฟล์ CSS
if (!empty($current_config['css']) && is_array($current_config['css'])) {
  foreach ($current_config['css'] as $cssFile) {
    $pageCss[] = $cssFile;
  }
}

// เพิ่มไฟล์ JS
if (!empty($current_config['js']) && is_array($current_config['js'])) {
  foreach ($current_config['js'] as $jsFile) {
    $pageJs[] = $jsFile;
  }
}



// ------------------------------------------------
// กำหนดตัวแปรพื้นฐานสำหรับ Navbar
// ------------------------------------------------
$onHome = ($page === 'home');




// ------------------------------------------------
// เชื่อมต่อฐานข้อมูล
// ------------------------------------------------
$databaseFile = APP_PATH . '/core/database.php';
if (file_exists($databaseFile)) {
  require_once $databaseFile;
} else {
  // กรณีไม่มีไฟล์ Database ให้ Log error แต่ไม่ให้เว็บพังทันที 
  app_log("Database file missing: $databaseFile");
}
