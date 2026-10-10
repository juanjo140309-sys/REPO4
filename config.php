<?php
// Configuración de conexión a MySQL en AlwaysData.
// Por seguridad, evita publicar este archivo en repositorios públicos.
$host = 'mysql-juancxo.alwaysdata.net';
$db   = 'juancxo_articulosgit';
$user = 'juaxho';
$pass = 'CAMBIA_ESTA_CLAVE'; // Reemplaza por la contraseña de tu base de datos.

$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Crear la tabla automáticamente si no existe.
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS articulos (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(150) NOT NULL,
            marca VARCHAR(100) NOT NULL,
            unidades INT UNSIGNED NOT NULL DEFAULT 0,
            ubicacion VARCHAR(150) NOT NULL,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (PDOException $e) {
    error_log('Error de conexión/configuración de base de datos: ' . $e->getMessage());
    http_response_code(500);
    exit('No fue posible conectar con la base de datos. Revisa los datos de config.php y que la base de datos exista en AlwaysData.');
}
