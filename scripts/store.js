document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.agregarCarrito');
    const pTotal = document.getElementById('totalCarrito');
    const cTotal = document.getElementById('cantidadCarrito');

    let total = 0;
    let cantidad = 0;

    function simboloPrecio(num) {
        return '$' + num;
    }

    function update() {
        pTotal.textContent = simboloPrecio(total);
        cTotal.textContent = cantidad;
    }

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const precio = Number(btn.value) || 0;
            total += precio;
            cantidad += 1;
            update();
        });
    });

});
