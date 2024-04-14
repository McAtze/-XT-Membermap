((window, document) => {
    'use strict'

	XF.Element.extend('asset-upload', {
		__backup: {
			'ajaxResponse': '_afterAjaxResponseXtMapIcon'
		},
		ajaxResponse(data) {
			this._afterAjaxResponseXtMapIcon(data)
			if (data.path) {
				this.path.style.backgroundImage = 'url(' + data.path + ')'
			}
		}
	})
	
	XF.xtAssetImage = XF.Element.newHandler({
		oldval: null,

		init() {
			this.target.style.backgroundImage = 'url(' + this.target.value + ')'
			XF.on(this.target, 'blur', this.blur.bind(this))
			XF.on(this.target, 'focus', this.focus.bind(this))
		},

		focus(e) {
			this.target.style.backgroundImage ='none'
			this.oldval = this.target.value
		},

		blur(e) {
			this.target.style.backgroundImage = 'url(' + this.target.value + ')'
		},
	})

	XF.xtSetIcon = XF.Event.newHandler({
        eventType: 'click',
      	eventNameSpace: 'xtSetIcon',

		container: null,

		init() {
			this.container = document.querySelector('.xt--mm-imagepreview')
		},

        click() {
			this.container.value = this.target.getAttribute('src')
			this.container.style.backgroundImage = 'url(' + this.container.value + ')'
        }
	})

	XF.Element.register('xtAssetImage', 'XF.xtAssetImage');
	XF.Event.register('click', 'xtSetIcon', 'XF.xtSetIcon');
})(window, document)