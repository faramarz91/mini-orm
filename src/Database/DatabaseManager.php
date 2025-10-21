<?php
namespace MiniORM\Database;

use PDO;
use MiniORM\Exceptions\ORMException;

class DatabaseManager {
    protected static array $connections = [];
    protected static ?ConnectionInterface $default = null;

    /**
     * Add/register a connection config and create a Connection instance immediately.
     * $config = ['driver'=>'sqlite'|'mysql', 'database'=>..., 'host'=>..., 'username'=>..., 'password'=>...]
     */
    public static function addConnection(string $name, array $config): void {
        $driver = $config['driver'] ?? 'sqlite';

        if ($driver === 'sqlite') {
            $dsn = isset($config['database']) && $config['database'] !== ':memory:'
                ? "sqlite:{$config['database']}"
                : "sqlite::memory:";
            $pdo = new PDO($dsn);
        } elseif ($driver === 'mysql') {
            $host = $config['host'] ?? 'localhost';
            $db = $config['database'] ?? '';
            $user = $config['username'] ?? '';
            $pass = $config['password'] ?? '';
            $charset = $config['charset'] ?? 'utf8mb4';
            $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } else {
            throw new ORMException("Unsupported driver: {$driver}");
        }

        // common attributes
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $conn = new Connection($pdo);
        self::$connections[$name] = $conn;
        if (self::$default === null) {
            self::$default = $conn;
        }
    }

    public static function connection(string $name = null): ConnectionInterface {
        if ($name === null) {
            if (!self::$default) {
                throw new ORMException("No default connection configured");
            }
            return self::$default;
        }
        if (!isset(self::$connections[$name])) {
            throw new ORMException("Connection '{$name}' not found");
        }
        return self::$connections[$name];
    }

    public static function setDefault(string $name): void {
        if (!isset(self::$connections[$name])) {
            throw new ORMException("Connection '{$name}' not found");
        }
        self::$default = self::$connections[$name];
    }
}
