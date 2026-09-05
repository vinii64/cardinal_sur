<?php
session_start();
include('conexion.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeria - Cardinal Sur</title>
    <link rel="stylesheet" href="styles/gallery.css">
    <link rel="icon" type="image/x-icon" href="assets/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body><div class="navbar"> 


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

  <?php if (isset($_SESSION['usuario']) && $_SESSION['admin'] == 1): ?>
    <a class="menu" href="adminGaleria.php">
      <img src="assets/admin.svg" class="icono"> <p>ADMIN</p>
    </a>
  <?php endif; ?>

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



<main class="gallery-section">

  <h1 class="gallery-title">GALERÍA</h1>

  <div class="gallery-grid">

    <?php
    $resultado = $conexion->query("SELECT id_foto, titulo FROM galeria ORDER BY id_foto DESC");

    if ($resultado && $resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            $alt = $fila['titulo'] !== null && $fila['titulo'] !== '' ? $fila['titulo'] : 'Foto de la galería';
    ?>

    <div class="gallery-item">
      <img src="verFoto.php?id=<?php echo $fila['id_foto']; ?>" alt="<?php echo htmlspecialchars($alt); ?>">
    </div>

    <?php
        }
    } else {
        echo '<p class="gallery-empty">Todavía no hay fotos cargadas.</p>';
    }
    ?>

  </div>

</main>


<!-- Lightbox: se muestra al hacer click en una imagen -->
<div class="lightbox" id="lightbox">
  <span class="lightbox-close" id="lightboxClose">&times;</span>
  <img src="" alt="" id="lightboxImg">
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

<script src="scripts/gallery.js"></script>

</body>
</html>
