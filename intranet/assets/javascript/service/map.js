import L from "leaflet/dist/leaflet";
import markerClusterGroup from "leaflet.markercluster/dist/leaflet.markercluster";

export default function map () {
    var map = L.map('map', {
        scrollWheelZoom: false,
        worldCopyJump: true,
    }).setView([0, 0], 2);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager_labels_under/{z}/{x}/{y}{r}.png', {
        maxZoom: 10,
        noWrap: true,
    }).addTo(map);

    var markers = L.markerClusterGroup();

    for (let customerServiceRecord of globalCsrs) {
        let airportCode = ''
        let airportName = ''
        let latitude = 0
        let longitude = 0

        if (customerServiceRecord.airport) {
            airportCode = customerServiceRecord.airport.code
            airportName = customerServiceRecord.airport.name
            latitude = customerServiceRecord.airport.latitude
            longitude = customerServiceRecord.airport.longitude
        }

        let marker = L.marker(new L.LatLng(latitude, longitude));
        marker.bindTooltip(`        
            <div class="p-2">
                <h4>CSR#${customerServiceRecord.id}</h4>
                <h5>${customerServiceRecord.equipmentRecord.serialNumber} - ${customerServiceRecord.equipmentRecord.model}</h5>
                <h6>
                    ${airportCode} ${airportName}
                </h6>
                <p>${customerServiceRecord.title}</p>
                </div>
            </div>
        </div>
        `);

        if (customerServiceRecord.airport) {
            marker.on('click', function () {
                const airportFilter = document.getElementById('planner_filter_airport');

                if (airportFilter) {
                    airportFilter.value = customerServiceRecord.airport['@id'];
                }
            })
        }

        markers.addLayer(marker);
    }

    map.on('click', function () {
        const airportFilter = document.getElementById('planner_filter_airport');

        if (airportFilter) {
            airportFilter.value = null;
        }
    })

    map.addLayer(markers);
}