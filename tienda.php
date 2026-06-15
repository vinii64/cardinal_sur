<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda - Cardinal Sur</title>

    <link rel="stylesheet" href="styles/style.css">
    <link rel="icon" type="image/x-icon" href="assets/favicon.png">

    <link rel="stylesheet" href="styles/tienda.css">

</head>
<body>

<div class="navbar"> 

  <a class="menu" href="index.php">
    <img src="assets/logoLetras.png" class="iconoCardinal">
  </a>

  <a class="menu" href="">
    <img src="assets/image.svg" class="icono">
    <p>FOTOS</p>
  </a>

  <a class="menu" href="">
    <img src="assets/map.svg" class="icono">
    <p>TRAVESÍAS</p>
  </a>

  <a class="menu" href="tienda.html">
    <i class="fas fa-store icono"></i>
    <p>TIENDA</p>
  </a>

  <a class="menu" href="">
    <img src="assets/help.svg" class="icono">
    <p>ACERCA</p>
  </a>

  <a class="menu carrito derecha" href="#" style="float:right;">

    <p>CARRITO</p>
  </a>
</div>


<div class="tienda-container">

    <h1>Tienda</h1>

    <div class="productos">

        <div class="card-producto">
            <img src="assets/remera1.jpg">

            <div class="info-producto">
                <h2>Remera Cardinal Sur</h2>
                <p>Algodón 100%</p>

                <span>$25.000</span>

                <button>Agregar al Carrito</button>
            </div>
        </div>


        <div class="card-producto">
            <img src="assets/gorra1.jpg">

            <div class="info-producto">
                <h2>Gorra Cardinal Sur</h2>
                <p>Edición explorador</p>

                <span>$18.000</span>

                <button>Agregar al Carrito</button>
            </div>
        </div>


        <div class="card-producto">
            <img src="assets/taza1.jpg">

            <div class="info-producto">
                <h2>Banderin Cardinal Sur</h2>
                <p>Lorem ipsum dolor sit</p>

                <span>$12.000</span>

                <button>Agregar al Carrito</button>
            </div>
        </div>

    </div>

</div>

</body>
</html>