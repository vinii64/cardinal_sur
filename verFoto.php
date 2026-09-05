<?php
include('conexion.php');

$id = (int)($_GET['id'] ?? 0);

$stmt = $conexion->prepare("SELECT imagen FROM galeria WHERE id_foto = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$fila = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$fila) {
    http_response_code(404);
    exit();
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$tipo = $finfo->buffer($fila['imagen']);

header('Content-Type: ' . $tipo);
header('Content-Length: ' . strlen($fila['imagen']));
echo $fila['imagen'];
