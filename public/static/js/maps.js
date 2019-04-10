(function ($) {
    "use strict";
    var markerIcon = {
        anchor: new google.maps.Point(12, 0),
        url: 'images/marker.png',
        labelOrigin: new google.maps.Point(25, 20)
    }
    function mainMap() {
        function locationData(locationURL, locationImg, locationTitle, locationAddress, locationPrice, locationStarRating) {
            return ('<div class="map-popup-wrap"><div class="map-popup"><div class="infoBox-close"><i class="far fa-times"></i></div><a href="' + locationURL + '" class="listing-img-content fl-wrap"><img src="' + locationImg + '" alt=""><span class="map-popup-location-price"><strong>Awg/Night</strong>' + locationPrice + '</span></a> <div class="listing-content fl-wrap"><div class="card-popup-raining map-card-rainting" data-staRrating="' + locationStarRating + '"></div><div class="listing-title fl-wrap"><h4><a href=' + locationURL + '>' + locationTitle + '</a></h4><span class="map-popup-location-info"><i class="fas fa-map-marker-alt"></i>' + locationAddress + '</span></div></div></div></div>')
        }
	    //  Map Infoboxes ------------------
        var locations = [
            [locationData('listing-single2.html', 'images/gal/8.jpg', 'Premium Plaza Hotel', "1327 Intervale Ave, Bronx, NY, USA", "$ 320", "5"), 40.72956781, -73.99726866, 0, markerIcon],
            [locationData('listing-single.html', 'images/gal/4.jpg', 'Grand Hero Palace', "W 85th St, NY, USA ", "$ 120", "4"), 40.76221766, -73.96511769, 1, markerIcon],
            [locationData('listing-single.html', 'images/gal/6.jpg', 'Park Central', "40 Journal Square Plaza, NJ,  USA", "$ 50", "5"), 40.88496706, -73.88191222, 2, markerIcon],
            [locationData('listing-single.html', 'images/gal/2.jpg', 'Holiday Home', "75 Prince St,  NY, USA", "$ 50", "3"), 40.72228267, -73.99246214, 3, markerIcon],
            [locationData('listing-single.html', 'images/gal/3.jpg', 'Gold Plaza Hotel', "34-42 Montgomery St, New York, NY", "$ 210", "5"), 40.94982541, -73.84357452, 4, markerIcon],
            [locationData('listing-single.html', 'images/gal/5.jpg', 'Moonlight Hotel', "70 Bright St, Jersey City, NJ", "$ 105", "4"), 40.90261483, -74.15737152, 5, markerIcon],
            [locationData('listing-single.html', 'images/gal/1.jpg', 'Zebra Premium Hotel', "123 School St. Lynchburg, NY ", "$ 115", "3"), 40.79145927, -74.08252716, 6, markerIcon],
            [locationData('listing-single2.html', 'images/gal/7.jpg', 'Fancy Hotel', "Mt Carmel Pl, New York, NY", "$70", "5"), 40.58423508, -73.96099091, 7, markerIcon],
            [locationData('listing-single2.html', 'images/gal/9.jpg', 'Luxary Hotel-Spa', "1-30 Hunters Point Ave, Long Island City, NY", "$320", "5"), 40.58110616, -73.97678375, 8, markerIcon],
            [locationData('listing-single3.html', 'images/gal/4.jpg', 'NY Plaza Hotel', "726-1728 2nd Ave, New York, NY", "$ 120", "5"), 40.73112881, -74.07897948, 9, markerIcon],
            [locationData('listing-single3.html', 'images/gal/5.jpg', 'Apartment Hotel-Spa', "9443 Fairview Ave, North Bergen, NJ", "$ 210", "4"), 40.67386831, -74.10438536, 10, markerIcon],
        ];
	    //   Map Infoboxes end ------------------
        var map = new google.maps.Map(document.getElementById('map-main'), {
            zoom: 10,
            scrollwheel: false,
            center: new google.maps.LatLng(40.8, -73.90),
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            zoomControl: false,
            mapTypeControl: false,
            scaleControl: false,
            panControl: false,
            fullscreenControl: true,
            navigationControl: false,
            streetViewControl: false,
            animation: google.maps.Animation.BOUNCE,
            gestureHandling: 'cooperative',
            styles: [{
                    "featureType": "poi.attraction",
                    "stylers": [{
                        "visibility": "off"
                    }]
                },
                {
                    "featureType": "poi.business",
                    "stylers": [{
                        "visibility": "off"
                    }]
                },
                {
                    "featureType": "poi.medical",
                    "stylers": [{
                        "visibility": "off"
                    }]
                },
                {
                    "featureType": "poi.place_of_worship",
                    "stylers": [{
                        "visibility": "off"
                    }]
                },
                {
                    "featureType": "poi.school",
                    "stylers": [{
                        "visibility": "off"
                    }]
                },
                {
                    "featureType": "transit.station.bus",
                    "stylers": [{
                        "visibility": "off"
                    }]

                }
            ]
        });
        var boxText = document.createElement("div");
        boxText.className = 'map-box'
        var currentInfobox;
        var boxOptions = {
            content: boxText,
            disableAutoPan: true,
            alignBottom: true,
            maxWidth: 0,
            pixelOffset: new google.maps.Size(-137, -25),
            zIndex: null,
            boxStyle: {
                width: "260px"
            },
            closeBoxMargin: "0",
            closeBoxURL: "",
            infoBoxClearance: new google.maps.Size(1, 1),
            isHidden: false,
            pane: "floatPane",
            enableEventPropagation: false,
        };

        var markerCluster, marker, i;
        var allMarkers = [];
        var clusterStyles = [{
            textColor: 'white',
            url: '',
            height: 50,
            width: 50
        }];
        for (i = 0; i < locations.length; i++) {
            var labels = '123456789';
            marker = new google.maps.Marker({
                position: new google.maps.LatLng(locations[i][1], locations[i][2]),
                icon: locations[i][4],
                id: i,
                label: {
                    text: "" + (i + 1),
                    color: "#a8e93a",
                    fontSize: "11px",
                    fontWeight: "bold",
                },
            });
            allMarkers.push(marker);
            var ib = new InfoBox();
            google.maps.event.addListener(ib, "domready", function () {
                cardRaining()
            });
            google.maps.event.addListener(marker, 'click', (function (marker, i) {
                return function () {
                    ib.setOptions(boxOptions);
                    boxText.innerHTML = locations[i][0];
                    ib.close();
                    ib.open(map, marker);
                    currentInfobox = marker.id;
                    var latLng = new google.maps.LatLng(locations[i][1], locations[i][2]);
                    map.panTo(latLng);
                    map.panBy(0, -50);
                    google.maps.event.addListener(ib, 'domready', function () {
                        $('.infoBox-close').click(function (e) {
                            e.preventDefault();
                            ib.close();
                        });
                    });
                }
            })(marker, i));
        }
        var options2 = {
            imagePath: 'images/',
            styles: clusterStyles,
            minClusterSize: 2
        };
        markerCluster = new MarkerClusterer(map, allMarkers, options2);
        google.maps.event.addDomListener(window, "resize", function () {
            var center = map.getCenter();
            google.maps.event.trigger(map, "resize");
            map.setCenter(center);
        });
        if ($(".controls-mapwn").length) {
            var input = document.getElementById('pac-input');
            var searchBox = new google.maps.places.SearchBox(input);
            map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
            map.addListener('bounds_changed', function () {
                searchBox.setBounds(map.getBounds());
            });
            var markers = [];
            searchBox.addListener('places_changed', function () {
                var places = searchBox.getPlaces();

                if (places.length == 0) {
                    return;
                }
                markers.forEach(function (marker) {
                    marker.setMap(null);
                });
                markers = [];

                var bounds = new google.maps.LatLngBounds();
                places.forEach(function (place) {
                    if (!place.geometry) {
                        console.log("Returned place contains no geometry");
                        return;
                    }
                    var icon = {
                        url: place.icon,
                        size: new google.maps.Size(71, 71),
                        origin: new google.maps.Point(0, 0),
                        anchor: new google.maps.Point(17, 34),
                        scaledSize: new google.maps.Size(25, 25)
                    };
                    markers.push(new google.maps.Marker({
                        map: map,
                        icon: icon,
                        title: place.name,
                        position: place.geometry.location
                    }));

                    if (place.geometry.viewport) {
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                });
                map.fitBounds(bounds);
            });
        }
        $('.map-item').on("click", function (e) {
            e.preventDefault();
            map.setZoom(15);
            var index = currentInfobox;
            var marker_index = parseInt($(this).attr('href').split('#')[1], 10);
            google.maps.event.trigger(allMarkers[marker_index], "click");
            if ($(window).width() > 1064) {
                if ($(".map-container").hasClass("fw-map")) {
                    $('html, body').animate({
                        scrollTop: $(".map-container").offset().top + "-110px"
                    }, 1000)
                    return false;
                }
            }
        });
        $('.nextmap-nav').on("click", function (e) {
            e.preventDefault();
            map.setZoom(15);
            var index = currentInfobox;
            if (index + 1 < allMarkers.length) {
                google.maps.event.trigger(allMarkers[index + 1], 'click');
            } else {
                google.maps.event.trigger(allMarkers[0], 'click');
            }
        });
        $('.prevmap-nav').on("click", function (e) {
            e.preventDefault();
            map.setZoom(15);
            if (typeof (currentInfobox) == "undefined") {
                google.maps.event.trigger(allMarkers[allMarkers.length - 1], 'click');
            } else {
                var index = currentInfobox;
                if (index - 1 < 0) {
                    google.maps.event.trigger(allMarkers[allMarkers.length - 1], 'click');
                } else {
                    google.maps.event.trigger(allMarkers[index - 1], 'click');
                }
            }
        });
        var zoomControlDiv = document.createElement('div');
        var zoomControl = new ZoomControl(zoomControlDiv, map);
        function ZoomControl(controlDiv, map) {
            zoomControlDiv.index = 1;
            map.controls[google.maps.ControlPosition.RIGHT_CENTER].push(zoomControlDiv);
            controlDiv.style.padding = '5px';
            var controlWrapper = document.createElement('div');
            controlDiv.appendChild(controlWrapper);
            var zoomInButton = document.createElement('div');
            zoomInButton.className = "mapzoom-in";
            controlWrapper.appendChild(zoomInButton);
            var zoomOutButton = document.createElement('div');
            zoomOutButton.className = "mapzoom-out";
            controlWrapper.appendChild(zoomOutButton);
            google.maps.event.addDomListener(zoomInButton, 'click', function () {
                map.setZoom(map.getZoom() + 1);
            });
            google.maps.event.addDomListener(zoomOutButton, 'click', function () {
                map.setZoom(map.getZoom() - 1);
            });
        }
    }
    var map = document.getElementById('map-main');
    if (typeof (map) != 'undefined' && map != null) {
        google.maps.event.addDomListener(window, 'load', mainMap);
    }
})(this.jQuery);