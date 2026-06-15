<?php
session_start();
include('conexion.php');

if (isset($_POST['usuario'])) {
    $usuario = trim($_POST['usuario']);
    $contrasenia = md5(trim($_POST['contrasenia']));

    $stmt = mysqli_prepare($conexion, "SELECT * FROM usuarios WHERE usuario = ? AND contrasenia = ?");
    mysqli_stmt_bind_param($stmt, "ss", $usuario, $contrasenia);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $datos = mysqli_fetch_assoc($resultado);

    if ($datos) {
        $_SESSION['usuario'] = $datos['usuario'];
        $_SESSION['id'] = $datos['id'];
        echo "<script>alert('Sesión iniciada correctamente.'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Usuario o contraseña incorrectos')</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inicia sesión - Cardinal Sur Off Road</title>
    <link rel="stylesheet" href="styles/login.css">
    <link rel="icon" type="image/x-icon" href="assets/favicon.png">
</head>
<body>

<div class="login-container">
    <div class="login-card">

        <div class="logo">
            <img src="assets/logoLetras.png" alt="Cardinal Sur" >
        </div>

        <form action="" method="post">

            <input type="text" name="usuario" placeholder="Usuario" required>

            <input type="password" name="contrasenia" placeholder="Contraseña" required>

            <button type="submit">Iniciar sesión</button>

        </form>

        <div class="cartel">
            <p>¿No tenés cuenta?
                <a href="register.php">
                    Registrate
                </a>
            </p>
        </div>

    </div>
</div>

</body>
</html>