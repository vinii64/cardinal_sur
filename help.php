<?php
// Si la sesión no ha sido iniciada en otro archivo (header.php, etc.), la iniciamos aquí
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acerca de nosotros</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/about.css">
    <link rel="icon" type="image/x-icon" href="assets/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="navbar"> 


  <a class="menu" href="">
    <img src="assets/logoLetras.png" class="iconoCardinal">
  </a>

  <a class="menu" href="">
    <img src="assets/image.svg" class="icono"> <p>FOTOS</p>
  </a>

  <a class="menu" href="">
    <img src="assets/map.svg" class="icono"> <p>TRAVESÍAS</p>
  </a>

  <a class="menu" href="store.php">
  <img src="assets/store.svg" class="icono"> <p>TIENDA</p>
  </a>

  <a class="menu" href="">
    <img src="assets/help.svg" class="icono"> <p>ACERCA</p>
  </a>

  
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


<div class="titulo">
    <img src="assets/fondo.jpg" alt="" class="titulo-img">

    <div class="titulo-texto">
        <h1>Cardinal Sur Off Road</h1>
        <p>Pasión por la aventura, la naturaleza y el espíritu 4x4.</p>
    </div>
</div>

    <div class="caja">
        <div class="contenido">

            <h2>Nuestra historia</h2>

            <p>
                Cardinal Sur Off Road nació de la pasión de un grupo de entusiastas del mundo off road que comparten el deseo de explorar nuevos caminos, disfrutar de la naturaleza y promover un manejo responsable en todo tipo de terrenos.
            </p>

            <p>
                Con el paso del tiempo, Cardinal Sur Off Road fue creciendo y consolidándose como una comunidad dedicada a organizar encuentros, travesías y actividades para quienes disfrutan del espíritu todo terreno.
            </p>

            <h2>Lo que nos mueve</h2>

            <!-----------FOTOS-------------->
            <div class="cartas">

                <div class="carta">
                    <img src="assets/carta-1.jpg">
                    <h3>Aventura</h3>
                    <p>Disfrutamos descubrir nuevos caminos y compartir experiencias.</p>
                </div>

                <div class="carta">
                    <img src="assets/carta-2.jpg">
                    <h3>Naturaleza</h3>
                    <p>Respetamos y cuidamos cada lugar que visitamos.</p>
                </div>

                <div class="carta">
                    <img src="assets/carta-3.jpg">
                    <h3>Comunidad</h3>
                    <p>Creemos en el compañerismo y el trabajo en equipo.</p>
                </div>

            </div>

            <div class="contacto">
                <h2>¿Querés saber más?</h2>

                <p>Seguinos en nuestras redes y conocé nuestras próximas travesías.</p>

                <a href="https://cardinalsuroffroad.taplink.site/">Seguinos</a>
            </div>

        </div>
    </div>

  <!-------------------FOOTER------------------- -->

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

</body>
</html>