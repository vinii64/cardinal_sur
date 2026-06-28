<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda - Cardinal Sur</title>

    <link rel="stylesheet" href="styles/style.css">
    <link rel="icon" type="image/x-icon" href="assets/favicon.png">
    <link rel="stylesheet" href="styles/store.css">

</head>


<body>

<div class="navbar"> 

  <a class="menu" href="index.php">
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

  <a class="menu" href="">
    <img src="assets/shopping.svg" class="icono"><p>CARRITO</p>
  </a>
</div>


<div class="tienda-container">

    <h1></h1>

    <div class="productos">

        <div class="card-producto">

            <img src="assets/remera.png">

            <div class="info-producto">
                <h2>Remera Cardinal Sur</h2>
                <p>Remera negra lisa con estampado frontal</p>

            <span>$30.000</span>

            <button class="agregarCarrito" value="30000">Agregar al Carrito</button>
            </div>
        </div>


        <div class="card-producto">
            <img src="assets/gorra.png">

            <div class="info-producto">
                <h2>Gorra Cardinal Sur</h2>
                <p>Gorra negra clásica con logo bordado al frente</p>

            <span>$15.000</span>

            <button class="agregarCarrito" value="15000">Agregar al Carrito</button>
            </div>
        </div>


        <div class="card-producto">
            <img src="assets/banderin.png">

            <div class="info-producto">
                <h2>Banderin Cardinal Sur</h2>
                <p>Banderín naranja con diseño invertido y mástil negro</p>

            <span>$8.000</span>

            <button class="agregarCarrito" value="8000">Agregar al Carrito</button>
            </div>
        </div>

        
        <div class="card-producto">
            <img src="assets/taza.png">

            <div class="info-producto">
                <h2>Taza Cardinal Sur</h2>
                <p>aza de cerámica negra mate con logo centrado.</p>

            <span>$5.000</span>

            <button class="agregarCarrito" value="5000">Agregar al Carrito</button>
            </div>
        </div>
        

    </div>

</div>

      <div class="precioCarrito">
        <h3>Carrito</h3>
        <div class="precioRow">
          <p>Total: <span id="totalCarrito">$0</span></p>
          <p>Items: <span id="cantidadCarrito">0</span></p>
          <a class="wspBtn" href="https://chat.whatsapp.com/GSz9qRSSgb8JSdfMf68AOc" target="_blank">Contactar por WhatsApp</a>
        </div>
      </div>

      <script src="scripts/store.js"></script>

    </body>
    </html>