<?php
session_start();

?>

<!DOCTYPE html>
<html>

<head>

    <title>Login - Cardinal Sur</title>

    <link rel="stylesheet" href="styles/login.css">

</head>

<body>

<div class="login-container">

    <form class="login-card" action="procesar_login.php" method="POST">

        <h1>Cardinal Sur</h1>

        <input
        type="text"
        name="usuario"
        placeholder="Usuario"
        required>

        <input
        type="password"
        name="password"
        placeholder="Contraseña"
        required>

        <button type="submit">

            Ingresar

        </button>

    </form>

</div>

</body>

</html>