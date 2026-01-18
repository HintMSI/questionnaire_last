<?php
class Database {
    // В Docker хост = имя сервиса из docker-compose.yml
    private $host = 'postgres';
    private $port = '5432';
    private $db_name = 'questionnaire_db';
    private $username = 'devops_user';
    private $password = '${DB_PASSWORD}';  
    private $conn;
    
    public function getConnection() {
        $this->conn = null;
        
        try {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name}";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("SET NAMES 'UTF8'");
            return $this->conn;
        } catch(PDOException $exception) {
            error_log("[DB Error] " . $exception->getMessage());
            return null;
        }
    }
}
?>