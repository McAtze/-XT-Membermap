! function ($, window, document, _undefined) {
    "use strict";

	XF.xtMembermap = XF.Event.newHandler({
        eventNameSpace: 'xtMembermap',

        options: {
            container: null,
            mapurl: null,
            maptype: null,
            latitude: null,
            longitude: null,
            zoom: null,
            poi: 1,
            cluster: 1,
        },

        load: null,
        container: null,
        dimensions: null,
        markers: null,
        mapData: null,
        markerClusterer: null,
        map: null,
        bounds: null,
        infoWindow: null,

        init: function () {
            if (this.load !== true)
            {
                this.load = true;
                this.bounds = new google.maps.LatLngBounds();
                this.infoWindow = new google.maps.InfoWindow({
                    maxWidth: 450
                });
                this.xhr = XF.ajax('post', this.options.mapurl, {}, XF.proxy(this, 'onLoad'));
            }
        },

        onLoad: function (data) {
            if (data.mapData)
            {
                this.mapData = data.mapData;
                this.initMap();
            }
        },

        addMarker: function(props) { 
            var marker = new google.maps.Marker({
                position: props.coords,
                map: this.map,
                title: props.title,
                icon: props.iconUrl,
            });           

            if(this.options.cluster)
            {
                var markerCluster = new MarkerClusterer(this.map, this.marker, {
                    averageCenter: true,
                    imagePath: props.clusterPath + '/m',
                });
            }

            if (props.coords)
            {
                var loc = new google.maps.LatLng(props.coords);
            }
            else
            {
                var loc = new google.maps.LatLng(this.options.latitude, this.options.longitude);
            }
            this.bounds.extend(loc);

            var self = this;

            (function (marker, props) {
                google.maps.event.addListener(marker, "click", function (e) {
                    self.map.panTo(marker.getPosition())
                    self.infoWindow.setContent(props.content);
                    self.infoWindow.open(self.map, marker);
                });
            })(marker, props);
        },

		initMap: function() {
            var options = {
                zoom: this.options.zoom,
                center: {lat: this.options.latitude, lng: this.options.longitude},
                mapTypeId: this.options.maptype,
                mapTypeControl: true,
                mapTypeControlOptions: {
                    style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
                    mapTypeIds: ["roadmap", "terrain", "hybrid", "satellite"],
                },
            };

            this.map = new google.maps.Map(document.getElementById('membermap'), options);
            
            var self = this;

            if (!this.options.poi)
            {
                var noPoi = [
                    {
                        featureType: "poi",
                        stylers: [
                              { visibility: "off" }
                        ]  
                    }
                ];
                this.map.setOptions({styles: noPoi});
            }

            $.each(this.mapData, function() {
                self.addMarker(this);
            });

           if (this.bounds.getNorthEast().equals(this.bounds.getSouthWest())) {
                var extendPoint1 = new google.maps.LatLng(this.bounds.getNorthEast().lat() + 0.01, this.bounds.getNorthEast().lng() + 0.01);
                var extendPoint2 = new google.maps.LatLng(this.bounds.getNorthEast().lat() - 0.01, this.bounds.getNorthEast().lng() - 0.01);
                this.bounds.extend(extendPoint1);
                this.bounds.extend(extendPoint2);
            }

            //this.map.fitBounds(this.bounds);
            //this.map.panToBounds(this.bounds);

        }
    });

    XF.Element.register('xtMembermap', 'XF.xtMembermap');

}(jQuery, window, document);
