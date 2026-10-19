<?php
session_start();
include('conexion.php');
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Solo socios logueados
if (!isset($_SESSION['socio_id'])) {
    header('Location: loginSocio.php');
    exit;
}

$meses = [1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
          'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

// El id sale de la sesión, nunca de la URL: así un socio solo puede ver SUS datos
$id = (int)$_SESSION['socio_id'];

$stmt = mysqli_prepare($conexion, "SELECT nombre, apellido, email, dni, fecha_alta FROM socios WHERE id_socio = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$socio = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$socio) {
    session_unset();
    session_destroy();
    header('Location: loginSocio.php');
    exit;
}

// Datos de pago (los edita el admin desde adminGaleria.php)
$pago = ['pago_alias' => '', 'pago_titular' => '', 'pago_whatsapp' => '', 'cuota_mensual' => '5000'];
$resPago = mysqli_query($conexion, "SELECT clave, valor FROM configuracion");
if ($resPago) {
    while ($f = mysqli_fetch_assoc($resPago)) {
        if (isset($pago[$f['clave']])) {
            $pago[$f['clave']] = $f['valor'];
        }
    }
}
$MENSUALIDAD = (float)$pago['cuota_mensual'];

$anioActual = (int)date('Y');
$mesActual  = (int)date('n');

$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : $anioActual;
if ($anio < 2000 || $anio > 2100) {
    $anio = $anioActual;
}

$altaTs   = strtotime($socio['fecha_alta']);
$altaAnio = (int)date('Y', $altaTs);
$altaMes  = (int)date('n', $altaTs);

// Todos los pagos del socio, indexados por "anio-mes"
$pagos = [];
$stmt = mysqli_prepare($conexion, "SELECT mes, anio, monto, fecha_pago FROM pagos WHERE id_socio = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($f = mysqli_fetch_assoc($res)) {
    $pagos[$f['anio'] . '-' . $f['mes']] = $f;
}
mysqli_stmt_close($stmt);

// Meses adeudados: desde el mes del alta hasta el mes actual, los que no tienen pago
$adeudados = [];
$a = $altaAnio;
$m = $altaMes;
while ($a < $anioActual || ($a == $anioActual && $m <= $mesActual)) {
    if (!isset($pagos[$a . '-' . $m])) {
        $adeudados[] = $meses[$m] . ' ' . $a;
    }
    $m++;
    if ($m > 12) {
        $m = 1;
        $a++;
    }
}
$cantDeuda = count($adeudados);

// Resumen del año que se está mirando
$pagadosAnio = 0;
$totalAnio = 0;
foreach ($meses as $num => $n) {
    if (isset($pagos[$anio . '-' . $num])) {
        $pagadosAnio++;
        $totalAnio += $pagos[$anio . '-' . $num]['monto'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi cuenta - Cardinal Sur</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="icon" type="image/x-icon" href="assets/favicon.png">

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
  <a class="menu" href="miCuenta.php">
    <img src="assets/socio.svg" class="icono"> <p>MI CUENTA</p>
  </a>
  <a class="menu" href="logout.php">
    <img src="assets/logout.svg" class="icono"> <p>CERRAR SESIÓN</p>
  </a>
</div>

<div class="cuenta-container">
    <h2>Hola, <?php echo htmlspecialchars($socio['nombre']); ?></h2>
    <p class="subtitulo">
        <?php echo htmlspecialchars($socio['nombre'] . ' ' . $socio['apellido']); ?>
        &middot; DNI <?php echo htmlspecialchars($socio['dni']); ?>
        &middot; Socio desde <?php echo date('d/m/Y', $altaTs); ?>
    </p>

    <?php if ($cantDeuda == 0): ?>
        <div class="banner ok">
            <strong>¡Estás al día!</strong>
            <p>No tenés cuotas pendientes. Gracias por ser parte de Cardinal Sur.</p>
        </div>
    <?php else: ?>
        <div class="banner deuda">
            <strong>Adeudás <?php echo $cantDeuda; ?> cuota<?php echo $cantDeuda == 1 ? '' : 's'; ?></strong>
            <p><?php echo htmlspecialchars(implode(', ', $adeudados)); ?></p>
            <p>Total aproximado a valor de cuota actual: $<?php echo number_format($cantDeuda * $MENSUALIDAD, 2, ',', '.'); ?></p>
        </div>
    <?php endif; ?>

    <div class="como-pagar">
        <h3>Cómo pagar</h3>
        <p><span>Cuota mensual:</span> $<?php echo number_format($MENSUALIDAD, 2, ',', '.'); ?></p>
        <?php if ($pago['pago_alias'] !== ''): ?>
            <p><span>Alias:</span> <?php echo htmlspecialchars($pago['pago_alias']); ?></p>
        <?php endif; ?>
        <?php if ($pago['pago_titular'] !== ''): ?>
            <p><span>Titular:</span> <?php echo htmlspecialchars($pago['pago_titular']); ?></p>
        <?php endif; ?>
        <?php if ($pago['pago_whatsapp'] !== ''): ?>
            <p>Después de pagar, avisanos por WhatsApp al <?php echo htmlspecialchars($pago['pago_whatsapp']); ?> y registramos tu pago.</p>
        <?php endif; ?>
    </div>

    <div class="selector-anio">
        <a href="?anio=<?php echo $anio - 1; ?>">&larr;</a>
        <strong><?php echo $anio; ?></strong>
        <a href="?anio=<?php echo $anio + 1; ?>">&rarr;</a>
    </div>
    

    <div class="meses">
    <?php foreach ($meses as $num => $nombreMes):
        $clave = $anio . '-' . $num;
        $pagado = isset($pagos[$clave]);
        $antesAlta = ($anio < $altaAnio) || ($anio == $altaAnio && $num < $altaMes);
        $esFuturo = ($anio > $anioActual) || ($anio == $anioActual && $num > $mesActual);

        if ($pagado)         { $clase = 'pagado'; }
        elseif ($antesAlta)  { $clase = 'previo'; }
        elseif ($esFuturo)   { $clase = 'futuro'; }
        else                 { $clase = 'adeudado'; }
    ?>
        <div class="mes <?php echo $clase; ?>">
            <h3><?php echo $nombreMes; ?></h3>
            <?php if ($pagado): ?>
                <div class="estado">PAGADO</div>
                <div class="detalle">
                    $<?php echo number_format($pagos[$clave]['monto'], 2, ',', '.'); ?>
                    &middot; <?php echo date('d/m/Y', strtotime($pagos[$clave]['fecha_pago'])); ?>
                </div>
            <?php else: ?>
                <div class="estado"><?php echo $antesAlta ? 'ANTES DE ASOCIARTE' : ($esFuturo ? 'PENDIENTE' : 'ADEUDADO'); ?></div>
                <div class="detalle">&nbsp;</div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    </div>
</div>

</body>
</html>
