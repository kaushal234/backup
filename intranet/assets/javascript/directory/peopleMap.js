import L from "leaflet";
import Translator from 'bazinga-translator';
import markerClusterGroup from "leaflet.markercluster/dist/leaflet.markercluster";

// If we want add a tooltip on cluster, it is mandatory to re-defined the cluster css
const clusterIconStyle = `
    .leaflet-div-icon.cluster-icon {
        background-color: #3388ff;
        color: white;
        background-clip: padding-box;
        border-radius: 20px;
        text-align: center;
        font-weight: bold;
        font-size: 14px;
        line-height: 35px;
        border: 2px solid white;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        background-color: rgba(240,194,12,.6);
    }
`;

const styleSheet = document.createElement("style");
styleSheet.innerText = clusterIconStyle;
document.head.appendChild(styleSheet);

let map = L.map('peopleMap', {
    scrollWheelZoom: false,
    worldCopyJump: true,
}).setView([0, 0], 2);

L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager_labels_under/{z}/{x}/{y}{r}.png', {
    maxZoom: 10,
    noWrap: true,
}).addTo(map);

// Clusters Initialisation
let markers = L.markerClusterGroup({
    iconCreateFunction: function(cluster) {
        let count = cluster.getChildCount();
        let locations = {};
        let names = [];

        cluster.getAllChildMarkers().forEach(function(marker) {
            if (marker.options && marker.options.person) {
                const person = marker.options.person;
                let locationLabel = 'Unknown';

                const premiseHasCoords = person.premise?.latitude != null && person.premise?.longitude != null;

                if (premiseHasCoords) {
                    locationLabel = `Premise: ${person.premise.name}`;
                } else if (person.airport) {
                    const code = person.airport.code || '';
                    const city = person.airport.cityName || '';
                    if (code && city) {
                        locationLabel = `Airport: ${code} - ${city}`;
                    } else if (code) {
                        locationLabel = `Airport: ${code}`;
                    } else {
                        locationLabel = `Airport: Unknown`;
                    }
                }

                if (locationLabel === "Unknown") {
                    console.warn('Unknown location for person:', person);
                }

                locations[locationLabel] = true;
                names.push(`${person.firstname} ${person.lastname}`);
            }
        });

        // Cluster icon
        let clusterIcon = L.divIcon({
            html: '<div class="cluster-icon"><strong>' + count + '</strong><br></div>',
            className: 'leaflet-div-icon cluster-icon',
            iconSize: L.point(40, 40)
        });

        // Tooltip
        cluster.on('mouseover', function () {
            let tooltipContent =
                '<strong>' + Object.keys(locations).length + ' location(s)</strong><br>' +
                '<small>' + Object.keys(locations).join('<br>') + '</small>';
            cluster.bindTooltip(tooltipContent, { offset: L.point(10, 0) }).openTooltip();
        });

        cluster.on('mouseout', function () {
            cluster.closeTooltip();
        });



        return clusterIcon;
    }
});




// Marker for people
for (let people of globalPeoples) {
    let latitude = -48.8683333;
    let longitude = -123.385;

    let tooltipContent = `${people.firstname} ${people.lastname}<br>`;

    if (
        people.premise?.latitude !== null &&
        people.premise?.latitude !== undefined &&
        people.premise?.longitude !== null &&
        people.premise?.longitude !== undefined
    ) {
        latitude = people.premise.latitude;
        longitude = people.premise.longitude;
        tooltipContent += `Premise: ${people.premise.name}`;
    } else if (people.airport) {
        latitude = people.airport.latitude;
        longitude = people.airport.longitude;
        tooltipContent += `Airport: ${people.airport.code} - ${people.airport.cityName}`;
    }

    const marker = L.marker(new L.LatLng(latitude, longitude), {
        person: people
    });

    marker.bindTooltip(tooltipContent);
    markers.addLayer(marker);
}


map.addLayer(markers);
