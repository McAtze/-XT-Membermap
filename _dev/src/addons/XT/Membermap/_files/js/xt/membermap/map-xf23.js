((window, document) => {
	'use strict'

	XF.xtMembermap = XF.Event.newHandler({
		eventNameSpace: 'xtMembermap',

		options: {
			container: null,
			mapurl: null,
			maptype: null,
			mapid: null,
			latitude: null,
			longitude: null,
			center: 'default',
			zoom: null,
			poi: 1,
			cluster: 1,
			clusterPath: null,
			maxZoom: null,
			minClusterSize: null,
		},

		load: null,
		container: null,
		dimensions: null,
		markers: null,
		mapData: null,
		markerCluster: null,
		map: null,
		bounds: null,
		infoWindow: null,
		userDataUrl: null,

		init() {
			if (this.load !== true)
			{
				this.load = true;
				this.bounds = new google.maps.LatLngBounds()
				this.infoWindow = new google.maps.InfoWindow({
					maxWidth: 450
				})

				this.xhr = XF.ajax('post', this.options.mapurl, {}, this.onLoad.bind(this))
			}
			this.markers = [];
		},

		onLoad(data) {
			if (data.mapData)
			{
				this.mapData = data.mapData;
				this.initMap()
			}
		},

		addMarker(props, userid) { 
			const marker = new google.maps.Marker({
				position: props.coords,
				map: this.map,
				title: props.title,
				icon: props.iconUrl,
			})

			let loc
			if (props.coords)
			{
				loc = new google.maps.LatLng(props.coords)
			}
			else
			{
				loc = new google.maps.LatLng(this.options.latitude, this.options.longitude)
			}

			XF.extendObject(this.bounds, loc);

			google.maps.event.addListener(marker, 'click', (e) =>
			{
				XF.ajax('post', props.infoUrl, {}, (data) => {
					this.map.panTo(marker.getPosition())
					this.infoWindow.setContent(data.html.content)
					this.infoWindow.open(this.map, marker)
				})
			})
			this.markers.push(marker)
		},

		initMap() {
			const latitude = parseFloat(this.options.latitude)
			const longitude = parseFloat(this.options.longitude)
			const options = {
				zoom: this.options.zoom,
				center: {
					lat: latitude,
					lng: longitude
				},
				mapId: this.options.mapid,
				mapTypeId: this.options.maptype,
				mapTypeControl: true,
				mapTypeControlOptions: {
					style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
					mapTypeIds: ['roadmap', 'terrain', 'hybrid', 'satellite'],
				},
			}

			this.map = new google.maps.Map(document.getElementById('membermap'), options)
			
			const self = this

			if (!this.options.poi)
			{
				const noPoi = [
					{
						featureType: 'poi',
						stylers: [
							  { visibility: 'off' }
						]  
					}
				];
				this.map.setOptions({styles: noPoi});
			}

			for (const userId in this.mapData)
			{
				this.addMarker(this.mapData[userId], userId)
			}

			if (this.options.cluster) {
				this.markerCluster = new MarkerClusterer(this.map, this.markers, {
					averageCenter: true,
					maxZoom: self.options.maxZoom,
					minimumClusterSize: self.options.minClusterSize,
					imagePath: self.options.clusterPath + '/m',
				});
			}

			this.oms = new OverlappingMarkerSpiderfier(this.map, {keepSpiderfied : true});
			for (let i = 0, len = this.markers.length; i < len; i ++){
				this.oms.addMarker(this.markers[i]);
			}

		   if (this.bounds.getNorthEast().equals(this.bounds.getSouthWest())) {
				const northEastLat = bounds.getNorthEast().lat()
				const northEastLng = bounds.getNorthEast().lat()
				const extendPoint1 = new google.maps.LatLng(northEastLat + 0.01, northEastLng + 0.01)
				const extendPoint2 = new google.maps.LatLng(northEastLat - 0.01, northEastLng - 0.01)
				this.bounds.extend(extendPoint1)
				this.bounds.extend(extendPoint2)
			}

			const oc = this.options.center

			if (oc === 'markers')
			{
				this.map.fitBounds(this.bounds)
				this.map.panToBounds(this.bounds)
			}
		}
	});

	XF.Element.register('xtMembermap', 'XF.xtMembermap')
})(window, document)
