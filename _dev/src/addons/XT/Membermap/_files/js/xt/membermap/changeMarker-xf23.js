((window, document) =>  {
	'use strict'

	XF.xtMembermap = XF.Event.newHandler({
		eventNameSpace: 'xtMembermap',

		options: {
			userLat: null,
			userLong: null,
			mapurl: null,
		},

		load: null,
		map: null,
		bounds: null,
		container: null,

		init() {
			if (this.load !== true)
			{
				this.load = true
				this.bounds = new google.maps.LatLngBounds()
				this.xhr = XF.ajax('get', this.options.mapurl, {}, this.onLoad.bind(this))
			}
		},

		onLoad() {
			if (this.options.userLat || this.options.userLong)
			{
				this.initMap()
			}
		},

		initMap() {
			const user = new google.maps.LatLng(this.options.userLat,this.options.userLong)

			const options = {
				center: user,
				zoom: 10,
			}

			this.map = new google.maps.Map(document.getElementById('accountMap'), options)

			this.marker = new google.maps.Marker({
				position: user,
				map: this.map,
				draggable: true,
				title: 'Drag me!',
			})

			google.maps.event.addListener(this.marker, 'dragend', (e) =>
			{
				XF.ajax(
					'POST',
					XF.canonicalizeUrl('index.php?account/xt-membermap'),
					{lat: e.latLng.lat(), lng: e.latLng.lng()}
				)
			})

			this.bounds.extend(user)
		}
	})

	XF.Element.register('xtMembermap', 'XF.xtMembermap')
})(window, document)