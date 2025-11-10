const canvas = document.getElementById("ruletaCanvas");
const ctx = canvas.getContext("2d");
const btn = document.getElementById("girarBtn");
const mensaje = document.getElementById("mensaje");
const form = document.getElementById("formRuleta");

const categorias = ["Deportes", "Entretenimiento", "Informática", "Matemáticas", "Historia"];
const colores = ["#ffebb5", "#bcedff", "#c2e68d", "rgba(255,197,214,0.89)", "#8338ec"];
const numSegmentos = categorias.length;
const anguloSegmento = (2 * Math.PI) / numSegmentos;
let anguloActual = 0;

function dibujarRuleta() {
    for (let i = 0; i < numSegmentos; i++) {
        const angInicio = i * anguloSegmento;
        ctx.beginPath();
        ctx.moveTo(150, 150);
        ctx.arc(150, 150, 140, angInicio, angInicio + anguloSegmento);
        ctx.fillStyle = colores[i % colores.length];
        ctx.fill();

        ctx.save();
        ctx.translate(150, 150);
        ctx.rotate(angInicio + anguloSegmento / 2);
        ctx.textAlign = "right";
        ctx.fillStyle = "#fff";
        ctx.font = "bold 14px Poppins";
        ctx.fillText(categorias[i], 125, 5);
        ctx.restore();
    }
}

dibujarRuleta();

btn.addEventListener("click", (e) => {
    e.preventDefault();
    btn.disabled = true;
    mensaje.textContent = "Girando...";

    const giroTotal = 360 * 5 + Math.random() * 360;
    const duracion = 4000;
    const inicio = performance.now();

    function animar(tiempo) {
        const progreso = Math.min((tiempo - inicio) / duracion, 1);
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

            const anguloAjustado = (anguloActual + 90) % 360;
            const indice = Math.floor(numSegmentos - (anguloAjustado / 360) * numSegmentos) % numSegmentos;
            const categoria = categorias[indice];

            mensaje.innerHTML = `Categoría seleccionada: <span class="highlight">${categoria}</span>`;

            const inputCategoria = document.createElement("input");
            inputCategoria.type = "hidden";
            inputCategoria.name = "categoria";
            inputCategoria.value = categoria;
            form.appendChild(inputCategoria);

            setTimeout(() => form.submit(), 2500);
            btn.disabled = false;
        }
    }

    requestAnimationFrame(animar);
});

