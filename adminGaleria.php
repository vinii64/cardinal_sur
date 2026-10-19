<?php
session_start();
include('conexion.php');

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

$id = (int)$_SESSION['id'];


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


$tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
$tamanoMaximo = 5 * 1024 * 1024;


/* AGREgAR FOTO */
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

/* GUARDAR DATOS DE PAGO (alias, titular y WhatsApp que ven los socios en "Mi cuenta") */
if (isset($_POST['accion']) && $_POST['accion'] == 'datos_pago') {

    $alias    = mb_substr(trim($_POST['alias'] ?? ''), 0, 100);
    $titular  = mb_substr(trim($_POST['titular'] ?? ''), 0, 100);
    $whatsapp = mb_substr(trim($_POST['whatsapp'] ?? ''), 0, 30);

    // Cuota mensual: acepta "5000", "5000,50" y también "5.000" (punto como separador de miles)
    $cuota = trim($_POST['cuota'] ?? '');
    if (preg_match('/^\d{1,3}(\.\d{3})+(,\d{1,2})?$/', $cuota)) {
        $cuota = str_replace('.', '', $cuota);
    }
    $cuota = str_replace(',', '.', $cuota);

    if ($alias === '' || $titular === '') {
        $mensaje = 'El alias y el titular no pueden estar vacíos.';
    } elseif (!is_numeric($cuota) || (float)$cuota <= 0) {
        $mensaje = 'La cuota mensual tiene que ser un número mayor a 0.';
    } else {
        $datos = [
            'pago_alias'    => $alias,
            'pago_titular'  => $titular,
            'pago_whatsapp' => $whatsapp,
            'cuota_mensual' => number_format((float)$cuota, 2, '.', '')
        ];

        // Si la clave ya existe la actualiza, si no existe la crea
        $stmt = $conexion->prepare("INSERT INTO configuracion (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
        $ok = true;

        foreach ($datos as $clave => $valor) {
            $stmt->bind_param('ss', $clave, $valor);
            if (!$stmt->execute()) {
                $ok = false;
            }
        }
        $stmt->close();

        $mensaje = $ok ? 'Datos de pago actualizados.' : 'Error al guardar los datos de pago.';
    }
}

/* Cargar los datos de pago actuales para mostrarlos en el formulario */
$pago = ['pago_alias' => '', 'pago_titular' => '', 'pago_whatsapp' => '', 'cuota_mensual' => '5000'];
$resPago = $conexion->query("SELECT clave, valor FROM configuracion");
if ($resPago) {
    while ($f = $resPago->fetch_assoc()) {
        if (isset($pago[$f['clave']])) {
            $pago[$f['clave']] = $f['valor'];
        }
    }
}


/* BUSCADOR DE SOCIOS */
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';

$sqlSocios = "SELECT id_socio, nombre, apellido, email, direccion, dni, telefono FROM socios";
$tiposSocios = '';
$paramsSocios = [];

if ($busqueda !== '') {
    // Cada palabra tiene que coincidir con algún campo (así "juan perez" encuentra nombre=Juan y apellido=Perez)
    $condiciones = [];
    foreach (preg_split('/\s+/', $busqueda) as $palabra) {
        $like = '%' . addcslashes($palabra, '%_\\') . '%';
        $condiciones[] = "(nombre LIKE ? OR apellido LIKE ? OR email LIKE ? OR dni LIKE ? OR direccion LIKE ? OR telefono LIKE ?)";
        for ($i = 0; $i < 6; $i++) {
            $tiposSocios .= 's';
            $paramsSocios[] = $like;
        }
    }
    $sqlSocios .= " WHERE " . implode(' AND ', $condiciones);
}

$sqlSocios .= " ORDER BY apellido, nombre";

$stmtSocios = $conexion->prepare($sqlSocios);
if ($tiposSocios !== '') {
    $stmtSocios->bind_param($tiposSocios, ...$paramsSocios);
}
$stmtSocios->execute();
$resSocios = $stmtSocios->get_result();
$totalSocios = $resSocios->num_rows;


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de admin</title>
    <link rel="stylesheet" href="styles/adminGaleria.css">
    <style>
        /* Sección de socios */
        .socios-panel { max-width: 1100px; margin: 30px auto; padding: 0 20px; color: #fff; }
        .socios-panel h2 { margin: 0 0 20px; color: #fd5c01; font-size: 26px; letter-spacing: 2px; text-transform: uppercase; border-bottom: 2px solid #fd5c01; padding-bottom: 12px; width: fit-content; }

        .buscador { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
        .buscador input[type="text"] { flex: 1; min-width: 220px; padding: 12px 15px; font-size: 16px; background: #1a1a1a; color: #fff; border: 2px solid #0b0b0b; border-radius: 10px; outline: none; transition: .3s; }
        .buscador input[type="text"]:focus { border-color: #fd5c01; }
        .buscador input[type="text"]::placeholder { color: #888; }
        .buscador button, .buscador a { padding: 12px 25px; font-size: 16px; font-weight: bold; border-radius: 10px; border: none; cursor: pointer; text-decoration: none; transition: .3s; }
        .buscador button { background: #fd5c01; color: #000; }
        .buscador button:hover { background: #ff7b2f; }
        .buscador a { background: #0b0b0b; color: #fd5c01; display: flex; align-items: center; }
        .buscador a:hover { background: #1a1a1a; }

        .contador { margin: 0 0 12px; color: #aaa; font-size: 14px; }

        .tabla-wrapper { overflow-x: auto; border-radius: 10px; }
        table.socios { width: 100%; border-collapse: collapse; background: #1a1a1a; }
        table.socios th, table.socios td { padding: 12px 14px; text-align: left; border-bottom: 1px solid #333; }
        table.socios th { background: #0b0b0b; color: #fd5c01; letter-spacing: 1px; text-transform: uppercase; font-size: 13px; white-space: nowrap; }
        table.socios td { color: #ddd; }
        table.socios tbody tr:hover td { background: #fd5c01; color: #000; }
        table.socios tbody tr.fila { cursor: pointer; }
    </style>
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

<!-- DATOS DE PAGO: lo que ven los socios en "Mi cuenta" -->
<div class="agregar">

    <form method="post">

        <input type="hidden" name="accion" value="datos_pago">

        <div class="contenedor">
            <h1>Datos de pago de los socios</h1>

            <label>Alias:</label>
            <input type="text" name="alias" maxlength="100" required
                   value="<?php echo htmlspecialchars($pago['pago_alias']); ?>">

            <br>

            <label>Nombre del titular:</label>
            <input type="text" name="titular" maxlength="100" required
                   value="<?php echo htmlspecialchars($pago['pago_titular']); ?>">

            <br>

            <label>Número de WhatsApp:</label>
            <input type="text" name="whatsapp" maxlength="30"
                   value="<?php echo htmlspecialchars($pago['pago_whatsapp']); ?>">

            <br>

            <label>Cuota mensual ($):</label>
            <input type="text" name="cuota" maxlength="12" inputmode="decimal" required
                   value="<?php echo htmlspecialchars((string)(float)$pago['cuota_mensual']); ?>">

            <br>

            <button type="submit">Guardar datos de pago</button>
        </div>

    </form>
</div>

<!-- SOCIOS: buscador y listado -->
<div class="socios-panel" id="socios">
    <h2>Socios registrados</h2>

    <form class="buscador" method="GET" action="adminGaleria.php#socios">
        <input type="text" name="q" placeholder="Buscar por nombre, apellido, email, DNI, dirección o teléfono"
               value="<?php echo htmlspecialchars($busqueda); ?>">
        <button type="submit">Buscar</button>
        <?php if ($busqueda !== ''): ?>
            <a href="adminGaleria.php#socios">Limpiar</a>
        <?php endif; ?>
    </form>

    <p class="contador"><?php echo $totalSocios; ?> socio<?php echo $totalSocios == 1 ? '' : 's'; ?> encontrado<?php echo $totalSocios == 1 ? '' : 's'; ?> &middot; Hacé click en un socio para ver y registrar sus pagos</p>

    <div class="tabla-wrapper">
        <table class="socios">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Email</th>
                    <th>Dirección</th>
                    <th>DNI</th>
                    <th>Teléfono</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($totalSocios > 0): ?>
                <?php while ($socio = $resSocios->fetch_assoc()): ?>
                    <tr class="fila" onclick="window.location.href='socioPagos.php?id=<?php echo (int)$socio['id_socio']; ?>'">
                        <td><?php echo (int)$socio['id_socio']; ?></td>
                        <td><?php echo htmlspecialchars($socio['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($socio['apellido']); ?></td>
                        <td><?php echo htmlspecialchars($socio['email']); ?></td>
                        <td><?php echo htmlspecialchars($socio['direccion']); ?></td>
                        <td><?php echo htmlspecialchars($socio['dni']); ?></td>
                        <td><?php echo htmlspecialchars($socio['telefono']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7">No se encontraron socios.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

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
