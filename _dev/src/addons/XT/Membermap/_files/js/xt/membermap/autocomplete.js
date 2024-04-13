! function ($, window, document, _undefined) {
    "use strict";

	XF.xtAutocomplete = XF.Event.newHandler({
        eventNameSpace: 'xtAutocomplete',

        options: {
			types: null,
			componentRestrictions: null
		},

		autocomplete: null,

        init: function()
		{
			let options = {};
			if (this.options.types)
			{
				options.types = this.options.types;
			}
			if (this.options.componentRestrictions)
			{
				options.componentRestrictions = this.options.componentRestrictions;
			}

			this.autocomplete = new google.maps.places.Autocomplete(this.$target[0], options);
		}
    });

    XF.Element.register('xtAutocomplete', 'XF.xtAutocomplete');

}(jQuery, window, document);