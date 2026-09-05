<?php
session_start();
include('conexion.php');

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

$id = (int)$_SESSION['id'];

/* Chequeo de admin con prepared statement */
$stmtAdmin = $conexion->prepare("SELECT admin FROM usuarios WHERE id = ?");
$stmtAdmin->bind_param('i', $id);
$stmtAdmin->execute();
$admin = $stmtAdmin->get_result()->fetch_assoc();
$stmtAdmin->close();

if (!$admin || $admin['admin'] != 1) {
    header('Location: index.php');
    exit();
}

$mensaje = '';

/* Tipos de imagen permitidos y tamaño máximo (5MB) */
$tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
$tamanoMaximo = 5 * 1024 * 1024;


/* AGREGAR FOTO */
if (isset($_POST['accion']) && $_POST['accion'] == 'agregar') {

    if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] != UPLOAD_ERR_OK) {
        $mensaje = 'Hubo un error al subir el archivo.';

    } elseif ($_FILES['archivo']['size'] > $tamanoMaximo) {
        $mensaje = 'La imagen no puede pesar más de 5MB.';

    } else {

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $tipoReal = $finfo->file($_FILES['archivo']['tmp_name']);

        if (!in_array($tipoReal, $tiposPermitidos)) {
            $mensaje = 'Formato no permitido. Solo JPG, PNG o WEBP.';

        } else {

            $imagen = file_get_contents($_FILES['archivo']['tmp_name']);
            $titulo = trim($_POST['titulo'] ?? '');

            $stmt = $conexion->prepare("INSERT INTO galeria (imagen, titulo) VALUES (?, ?)");
            $null = null;
            $stmt->bind_param('bs', $null, $titulo);
            $stmt->send_long_data(0, $imagen);

            if ($stmt->execute()) {
                $mensaje = 'Foto agregada correctamente.';
            } else {
                $mensaje = 'Error al guardar en la base de datos: ' . $conexion->error;
            }

            $stmt->close();
        }
    }
}

/* ELIMINAR FOTO */
if (isset($_POST['accion']) && $_POST['accion'] == 'eliminar') {

    $id_foto = (int)$_POST['id_foto'];

    $stmt = $conexion->prepare("DELETE FROM galeria WHERE id_foto = ?");
    $stmt->bind_param('i', $id_foto);

    $mensaje = $stmt->execute() ? 'Foto eliminada correctamente.' : 'Error al eliminar la foto.';
    $stmt->close();
}

/* ACTUALIZAR TITULO */
if (isset($_POST['accion']) && $_POST['accion'] == 'actualizar') {

    $id_foto = (int)$_POST['id_foto'];
    $titulo = trim($_POST['titulo'] ?? '');

    $stmt = $conexion->prepare("UPDATE galeria SET titulo = ? WHERE id_foto = ?");
    $stmt->bind_param('si', $titulo, $id_foto);

    $mensaje = $stmt->execute() ? 'Foto actualizada correctamente.' : 'Error al actualizar la foto.';
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de admin</title>
    <link rel="stylesheet" href="styles/adminGaleria.css">
</head>
<body>

<div class="navbar"> 


  <a class="menu" href="index.php">
    <img src="assets/logoLetras.png" class="iconoCardinal">
  </a>

  <a class="menu" href="gallery.php">
    <img src="assets/image.svg" class="icono"> <p>GALERÍA</p>
  </a>

  <a class="menu" href="store.php">
  <img src="assets/store.svg" class="icono"> <p>TIENDA</p>
  </a>

  <a class="menu" href="help.php">
    <img src="assets/help.svg" class="icono"> <p>ACERCA</p>
  </a>

  <?php
  /* muestra el panel de admin solo si el usuario logeado tiene admin = 1 */
  if (isset($_SESSION['usuario'])) {

      $idNav = (int)$_SESSION['id'];

      $stmtNav = $conexion->prepare("SELECT admin FROM usuarios WHERE id = ?");
      $stmtNav->bind_param('i', $idNav);
      $stmtNav->execute();
      $adminNav = $stmtNav->get_result()->fetch_assoc();
      $stmtNav->close();

      if ($adminNav && $adminNav['admin'] == 1) {
  ?>
      <a class="menu" href="adminGaleria.php">
        <img src="assets/admin.svg" class="icono"> <p>ADMIN</p>
      </a>
  <?php
      }
  }
  ?>

  <!-- esto hace q cuando no estas logeado muestre el boton de login pero si lo estas muestre el boton de logout -->
  <?php if(isset($_SESSION['usuario'])): ?>
    <a class="menu" href="logout.php">
      <img src="assets/logout.svg" class="icono"> <p>CERRAR SESIÓN</p>
    </a>
  <?php else: ?>
    <a class="menu" href="login.php">
      <img src="assets/login.svg" class="icono"> <p>INGRESA</p>
    </a>
  <?php endif; ?>
<!-- esto hace q cuando no estas logeado muestre el boton de login pero si lo estas muestre el boton de logout -->
</div>

<div class="cartel"><h1>Panel de administración</h1></div>

<?php if ($mensaje): ?>
    <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<div class="agregar">

    <form method="post" enctype="multipart/form-data">

        <input type="hidden" name="accion" value="agregar">

        <div class="contenedor">
            <h1>Agregar foto</h1>
            <input type="file" name="archivo" accept="image/jpeg,image/png,image/webp" required>

            <br>

            <label>Título:</label>
            <input type="text" name="titulo" maxlength="100">

            <br>

            <button type="submit">Agregar</button>
        </div>

    </form>
</div>

<div class="inventario">

<?php
$resultado = $conexion->query("SELECT id_foto, titulo FROM galeria ORDER BY id_foto DESC");

while ($fila = $resultado->fetch_assoc()) {
?>

    <div class="carta">

        <img src="verFoto.php?id=<?php echo $fila['id_foto']; ?>" alt="<?php echo htmlspecialchars($fila['titulo']); ?>">

        <!-- ELIMINAr -->
        <form method="post">
            <input type="hidden" name="accion" value="eliminar">
            <input type="hidden" name="id_foto" value="<?php echo $fila['id_foto']; ?>">
            <button type="submit">Eliminar foto</button>
        </form>


    </div>

<?php
}
?>

</div>

</body>
</html>
