<?php
session_start();
include('conexion.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cardinal Sur</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="icon" type="image/x-icon" href="assets/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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


<div class="headerContainer">
  <img src="assets/headerFoto.png" class="imagen" />
</div>
<div class="galeria">

  <a href="gallery.php" class="item">
    <img src="assets/fotoGaleria1.png" alt="Galería" />
    <div class="overlay"><p>GALERÍA</p></div>
  </a>

  <a href="help.php" class="item">
    <img src="assets/fotoGaleria2.png" alt="Acerca de nosotros" />
    <div class="overlay"><p>ACERCA DE NOSOTROS</p></div>
  </a>

    <a href="sociosRegistro.php" class="item">
    <img src="assets/fotoGaleria5.png" alt="Próximamente" />
    <div class="overlay"><p>HACETE SOCIO</p></div>
  </a>


  <a href="https://cardinalsuroffroad.taplink.site/" class="item">
    <img src="assets/fotoGaleria4.png" alt="Eventos" />
    <div class="overlay"><p>REDES SOCIALES</p></div>
  </a>

    <a href="store.php" class="item">
    <img src="assets/fotoGaleria3.png" alt="Aportes solidarios" />
    <div class="overlay"><p>NUESTRA TIENDA</p></div>
  </a>


</div>




<footer>
  <div class="footer-grid">

    <div>
      <h3>Cardinal Sur</h3>
      <p>Explorando Necochea</p>
    </div>

    <div>
      <span>Explorar</span>
      <a href="#">Fotos</a>
      <a href="#">Travesías</a>
      <a href="#">Mapas</a>
    </div>

    <div>
      <span>Info</span>
      <a href="#"><i class="fab fa-instagram"></i></a>
      <a href="#"><i class="fab fa-youtube"></i></a>
      <a href="#"><i class="fab fa-tiktok"></i></a>
    </div>

  </div>


  <div class="footer-bottom">
    <p>© 2026 Cardinal Sur</p>
  </div>


</footer>

<script src="script.js"></script>
</body>
</html>
