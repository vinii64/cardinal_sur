<?php
session_start();
include('conexion.php');

// Cuota mensual por defecto (se cambia desde adminGaleria.php)

$resCfg = mysqli_query($conexion, "SELECT valor FROM configuracion WHERE clave = 'cuota_mensual'");
if ($resCfg && ($filaCfg = mysqli_fetch_assoc($resCfg))) {
    $MENSUALIDAD = (float)$filaCfg['valor'];
}
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Solo admins logueados
if (!isset($_SESSION['usuario']) || !isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
    header('Location: index.php');
    exit;
}

$meses = [1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
          'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');
if ($anio < 2000 || $anio > 2100) {
    $anio = (int)date('Y');
}

// Datos del socio
$stmt = mysqli_prepare($conexion, "SELECT id_socio, nombre, apellido, email, direccion, dni, telefono, fecha_alta FROM socios WHERE id_socio = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$socio = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$socio) {
    header('Location: index.php');
    exit;
}

// Registrar o anular un pago
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'], $_POST['mes'])) {
    $mes = (int)$_POST['mes'];

    if ($mes >= 1 && $mes <= 12) {
        if ($_POST['accion'] === 'pagar') {
            $monto = (float)str_replace(',', '.', isset($_POST['monto']) ? $_POST['monto'] : '0');
            if ($monto <= 0) {
                $monto = $MENSUALIDAD;
            }
            // INSERT IGNORE: si ya estaba pago ese mes, no lo duplica (la tabla tiene UNIQUE)
            $stmt = mysqli_prepare($conexion, "INSERT IGNORE INTO pagos (id_socio, mes, anio, monto, fecha_pago) VALUES (?, ?, ?, ?, CURDATE())");
            mysqli_stmt_bind_param($stmt, "iiid", $id, $mes, $anio, $monto);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif ($_POST['accion'] === 'anular') {
            $stmt = mysqli_prepare($conexion, "DELETE FROM pagos WHERE id_socio = ? AND mes = ? AND anio = ?");
            mysqli_stmt_bind_param($stmt, "iii", $id, $mes, $anio);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    // Redirige para que al recargar no se repita el envío del formulario
    header("Location: socioPagos.php?id=$id&anio=$anio");
    exit;
}

// Pagos del socio en el año elegido
$pagos = [];
$totalAnio = 0;
$stmt = mysqli_prepare($conexion, "SELECT mes, monto, fecha_pago FROM pagos WHERE id_socio = ? AND anio = ?");
mysqli_stmt_bind_param($stmt, "ii", $id, $anio);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($fila = mysqli_fetch_assoc($res)) {
    $pagos[(int)$fila['mes']] = $fila;
    $totalAnio += $fila['monto'];
}
mysqli_stmt_close($stmt);

$anioActual = (int)date('Y');
$mesActual  = (int)date('n');

$altaTs   = strtotime($socio['fecha_alta']);
$altaAnio = (int)date('Y', $altaTs);
$altaMes  = (int)date('n', $altaTs);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos de <?php echo htmlspecialchars($socio['nombre'] . ' ' . $socio['apellido']); ?> - Cardinal Sur</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="icon" type="image/x-icon" href="assets/favicon.png">
    <style>
        .pagos-container { max-width: 1100px; margin: 0 auto; padding: 120px 20px 60px; color: #fff; }
        .volver { color: #fd5c01; text-decoration: none; display: inline-block; margin-bottom: 16px; }
        .volver:hover { text-decoration: underline; }
        .pagos-container h2 { margin: 0 0 20px; color: #fd5c01; font-size: 28px; letter-spacing: 2px; text-transform: uppercase; border-bottom: 2px solid #fd5c01; padding-bottom: 12px; width: fit-content; }

        .ficha { background: #1a1a1a; border-radius: 10px; padding: 16px 20px; margin-bottom: 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px 24px; }
        .ficha span { display: block; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; color: #777; }
        .ficha p { margin: 2px 0 0; color: #ddd; word-break: break-word; }

        .selector-anio { display: flex; align-items: center; gap: 14px; margin-bottom: 8px; }
        .selector-anio a { background: #0b0b0b; color: #fd5c01; text-decoration: none; padding: 8px 16px; border-radius: 10px; font-weight: bold; transition: .3s; }
        .selector-anio a:hover { background: #1a1a1a; }
        .selector-anio strong { font-size: 24px; }
        .resumen { color: #aaa; font-size: 14px; margin: 0 0 18px; }

        .meses { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; }
        .mes { background: #1a1a1a; border-radius: 10px; padding: 14px; border: 2px solid #0b0b0b; display: flex; flex-direction: column; gap: 6px; }
        .mes h3 { margin: 0; font-size: 18px; }
        .mes .estado { font-size: 12px; letter-spacing: 2px; font-weight: bold; }
        .mes .detalle { font-size: 13px; opacity: .85; min-height: 17px; }

        .mes.pagado { background: #fd5c01; color: #000; border-color: #fd5c01; }
        .mes.adeudado { border-color: #c0392b; }
        .mes.adeudado .estado { color: #e74c3c; }
        .mes.futuro, .mes.previo { opacity: .5; }

        .mes form { display: flex; gap: 6px; margin-top: auto; }
        .mes button { flex: 1; padding: 8px; font-weight: bold; border: none; border-radius: 8px; cursor: pointer; transition: .3s; background: #fd5c01; color: #000; }
        .mes button:hover { background: #ff7b2f; }
        .mes.pagado button { background: #000; color: #fd5c01; }
        .mes.pagado button:hover { background: #1a1a1a; }
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

  <?php if (isset($_SESSION['usuario']) && isset($_SESSION['admin']) && $_SESSION['admin'] == 1): ?>
    <a class="menu" href="adminGaleria.php">
      <img src="assets/admin.svg" class="icono"> <p>ADMIN</p>
    </a>

  <?php endif; ?>

  <!-- si hay un socio logueado muestra MI CUENTA, si no muestra UNIRSE -->
<?php if (isset($_SESSION['socio_id'])): ?>
  <a class="menu" href="miCuenta.php">
    <img src="assets/socio.svg" class="icono"> <p>MI CUENTA</p>
  </a>

<?php elseif (!isset($_SESSION['admin']) || $_SESSION['admin'] != 1): ?>
  <a class="menu" href="sociosRegistro.php">
    <img src="assets/socio.svg" class="icono"> <p>UNIRSE</p>
  </a>
<?php endif; ?>



  <!-- esto hace q cuando no estas logeado (admin o socio) muestre el boton de login pero si lo estas muestre el boton de logout -->
  <?php if (isset($_SESSION['usuario']) || isset($_SESSION['socio_id'])): ?>
    <a class="menu" href="logout.php">
      <img src="assets/logout.svg" class="icono"> <p>CERRAR SESIÓN</p>
    </a>
  <?php else: ?>
    <a class="menu" href="loginSocio.php">
      <img src="assets/login.svg" class="icono"> <p>INGRESA</p>
    </a>
  <?php endif; ?>
<!-- esto hace q cuando no estas logeado muestre el boton de login pero si lo estas muestre el boton de logout -->



</div>

<div class="pagos-container">
    <a class="volver" href="adminGaleria.php">&larr; Volver a socios</a>
    <h2><?php echo htmlspecialchars($socio['nombre'] . ' ' . $socio['apellido']); ?></h2>

    <div class="ficha">
        <div><span>DNI</span><p><?php echo htmlspecialchars($socio['dni']); ?></p></div>
        <div><span>Email</span><p><?php echo htmlspecialchars($socio['email']); ?></p></div>
        <div><span>Teléfono</span><p><?php echo htmlspecialchars($socio['telefono']); ?></p></div>
        <div><span>Dirección</span><p><?php echo htmlspecialchars($socio['direccion']); ?></p></div>
        <div><span>Socio desde</span><p><?php echo date('d/m/Y', $altaTs); ?></p></div>
    </div>

    <div class="selector-anio">
        <a href="?id=<?php echo $id; ?>&anio=<?php echo $anio - 1; ?>">&larr;</a>
        <strong><?php echo $anio; ?></strong>
        <a href="?id=<?php echo $id; ?>&anio=<?php echo $anio + 1; ?>">&rarr;</a>
    </div>
    <p class="resumen"><?php echo count($pagos); ?> de 12 meses pagados &middot; Total abonado: $<?php echo number_format($totalAnio, 2, ',', '.'); ?></p>

    <div class="meses">
    <?php foreach ($meses as $num => $nombreMes):
        $pagado = isset($pagos[$num]);
        $esFuturo = ($anio > $anioActual) || ($anio == $anioActual && $num > $mesActual);
        $antesAlta = ($anio < $altaAnio) || ($anio == $altaAnio && $num < $altaMes);

        if ($pagado)        { $clase = 'pagado'; }
        elseif ($antesAlta) { $clase = 'previo'; }
        elseif ($esFuturo)  { $clase = 'futuro'; }
        else                { $clase = 'adeudado'; }
    ?>
        <div class="mes <?php echo $clase; ?>">
            <h3><?php echo $nombreMes; ?></h3>

            <?php if ($pagado): ?>
                <div class="estado">PAGADO</div>
                <div class="detalle">
                    <?php echo date('d/m/Y', strtotime($pagos[$num]['fecha_pago'])); ?>
                </div>
                <form method="POST" action="" onsubmit="return confirm('¿Anular el pago de <?php echo $nombreMes . ' ' . $anio; ?>?');">
                    <input type="hidden" name="accion" value="anular">
                    <input type="hidden" name="mes" value="<?php echo $num; ?>">
                    <button type="submit">Anular</button>
                </form>
            <?php else: ?>
                <div class="estado"><?php echo $antesAlta ? 'ANTES DEL ALTA' : ($esFuturo ? 'PENDIENTE' : 'ADEUDADO'); ?></div>
                <div class="detalle">&nbsp;</div>
                <form method="POST" action="">
                    <input type="hidden" name="accion" value="pagar">
                    <input type="hidden" name="mes" value="<?php echo $num; ?>">
                    <button type="submit">Marcar como pago</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    </div>
</div>

</body>
</html>
