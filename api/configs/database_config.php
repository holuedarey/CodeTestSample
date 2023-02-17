<?php

namespace api\configs;

include_once(__DIR__ . '/../helpers/Logger.php');


use helpers\Logger;
use mysqli;

class DatabaseConfig
{
    private $servername;
    private $username;
    private $password;
    private $dbname;
    private $conn;

    public function __construct()
    {
        $this->servername = "127.0.0.1";
        $this->username = "root";
        $this->password = "";
        $this->dbname = "movie_collection";
    }

    public function connect()
    {
        // Create connection
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname, 3306);

        // Check connection
        if ($this->conn->connect_errno) {
            echo $this->conn->connect_errno;
            Logger::log($this->conn->connect_error);

            die("Connection failed: " . $this->conn->connect_error);
        }

        // Create database
        $sql = "CREATE DATABASE IF NOT EXISTS $this->dbname";
        if ($this->conn->query($sql) === TRUE) {
            Logger::log("Database created successfully");
            error_log("Database created successfully");
        } else {
            Logger::log($this->conn->error);
            error_log("Error creating database: " . $this->conn->error);
        }

        // Close connection
        $this->conn->close();

        // Create table products
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname, 3306);

        $sql = "CREATE TABLE IF NOT EXISTS movies (
                    id VARCHAR(36) PRIMARY KEY,
                    title VARCHAR(255) NOT NULL,
                    runtime VARCHAR(255) NOT NULL,
                    release_date DATETIME NOT NULL,
                    created_at DATETIME NOT NULL
                );";

        if ($this->conn->query($sql) === TRUE) {
            error_log("Table products created successfully");
        } else {
            Logger::log("Error creating table: " . $this->conn->error);
            error_log("Error creating table: " . $this->conn->error);
        }

        // Create table comments
        $sql = "CREATE TABLE IF NOT EXISTS actors (
            id VARCHAR(36) PRIMARY KEY,
            movie_id VARCHAR(36) NOT NULL,
            actor_name VARCHAR(200) NOT NULL,
            date_of_birth DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE
        )";

        if ($this->conn->query($sql) === TRUE) {
            error_log("Table comments created successfully");
        } else {
            error_log("Error creating table: " . $this->conn->error);
        }

        return $this->conn;
    }

    public function close()
    {
        $this->conn->close();
    }
}