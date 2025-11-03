let coordStr = "{{usuario.coordenadas}}"; 

let defaultLat = -34.6037;
let defaultLng = -58.3816;

let lat, lng;

if (coordStr && coordStr.includes(",")) {
    [lat, lng] = coordStr.split(",").map(c => parseFloat(c.trim()));
} else {
    lat = defaultLat;
    lng = defaultLng;
}

let mapa = L.map('mapa').setView([lat, lng], 12);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(mapa);

L.marker([lat, lng]).addTo(mapa).bindPopup("{{usuario.usuario}}");
