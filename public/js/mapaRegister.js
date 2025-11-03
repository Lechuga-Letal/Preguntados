    const mapa = L.map('mapa').setView([-34.6037, -58.3816], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(mapa);

    let marcador;
        mapa.on('click', function(e) { 
            var lat = e.latlng.lat.toFixed(3);
            var lng = e.latlng.lng.toFixed(3);
            if (marcador) mapa.removeLayer(marcador);
            marcador = L.marker([lat, lng]).addTo(mapa);

            var latInput = document.getElementById('latitud');
            var lngInput = document.getElementById('longitud');
            var ubicacionInput = document.getElementById('coordenadas');

            if (latInput) latInput.value = lat;
            if (lngInput) lngInput.value = lng;
            if (ubicacionInput) ubicacionInput.value = `${lat}, ${lng}`;


            fetch('/mapa', {  
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ lat: lat, lng: lng })
            }) 
            .then(respuesta => respuesta.json())
            .then(ubicacion =>{
                var pais= document.getElementById('pais');
                pais.value=ubicacion.pais;

                var provincia= document.getElementById('ciudad');
                provincia.value=ubicacion.provincia;
            })


            })
        