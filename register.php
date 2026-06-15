<?php

    include('conexion.php');
    if (isset($_POST['usuario'])){
        $email = $_POST['email'];
        $usuario = $_POST['usuario'];
        $dni = $_POST['dni'];
        $contrasenia = $_POST['contrasenia'];

        $sql = "SELECT * FROM usuarios WHERE email = '".$email."'";

        $resultado = mysqli_query($conexion,$sql);
        $filas = mysqli_num_rows($resultado);
        

        if($filas == 0){
            $contraEncriptada = md5($contrasenia);
            $consulta="INSERT INTO usuarios (dni, email, usuario, contrasenia) VALUES
            ('".$_POST['dni']."','".$_POST['email']."','".$_POST['usuario']."','".$contraEncriptada."')";
            $resultado = mysqli_query($conexion,$consulta);
            echo "<script>alert('Cuenta creada correctamente.'); window.location.href='login.php';</script>";
        }
        else{
            echo "<script>alert('El correo ya esta en uso')</script>";
        }
    } 
?>
<!DOCTYPE html>
<html>
<head>
    <title>Regístrate - Cardinal Sur Off Road</title>
    <link rel="stylesheet" href="styles/login.css">
    <link rel="icon" type="image/x-icon" href="assets/favicon.png">
</head>
<body>

<div class="login-container">
    <div class="login-card">

        <div class="logo">
            <img src="assets/logoLetras.png" alt="Cardinal Sur">
        </div>

        <form action="" method="POST">
            <input type="text" name="usuario" placeholder="Usuario" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="dni" placeholder="DNI" required>
            <input type="password" name="contrasenia" placeholder="Contraseña" required>
            <button type="submit">Registra tu cuenta</button>
        </form>

        <div class="cartel">
            <p>¿Ya tenés cuenta? <a href="login.php">Iniciá sesión</a></p>
        </div>

    </div>
</div>

</body>
</html>