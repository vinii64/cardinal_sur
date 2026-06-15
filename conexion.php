<?php
    $conexion = new mysqli("localhost","root","","cardinal_sur_db");

    if ($conexion->connect_error){
        die("Error al conectarse".$conexion->connect_error);
    }
?>