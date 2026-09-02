import L from "leaflet/dist/leaflet";

let map = L.map('map', { scrollWheelZoom: false }).setView([globalAirport.latitude, globalAirport.longitude], 2);
L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager_labels_under/{z}/{x}/{y}{r}.png', {
    maxZoom: 10
}).addTo(map);

let marker = L.marker(new L.LatLng(globalAirport.latitude, globalAirport.longitude));
marker.bindTooltip(`        
    <div class="p-2">
        <h4>${globalAirport.code}</h4>
        <h5>${globalAirport.cityName}</h5>
    </div>
</div>
`).addTo(map);