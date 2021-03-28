!function($, window, document, _undefined)
{
	"use strict";

	XT.Membermap.initMap = function()
	{
		jQuery.extend(true, XT.Membermap.mapOptions, {
			zoom: 2,
			//mapTypeId: google.maps.MapTypeId[XT.Membermap.config.mapTypeId]
		});

		if (XT.Membermap.config.lat !== null)
		{
			jQuery.extend(true, XT.Membermap.mapOptions, {
				center: new google.maps.LatLng(XT.Membermap.config.lat, XT.Membermap.config.long)
			});
		}

		var map = new google.maps.Map(document.getElementById('membermap'), XT.Membermap.mapOptions),
			markers = [],
			lastopen = null;

		$('#markers > div').each(function ()
		{
			var self = $(this),
				infowindow = new google.maps.InfoWindow({content: self.html()}),
				markerOptions = {
					title: self.attr('title'),
					shadow: new google.maps.MarkerImage('https://chart.apis.google.com/chart?chst=d_map_pin_shadow', new google.maps.Size(40, 37), new google.maps.Point(0, 0), new google.maps.Point(12, 35)),
					position: new google.maps.LatLng(self.data('latitude'), self.data('longitude')),
					animation: google.maps.Animation.DROP,
					draggable: (self.data('move') == '1'),
					icon: XT.Membermap.config.basePath + self.data('icon') + '.png'
				}
			;

			if (!XT.Membermap.config.enableCluster)
			{
				markerOptions.map = map;
			}

			var marker = new google.maps.Marker(markerOptions);

			google.maps.event.addListener(marker, 'mouseover', function ()
			{
				// set popups on hover
				if (lastopen != infowindow)
				{
					if (lastopen)
					{
						lastopen.close();
					}

					infowindow.open(map, marker);
					lastopen = infowindow;
				}
			});
			google.maps.event.addListener(marker, 'click', function ()
			{
				// set popups on hover
				if (lastopen != infowindow)
				{
					if (lastopen)
					{
						lastopen.close();
					}

					infowindow.open(map, marker);
					lastopen = infowindow;
				}
			});
			google.maps.event.addListener(infowindow, 'closeclick', function ()
			{
				// avoid bugs
				lastopen = null;
			});

			markers.push(marker);
		});

		if (XT.Membermap.config.enableCluster)
		{
			setTimeout(function ()
			{
				var markerCluster = new MarkerClusterer(map, markers,
					{
						maxZoom: 7,
						imagePath: XT.Membermap.config.basePath + 'cluster/m'
					});
			}, 1000);
		}
	};
}
(jQuery, window, document);