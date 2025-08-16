<?php
/**
 * Classe de gestion de la base de données
 * Utilise PDO pour une connexion sécurisée et des requêtes préparées
 */

require_once 'config.php';

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Erreur de connexion à la base de données: " . $e->getMessage());
            die("Erreur de connexion à la base de données. Veuillez vérifier la configuration.");
        }
    }
    
    // Pattern Singleton pour éviter les connexions multiples
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Obtenir la connexion PDO
    public function getConnection() {
        return $this->connection;
    }
    
    // Exécuter une requête SELECT
    public function select($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur SELECT: " . $e->getMessage());
            return false;
        }
    }
    
    // Exécuter une requête SELECT et retourner une seule ligne
    public function selectOne($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Erreur SELECT ONE: " . $e->getMessage());
            return false;
        }
    }
    
    // Exécuter une requête INSERT
    public function insert($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erreur INSERT: " . $e->getMessage());
            return false;
        }
    }
    
    // Exécuter une requête UPDATE
    public function update($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erreur UPDATE: " . $e->getMessage());
            return false;
        }
    }
    
    // Exécuter une requête DELETE
    public function delete($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erreur DELETE: " . $e->getMessage());
            return false;
        }
    }
    
    // Compter le nombre de lignes
    public function count($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erreur COUNT: " . $e->getMessage());
            return false;
        }
    }
    
    // Vérifier si une table existe
    public function tableExists($tableName) {
        try {
            $query = "SHOW TABLES LIKE :table";
            $stmt = $this->connection->prepare($query);
            $stmt->execute(['table' => $tableName]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erreur tableExists: " . $e->getMessage());
            return false;
        }
    }
    
    // Obtenir la structure d'une table
    public function getTableStructure($tableName) {
        try {
            $query = "DESCRIBE " . $tableName;
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getTableStructure: " . $e->getMessage());
            return false;
        }
    }
    
    // Démarrer une transaction
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    // Valider une transaction
    public function commit() {
        return $this->connection->commit();
    }
    
    // Annuler une transaction
    public function rollback() {
        return $this->connection->rollback();
    }
    
    // Vérifier si une transaction est en cours
    public function inTransaction() {
        return $this->connection->inTransaction();
    }
    
    // Échapper une chaîne pour éviter l'injection SQL
    public function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    // Nettoyer une chaîne d'entrée
    public function sanitize($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitize'], $input);
        }
        return trim(strip_tags($input));
    }
    
    // Vérifier la connexion
    public function isConnected() {
        try {
            $this->connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // Fermer la connexion
    public function close() {
        $this->connection = null;
        self::$instance = null;
    }
    
    // Destructeur
    public function __destruct() {
        $this->close();
    }
}

// Fonction utilitaire pour obtenir l'instance de la base de données
function db() {
    return Database::getInstance();
}

// Fonction utilitaire pour exécuter une requête rapide
function db_query($query, $params = []) {
    return db()->select($query, $params);
}

// Fonction utilitaire pour exécuter une requête d'insertion
function db_insert($query, $params = []) {
    return db()->insert($query, $params);
}

// Fonction utilitaire pour exécuter une requête de mise à jour
function db_update($query, $params = []) {
    return db()->update($query, $params);
}

// Fonction utilitaire pour exécuter une requête de suppression
function db_delete($query, $params = []) {
    return db()->delete($query, $params);
}
?>