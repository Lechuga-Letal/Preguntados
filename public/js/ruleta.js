const categorias = window.categorias || [];
const colores = window.colores || ["#ffebb5", "#bcedff", "#c2e68d", "rgba(255,197,214,0.89)", "#8338ec"];

//console.log("Categorias cargadas:", categorias);

const canvas = document.getElementById("ruleta");
const ctx = canvas.getContext("2d");

const size = categorias.length;
const arc = (2 * Math.PI) / size;
let anguloActual = 0;

function dibujarRuleta() {
    for (let i = 0; i < size; i++) {
        const inicio = i * arc;

        ctx.beginPath();
        ctx.fillStyle = colores[i % colores.length];
        ctx.moveTo(150, 150);
        ctx.arc(150, 150, 140, inicio, inicio + arc);
        ctx.fill();

        ctx.save();
        ctx.translate(150, 150);
        ctx.rotate(inicio + arc / 2);
        ctx.textAlign = "right";
        ctx.fillStyle = "#ffffff";
        ctx.font = "bold 14px Arial";
        ctx.fillText(categorias[i].nombre, 125, 5);
        ctx.restore();
    }
}

dibujarRuleta();

const btn = document.getElementById("girarBtn");
const mensaje = document.getElementById("mensaje");
const form = document.getElementById("formRuleta");

btn.addEventListener("click", (e) => {
    e.preventDefault();

    btn.disabled = true;
    mensaje.textContent = "Girando...";

    const giroTotal = 360 * 5 + Math.random() * 360;
    const duracion = 4000;
    const inicio = performance.now();

    function animar(t) {
        const progreso = Math.min((t - inicio) / duracion, 1);
        const angulo = giroTotal * (1 - Math.pow(1 - progreso, 3));

        ctx.clearRect(0, 0, 300, 300);
        ctx.save();
        ctx.translate(150, 150);
        ctx.rotate((angulo * Math.PI) / 180);
        ctx.translate(-150, -150);
        dibujarRuleta();
        ctx.restore();

        if (progreso < 1) {
            requestAnimationFrame(animar);
        } else {
            anguloActual = angulo % 360;

            const ajustado = (anguloActual + 90) % 360;
            const indice = Math.floor(size - (ajustado / 360) * size) % size;

            const categoria = categorias[indice];

            mensaje.innerHTML = `Categoría seleccionada: <b>${categoria.nombre}</b>`;

            console.log(categoria.id); 
            document.getElementById("idCategoria").value = categoria.id;
            console.log(document.getElementById("idCategoria").value); 

            setTimeout(() => form.submit(), 2500);
            btn.disabled = false;
        }
    }

    requestAnimationFrame(animar);
});
