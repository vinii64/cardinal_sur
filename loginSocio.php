<?php
session_start();
include('conexion.php');

if (isset($_SESSION['socio_id'])) {
    header('Location: miCuenta.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identificador = isset($_POST['identificador']) ? trim($_POST['identificador']) : '';
    $pass = isset($_POST['contrasenia']) ? $_POST['contrasenia'] : '';

    if ($identificador === '' || $pass === '') {
        $error = 'Completá todos los campos.';
    } else {
        $stmt = mysqli_prepare($conexion, "SELECT id_socio, nombre, password FROM socios WHERE email = ? OR dni = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "ss", $identificador, $identificador);
        mysqli_stmt_execute($stmt);
        $socio = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        $passOk = false;
        if ($socio && $socio['password'] !== null && $socio['password'] !== '') {
            $hash = $socio['password'];
            if (strlen($hash) === 32 && ctype_xdigit($hash)) {
                // Contraseña guardada con MD5 (como se guarda en la base actual)
                $passOk = hash_equals(strtolower($hash), md5($pass));
            } else {
                // Contraseña guardada con password_hash (bcrypt)
                $passOk = password_verify($pass, $hash);
            }
        }

        if ($passOk) {
            session_regenerate_id(true);
            $_SESSION['socio_id'] = (int)$socio['id_socio'];
            $_SESSION['socio_nombre'] = $socio['nombre'];
            header('Location: miCuenta.php');
            exit;
        } else {
            $error = 'Datos incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ingresá - Cardinal Sur Off Road</title>
    <link rel="stylesheet" href="styles/login.css">
    <link rel="icon" type="image/x-icon" href="assets/favicon.png">
    <style>
        .login-card h2 { text-align: center; color: #fd5c01; }
        .msg-error { color: #ff6b6b; text-align: center; margin: 0 0 10px; font-size: 14px; }
        .links { text-align: center; margin-top: 14px; font-size: 14px; }
        .links a { color: #fd5c01; text-decoration: none; display: block; margin-top: 6px; }
        .links a:hover { text-decoration: underline; }
        .links .admin { color: #888; font-size: 12px; margin-top: 16px; }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">

        <div class="logo">
            <img src="assets/logoLetras.png" alt="Cardinal Sur">
        </div>

        <h2>Mi cuenta de socio</h2>

        <?php if ($error !== ''): ?>
            <p class="msg-error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="text" name="identificador" placeholder="Email o DNI" required
                   value="<?php echo isset($_POST['identificador']) ? htmlspecialchars($_POST['identificador']) : ''; ?>">
            <input type="password" name="contrasenia" placeholder="Contraseña" required>
            <button type="submit">Ingresar</button>
        </form>

        <div class="links">
            <a href="sociosRegistro.php">¿Todavía no sos socio? Registrate acá</a>

            <a class="admin" href="login.php">Acceso administradores</a>
        </div>

    </div>
</div>

</body>
</html>
