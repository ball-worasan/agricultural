<?php

declare(strict_types=1);

// สร้างคลาส Database 
final class Database
{
  // ตัวแปรเก็บการเชื่อมต่อฐานข้อมูล
  private static ?PDO $conn = null;
  private static bool $envLoaded = false;
  private static array $env = [];
  private static ?array $config = null;

  // ประเภทฐานข้อมูลที่รองรับ
  private const SUPPORTED_DRIVERS = ['mysql', 'pgsql', 'sqlite'];

  // ฟังก์ชันโหลด env 
  private static function loadEnv(): void
  {
    if (self::$envLoaded) return;

    // ค้นหาไฟล์ .env ในโฟลเดอร์รากของโปรเจคต์
    $root = defined('APP_PATH') ? APP_PATH : dirname(__DIR__, 2);
    $path = $root . DIRECTORY_SEPARATOR . '.env';

    // ถ้าเจอไฟล์ .env ให้โหลดค่าตัวแปร
    if (is_readable($path)) {
      $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

      // ถ้า อ่านไฟล์ได้ ให้แยกวิเคราะห์แต่ละบรรทัด
      if ($lines !== false) {
        foreach ($lines as $line) {
          $line = trim($line);
          if ($line === '' || str_starts_with($line, '#')) continue;
          if (str_starts_with($line, 'export ')) $line = substr($line, 7);

          $parts = explode('=', $line, 2);
          if (count($parts) !== 2) continue;

          $key = trim($parts[0]);
          $val = trim($parts[1]);

          if (!preg_match('/^["\'].*["\']$/', $val)) {
            $val = explode(' #', $val, 2)[0];
          }
          $val = trim($val, " \t\n\r\0\x0B\"'");

          if (!array_key_exists($key, self::$env)) {
            self::$env[$key] = $val;
          }
        }
      }
    }
    self::$envLoaded = true;
  }

  // ฟังก์ชันดึงค่าตัวแปรจาก env
  public static function env(string $key, mixed $default = null): mixed
  {
    self::loadEnv();
    $val = getenv($key);
    if ($val !== false) return $val;
    return self::$env[$key] ?? $default;
  }

  // ฟังก์ชันดึงการตั้งค่าฐานข้อมูล
  private static function getConfig(): array
  {
    if (self::$config !== null) return self::$config;

    $driver = self::env('DB_CONNECTION', 'mysql');

    if (!in_array($driver, self::SUPPORTED_DRIVERS)) {
      throw new RuntimeException("Unsupported DB driver: {$driver}");
    }

    self::$config = [
      'driver'     => $driver,
      'host'       => self::env('DB_HOST', '127.0.0.1'),
      'port'       => self::env('DB_PORT', '3306'),
      'db'         => self::env('DB_DATABASE'),
      'user'       => self::env('DB_USERNAME'),
      'pass'       => self::env('DB_PASSWORD'),
      'charset'    => self::env('DB_CHARSET', 'utf8mb4'),
      'collation'  => self::env('DB_COLLATION', 'utf8mb4_unicode_ci'),
      'options'    => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
      ]
    ];

    return self::$config;
  }

  // ฟังก์ชันเชื่อมต่อฐานข้อมูล
  public static function connection(): PDO
  {
    // ถ้ามีการเชื่อมต่ออยู่แล้ว ให้คืนค่าการเชื่อมต่อนั้น
    if (self::$conn instanceof PDO) return self::$conn;

    // ดึงการตั้งค่าฐานข้อมูล
    $cfg = self::getConfig();

    // สร้าง Data Source Name (DSN) ตามประเภทฐานข้อมูล
    $dsn = match ($cfg['driver']) {
      'sqlite' => "sqlite:" . $cfg['db'],
      'pgsql'  => "pgsql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['db']}",
      default  => "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['db']};charset={$cfg['charset']}",
    };

    // เพิ่มคำสั่งตั้งค่าเริ่มต้นสำหรับ MySQL
    if ($cfg['driver'] === 'mysql' && defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
      $cfg['options'][PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES {$cfg['charset']} COLLATE {$cfg['collation']}";
    }

    try {
      // สร้างการเชื่อมต่อฐานข้อมูล
      self::$conn = new PDO($dsn, $cfg['user'], $cfg['pass'], $cfg['options']);
    } catch (PDOException $e) {
      $debug = true;

      $msg = $e->getMessage();
      if (strpos($msg, 'could not find driver') !== false) {
        $msg = "PDO Driver not found. Please install php-mysql extension.";
      }
      throw new RuntimeException(
        $debug ? "Connection failed: " . $msg : "Database connection error."
      );
    }

    return self::$conn;
  }

  // ฟังก์ชันปิดการเชื่อมต่อฐานข้อมูล
  public static function close(): void
  {
    self::$conn = null;
  }

  // ฟังก์ชันรันคำสั่ง SQL
  public static function query(string $sql, array $params = []): PDOStatement
  {
    $stmt = self::connection()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
  }

  // ฟังก์ชันช่วยดึงข้อมูลแบบทั้งหมด
  public static function fetchAll(string $sql, array $params = []): array
  {
    return self::query($sql, $params)->fetchAll();
  }

  // ฟังก์ชันดึงข้อมูลแถวเดียว
  public static function fetchOne(string $sql, array $params = []): ?array
  {
    $result = self::query($sql, $params)->fetch();
    return $result === false ? null : $result;
  }

  // ฟังก์ชันรันคำสั่ง SQL ที่ไม่คืนค่า
  public static function execute(string $sql, array $params = []): int
  {
    $stmt = self::query($sql, $params);
    return $stmt->rowCount();
  }

  // ฟังก์ชันดึง ID ล่าสุดที่เพิ่มเข้าไป
  public static function lastInsertId(): string|false
  {
    return self::connection()->lastInsertId();
  }

  // ฟังก์ชันจัดการธุรกรรม (transaction)
  public static function transaction(callable $callback): mixed
  {
    $pdo = self::connection();
    $inTransaction = $pdo->inTransaction();
    $savepoint = $inTransaction ? 'SP_' . bin2hex(random_bytes(4)) : null;

    if ($inTransaction) {
      $pdo->exec("SAVEPOINT {$savepoint}");
    } else {
      $pdo->beginTransaction();
    }

    try {
      $result = $callback($pdo);
      if ($inTransaction) {
        $pdo->exec("RELEASE SAVEPOINT {$savepoint}");
      } else {
        $pdo->commit();
      }
      return $result;
    } catch (Throwable $e) {
      if ($inTransaction) {
        $pdo->exec("ROLLBACK TO SAVEPOINT {$savepoint}");
      } else {
        $pdo->rollBack();
      }
      throw $e;
    }
  }

  // ฟังก์ชันตรวจสอบสถานะการเชื่อมต่อฐานข้อมูล
  public static function health(): array
  {
    $start = microtime(true);
    try {
      $pdo = self::connection();
      $pdo->query('SELECT 1');
      return [
        'status'   => true,
        'latency'  => round((microtime(true) - $start) * 1000, 2) . 'ms',
        'driver'   => $pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
        'version'  => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
      ];
    } catch (Throwable $e) {
      return [
        'status'  => false,
        'error'   => $e->getMessage()
      ];
    }
  }
}
