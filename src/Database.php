<?php

namespace App;

use PDO;
use PDOException;

class Database {
    private static ?Database $instance = null;
    private ?PDO $conn = null;

    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // In produzione, registrare l'errore senza mostrare i dettagli sensibili
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        static $migrated = false;
        if (!$migrated && $this->conn !== null) {
            $migrated = true;
            try {
                $stmt = $this->conn->query("SHOW COLUMNS FROM registrations LIKE 'offer_expires_at'");
                if (!$stmt->fetch()) {
                    $this->conn->exec("ALTER TABLE registrations ADD COLUMN offer_expires_at DATETIME DEFAULT NULL");
                }
            } catch (\Exception $e) {
                // Silently ignore migration issues in case table doesn't exist yet
            }
        }
        return $this->conn;
    }

    // Previene la clonazione dell'oggetto
    private function __clone() {}

    // Previene la deserializzazione
    public function __wakeup() {
        throw new \Exception("Non è possibile deserializzare una classe Singleton.");
    }
}
