<?php
session_start();
include('conexion.php');
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Si ya hay un socio logueado, no tiene sentido registrarse de nuevo
if (isset($_SESSION['socio_id'])) {
    header('Location: miCuenta.php');
    exit;
}

$error = '';
$v = ['nombre' => '', 'apellido' => '', 'email' => '', 'direccion' => '', 'dni' => '', 'telefono' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($v) as $campo) {
        $v[$campo] = isset($_POST[$campo]) ? trim($_POST[$campo]) : '';
    }

    $pass  = isset($_POST['contrasenia'])  ? $_POST['contrasenia']  : '';
    $pass2 = isset($_POST['contrasenia2']) ? $_POST['contrasenia2'] : '';

    if (in_array('', $v, true) || $pass === '') {
        $error = 'Completá todos los campos.';
    } elseif (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no es válido.';
    } elseif (strlen($pass) < 8) {
        $error = 'La contraseña tiene que tener al menos 8 caracteres.';
    } elseif ($pass !== $pass2) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        // Verificar que el email o el DNI no estén registrados
        $stmt = mysqli_prepare($conexion, "SELECT id_socio FROM socios WHERE email = ? OR dni = ?");
        mysqli_stmt_bind_param($stmt, "ss", $v['email'], $v['dni']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $existe = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);

        if ($existe) {
            $error = 'El email o el DNI ya están registrados. Si ya sos socio, ingresá desde "Ingresa" o creá tu contraseña en "Activar cuenta".';
        } else {
            // Encriptar la contraseña con MD5
            $hash = md5($pass);
            $alta = date('Y-m-d');

            $stmt = mysqli_prepare($conexion,
                "INSERT INTO socios (nombre, apellido, email, direccion, dni, telefono, password, fecha_alta) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            mysqli_stmt_bind_param($stmt, "ssssssss",
                $v['nombre'], $v['apellido'], $v['email'], $v['direccion'], $v['dni'], $v['telefono'], $hash, $alta);

            if (mysqli_stmt_execute($stmt)) {
                // Queda logueado automáticamente
                $nuevoId = mysqli_insert_id($conexion);
                session_regenerate_id(true);
                $_SESSION['socio_id'] = $nuevoId;
                $_SESSION['socio_nombre'] = $v['nombre'];
                header('Location: miCuenta.php');
                exit;
            } else {
                $error = 'Ocurrió un error al registrarte. Intentá de nuevo.';
            }

            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hacete socio - Cardinal Sur Off Road</title>
    <link rel="stylesheet" href="styles/login.css">
    <link rel="icon" type="image/x-icon" href="assets/favicon.png">

    <style>
        .login-card h2 {
            text-align: center;
            color: #fd5c01;
        }

        .msg-error {
            color: #ff6b6b;
            text-align: center;
            margin: 0 0 10px;
            font-size: 14px;
        }

        .links {
            text-align: center;
            margin-top: 14px;
            font-size: 14px;
        }

        .links a {
            color: #fd5c01;
            text-decoration: none;
            display: block;
            margin-top: 6px;
        }

        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

<div class="login-container">
    <div class="login-card">

        <div class="logo">
            <img src="assets/logoLetras.png" alt="Cardinal Sur">
        </div>

        <h2>Hacete socio</h2>

        <?php if ($error !== ''): ?>
            <p class="msg-error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="text" name="nombre" placeholder="Nombre" maxlength="50" required value="<?php echo htmlspecialchars($v['nombre']); ?>">
            <input type="text" name="apellido" placeholder="Apellido" maxlength="50" required value="<?php echo htmlspecialchars($v['apellido']); ?>">
            <input type="email" name="email" placeholder="Email" maxlength="100" required value="<?php echo htmlspecialchars($v['email']); ?>">
            <input type="text" name="direccion" placeholder="Dirección" maxlength="150" required value="<?php echo htmlspecialchars($v['direccion']); ?>">
            <input type="text" name="dni" placeholder="DNI" maxlength="10" required value="<?php echo htmlspecialchars($v['dni']); ?>">
            <input type="tel" name="telefono" placeholder="Número de teléfono" maxlength="20" required value="<?php echo htmlspecialchars($v['telefono']); ?>">
            <input type="password" name="contrasenia" placeholder="Contraseña (mínimo 8 caracteres)" required>
            <input type="password" name="contrasenia2" placeholder="Repetir contraseña" required>

            <button type="submit">Hacete socio</button>
        </form>

        <div class="links">
            <a href="loginSocio.php">¿Ya sos socio? Ingresá</a>
        </div>

    </div>
</div>

</body>
</html>
