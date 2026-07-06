<?php
// config.php
// Reads DB connection details from environment variables.
// DB_HOST, APP_NAME, ENVIRONMENT come from the ConfigMap.
// DB_USER, DB_PASSWORD, DB_NAME come from the Secret.

$db_host     = getenv('DB_HOST') ?: 'mysql-service';
$db_user     = getenv('DB_USER') ?: 'appuser';
$db_password = getenv('DB_PASSWORD') ?: '';
$db_name     = getenv('DB_NAME') ?: 'studentdb';

$app_name    = getenv('APP_NAME') ?: 'Student Portal';
$environment = getenv('ENVIRONMENT') ?: 'development';

function getDbConnection() {
    global $db_host, $db_user, $db_password, $db_name;

    $conn = new mysqli($db_host, $db_user, $db_password, $db_name);

    if ($conn->connect_error) {
        die('Database connection failed: ' . $conn->connect_error);
    }

    return $conn;
}

session_start();
