<?php
namespace Moohamad\Core;
use PDO;
use PDOStatement;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}

    private static function getInstance(): ?PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = "pgsql:host=localhost;dbname=gestion_note_poo";
                self::$instance = new PDO($dsn, "postgres", "postgres", [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
                ]);
            } catch (PDOException $e) {
                error_log("Connexion PostgreSQL échouée : " . $e->getMessage());
                return null;
            }
        }
        return self::$instance;
    }

    public static function query(string $sql, bool $single = true): mixed
    {
        $query = self::getInstance()->query($sql);
        return $single ? $query->fetch(PDO::FETCH_OBJ) : $query->fetchAll(PDO::FETCH_OBJ);
    }

    private static function prepare(string $sql, array $datas = []): PDOStatement
    {
        $prepare = self::getInstance()->prepare($sql);
        $prepare->execute($datas);
        return $prepare;
    }

    public static function executeQuery(string $sql, array $datas = [], bool $single = true): mixed
    {
        $statement = self::prepare($sql, $datas);
        return $single ? $statement->fetch(PDO::FETCH_OBJ) : $statement->fetchAll(PDO::FETCH_OBJ);
    }

    public static function executeUpdate(string $sql, array $datas = []): int|string
    {
        $statement = self::prepare($sql, $datas);
        return (str_starts_with(strtoupper(trim($sql)), 'INSERT')) ? self::getInstance()->lastInsertId() : $statement->rowCount();
    }

    public static function getAllData(string $tableName): array
    {
        $sql = "SELECT * FROM $tableName";
        return self::query($sql, false);
    }
}
