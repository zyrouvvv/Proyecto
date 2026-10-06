<?php
session_start();
require '../includes/conexion.php';

header('Content-Type: application/json');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query)) {
    echo json_encode(['success' => true, 'resultados' => []]);
    exit;
}

try {
    // Busca coincidencias en el título o contenido de las publicaciones
    $sql = "SELECT p.id, p.titulo, p.contenido, p.fecha, u.nombre AS autor 
            FROM publicaciones p 
            JOIN usuarios u ON p.usuario_id = u.id 
            WHERE p.titulo LIKE :query OR p.contenido LIKE :query 
            ORDER BY p.fecha DESC 
            LIMIT 10";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['query' => "%{$query}%"]);
    $publicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatear los resultados para la respuesta JSON
    $resultados = array_map(function ($post) {
        return [
            'id' => $post['id'],
            'titulo' => $post['titulo'],
            'resumen' => mb_substr($post['contenido'], 0, 100) . '...',
            'autor' => $post['nombre'] ?? $post['autor'],
            'fecha' => date('d/m/Y', strtotime($post['fecha']))
        ];
    }, $publicaciones);

    echo json_encode(['success' => true, 'resultados' => $resultados]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error al realizar la búsqueda']);
}
?>
