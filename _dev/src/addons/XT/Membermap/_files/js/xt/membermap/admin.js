!function($, window, document, _undefined)
{
	"use strict";

	XF.Element.extend("asset-upload", {
		__backup: {
			"ajaxResponse": "_afterAjaxResponseXtMapIcon"
		},
		ajaxResponse: function (data) {
			this._afterAjaxResponseXtMapIcon(data);
			if (data.path) {
				this.$path.css('background-image', 'url(' + data.path + ')');
			}
		}
	});

	XF.xtAssetImage = XF.Element.newHandler({

		oldval: null,

		init: function () {
			this.$target.css('background-image', 'url(' + this.$target.val() + ')');
			var self = this;
			this.$target.on('blur', XF.proxy(this, 'blur'))
				.on('focus', XF.proxy(this, 'focus'));;
		},

		focus: function (e) {
			this.$target.css('background-image', 'url()');
			this.oldval = this.$target.val();
		},

		blur: function (e) {
			this.$target.css('background-image', 'url(' + this.$target.val() + ')');
		},

	});

	XF.xtSetIcon = XF.Event.newHandler({
		eventType: 'click',
		eventNameSpace: 'xtSetIcon',

		$container: null,

		init: function() {
			this.$container = $('.xt--mm-imagepreview');
		},

		click: function () {
			this.$container.val(this.$target.attr('src'));
			this.$container.css('background-image', 'url(' + this.$target.attr('src') + ')');
		}

	});

	XF.Element.register('xtAssetImage', 'XF.xtAssetImage');
	XF.Event.register('click', 'xtSetIcon', 'XF.xtSetIcon');


}(jQuery, window, document);