/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */
(function(exports) {
	/**
	 * The Global OpenEyes namespace
	 * @namespace OpenEyes
	 */
	exports.OpenEyes = {};
}(this));
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */
(function(exports) {

	/**
	 * OpenEyes Util module
	 * @namespace OpenEyes.Util
	 * @memberOf OpenEyes
	 */
	var Util = {};

	/**
	 * Extend an objects' prototype with another objects' prototype.
	 * @method
	 * @param {Function} parent The parent constructor.
	 * @param {Function} child  The child constructor.
	 * @example
	 * function Parent() {}
	 * Parent.prototype.method = function() {};
	 *
	 * function Child() {}
	 * Util.inherits(Parent, Child);
	 *
	 * var child = new Child();
	 * child.method();
	 * @returns {Function} The child constructor.
	 */
	Util.inherits = function(parent, child) {
		child._super = parent;
		child.prototype = Object.create(parent.prototype);
		child.prototype.constructor = child;
		return child;
	};

	exports.Util = Util;

}(this.OpenEyes));
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */
(function(exports) {

	/**
	 * Emitter
	 * @class OpenEyes.Util.EventEmitter
	 * @name OpenEyes.Util.EventEmitter
	 * @memberOf OpenEyes
	 * @tutorial emitter
	 */
	function Emitter() {
		this.events = {};
	}

	/**
	 * Adds a new handler (function) for a given event.
	 * @name OpenEyes.Util.EventEmitter#on
	 * @method
	 * @param {string} type - The event type.
	 * @param {function} handler - The callback handler for the event type.
	 * @returns {this}
	 */
	Emitter.prototype.on = function(type, handler) {

		var events = this.events;

		if (!events[type]) {
			events[type] = [];
		}

		events[type].push(handler);

		return this;
	};

	/**
	 * Remove a specific handler, or all handlers for a given event.
	 * @name OpenEyes.Util.EventEmitter#off
	 * @method
	 * @param {string} type - The event type.
	 * @param {function} [handler] - The callback handler to remove for the given event (optional)
	 * @returns {this}
	 */
	Emitter.prototype.off = function(type, handler) {

		var events = this.events[type];

		if (events) {

			if (!handler) {
				// Remove all event handlers
				events = [];
			} else {
				// Remove a specific event handler
				events.splice(events.indexOf(handler), 1);
			}

			// If this event handler group is empty then remove it
			if (!events.length) {
				delete this.events[type];
			}
		}

		return this;
	};

	/**
	 * Executes all handlers for a given event.
	 * @name OpenEyes.Util.EventEmitter#emit
	 * @method
	 * @param {string} type - The event type.
	 * @param {mixed} data - Event data to be passed to all the event handlers.
	 * @returns {this}
	 */
	Emitter.prototype.emit = function(type, data) {

		var event;
		var events = (this.events[type] || []).slice();

		// First, lets execute all the event handlers
		if (events.length) {
			while ((event = events.shift())) {
				event.call(this, data);
			}
		}

		// Now try trigger a callback handler
		return this.trigger(type, data);
	};

	/**
	 * Binds all methods of this object to the object itself.
	 * @name OpenEyes.Util.EventEmitter#bindAll
	 * @method
	 * @private
	 * @param {boolean} [inherited=false] - Bind to inherited methods?
	 */
	Emitter.prototype.bindAll = function(inherited) {
		OpenEyes.Util.bindAll(this, inherited);
	};

	/**
	 * Execute a callback handler for a given event. Callback handlers are stored
	 * within the 'options' property of this object, and have the format of 'onEventName'.
	 * @name OpenEyes.Util.EventEmitter#trigger
	 * @method
	 * @param {string} type - The event type.
	 * @param {mixed} data - Event data to be passed to all the event handlers.
	 * @returns {this}
	 */
	Emitter.prototype.trigger = function(type, data) {

		if (!this.options) {
			return;
		}

		var name = 'on' + type.slice(0,1).toUpperCase() + type.slice(1);
		var handler = this.options[name];

		if (handler) {
			handler.call(this, data);
		}

		return this;
	};

	exports.EventEmitter = Emitter;

}(this.OpenEyes.Util));
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */
(function(exports) {
	/**
	 * OpenEyes UI namespace
	 * @namespace OpenEyes.UI
	 * @memberOf OpenEyes
	 */
	exports.UI = {};
}(this.OpenEyes));
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */
(function(exports) {

	var NAMESPACE = 'sticky';
	var win = $(window);

	/**
	 * StickyElement constructor.
	 * @name OpenEyes.UI.StickyElement
	 * @constructor
	 * @param {Mixed}  element Element can be a selector string, a DOM element or a
	 * jQuery instance.
	 * @param {Object} options The custom options for this instance.
	 */
	function StickyElement(element, options) {

		this.element = $(element);
		if (!this.element.length) return;

		this.options = $.extend({}, StickyElement._defaultOptions, options);
		this.elementOffset = this.element.offset();
		this.wrapperHeight = this.options.wrapperHeight(this);

		this.wrapElement();
		this.bindEvents();
	}

	/**
	 * StickyElement default options.
	 * @name OpenEyes.UI.StickyElement#_defaultOptions
	 * @type {Object}
	 */
	StickyElement._defaultOptions = {
		wrapperClass: 'sticky-wrapper',
		stuckClass: 'stuck',
		offset: 0,
		debug: false,
		wrapperHeight: function(instance) {
			return instance.element.height();
		},
		enableHandler: function(instance) {
			instance.enable();
		},
		disableHandler: function(instance) {
			instance.disable();
		}
	};

	/**
	 * Wraps the element in a container div.
	 * @name OpenEyes.UI.StickyElement#wrapElement
	 * @private
	 * @method
	 */
	StickyElement.prototype.wrapElement = function() {
		this.element.wrap($('<div />', {
			'class': this.options.wrapperClass
		}));
		this.wrapper = this.element.parent();
	};

	/**
	 * Binds DOM events to method handlers.
	 * @name OpenEyes.UI.StickyElement#bindEvents
	 * @private
	 * @method
	 */
	StickyElement.prototype.bindEvents = function() {
		win.on('scroll.' + NAMESPACE, this.onWindowScroll.bind(this));
	};

	/**
	 * Make the element sticky.
	 * @name OpenEyes.UI.StickyElement#enable
	 * @method
	 */
	StickyElement.prototype.enable = function() {
		this.wrapper.height(this.wrapperHeight);
		this.element.addClass(this.options.stuckClass);
	};

	/**
	 * Unstick the element.
	 * @name OpenEyes.UI.StickyElement#disable
	 * @method
	 */
	StickyElement.prototype.disable = function() {
		this.wrapper.height('auto');
		this.element.removeClass(this.options.stuckClass);
	};

	/**
	 * Window scroll handler. This method compares the offset of the element to the
	 * window scroll position and determines if the element should be sticky or not.
	 * @name OpenEyes.UI.StickyElement#onWindowScroll
	 * @method
	 * @private
	 */
	StickyElement.prototype.onWindowScroll = function() {

		var offset = $.isFunction(this.options.offset) ? this.options.offset() : this.options.offset;
		var winTop = win.scrollTop();
		var elementTop = this.elementOffset.top + offset;

		// [OE-4014] This accounts for "over-scroll" that occurs when using a trackpad/touch
		// device. Offsets are calculated relative to the document, and as we're using
		// window.scrollTop in our calculations, we need to ensure the scroll position
		// value never exceeds the height of the document.
		var scrollHeight = $(document).height() - win.height();
		if (winTop > scrollHeight) {
			winTop -= (winTop - scrollHeight);
		}

		if (winTop >= elementTop) {
			this.options.enableHandler(this);
		} else {
			this.options.disableHandler(this);
		}
	};

	exports.StickyElement = StickyElement;

	exports.StickyElements = {
		refresh: function() {
			win.trigger('scroll.' + NAMESPACE);
		}
	};

}(this.OpenEyes.UI));
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

(function(exports, Util, EventEmitter) {

	'use strict';

	function Tooltip(options) {
		EventEmitter.call(this);
		this.options = $.extend(true, {}, Tooltip._defaultOptions, options);
		this.create();
	}

	Tooltip.prototype = Object.create(EventEmitter.prototype);

	Tooltip._defaultOptions = {
		className: 'quicklook tooltip',
		content: '',
		offset: {
			x: 0,
			y: 0
		},
		viewPortOffset: {
			x: 0,
			y: 0
		}
	};

	Tooltip.prototype.create = function() {

		this.container = $('<div />', {
			'class': this.options.className
		}).appendTo(document.body);

		this.setContent(this.options.content);
	};

	Tooltip.prototype.setContent = function(content) {
		this.container.html(content);
	};

	Tooltip.prototype.show = function(x, y) {
		this.container.css(this.getPosition(x, y));
	};

	Tooltip.prototype.getPosition = function(x, y) {

		this.container.show();

		var opts = this.options;

		var viewPortX = x - $(window).scrollLeft();
		var viewPortY = y - $(window).scrollTop();

		var viewPortWidth = $(window).width();
		var viewPortHeight = $(window).height();

		var width = this.container.outerWidth();
		var height = this.container.outerHeight();

		// Off-screen to the right?
		if (width + viewPortX + opts.offset.x + opts.viewPortOffset.x >= viewPortWidth) {
			x -= (width + opts.offset.x);
		} else {
			x += opts.offset.x;
		}

		// Off-screen to the bottom?
		if (height + viewPortY + opts.offset.y + opts.viewPortOffset.y >= viewPortHeight) {
			y -= (height + opts.offset.y);
		} else {
			y += opts.offset.y;
		}

		return { left: x, top: y };
	};

	Tooltip.prototype.hide = function() {
		this.container.hide();
	};

	Tooltip.prototype.destroy = function() {
		this.container.empty().remove();
	};

	exports.Tooltip = Tooltip;

}(OpenEyes.UI, OpenEyes.Util, EventEmitter2));
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

(function(exports, Util, EventEmitter) {

	'use strict';

	// Set the jQuery UI Dialog default options.
	$.extend($.ui.dialog.prototype.options, {
		dialogClass: 'dialog',
		show: 'fade'
	});

	/**
	 * Dialog constructor.
	 * @constructor
	 * @name OpenEyes.UI.Dialog
	 * @tutorial dialog
	 * @memberOf OpenEyes.UI
	 * @extends OpenEyes.Util.EventEmitter
	 * @example
	 * var dialog = new OpenEyes.UI.Dialog({
	 *	title: 'Title here',
	 *	content: 'Here is some content.'
	 * });
	 * dialog.on('open', function() {
	 *	console.log('The dialog is now open');
	 * });
	 * dialog.open();
	 */
	function Dialog(options) {

		EventEmitter.call(this);

		this.options = $.extend(true, {}, Dialog._defaultOptions, options);

		this.create();
		this.bindEvents();

		// Load dialog content in an iframe.
		if (this.options.iframe) {
			this.loadIframeContent();
		}
		// Load dialog content via an AJAX request.
		else if (this.options.url) {
			this.loadContent();
		}
	}

	Util.inherits(EventEmitter, Dialog);

	/**
	 * The default dialog options. Custom options will be merged with these.
	 * @name OpenEyes.UI.Dialog#_defaultOptions
	 * @property {mixed} [content=null] - Content to be displayed in the dialog.
	 * This option accepts multiple types, including strings, DOM elements, jQuery instances, etc.
	 * @property {boolean} [destroyOnClose=true] - Destroy the dialog when it is closed?
	 * @property {string|null} [url=null] - A URL string to load the dialog content in via an
	 * AJAX request.
	 * @property {object|null} [data=null] - Request data used when loading dialog content
	 * via an AJAX request.
	 * @property {string|null} [iframe=null] - A URL string to load the dialog content
	 * in via an iFrame.
	 * @property {string|null} [title=null] - The dialog title.
	 * @property {string|null} [dialogClass=dialog] - A CSS class string to be added to
	 * the main dialog container.
	 * @property {boolean} [constrainToViewport=false] - Constrain the dialog dimensions
	 * so that it is never displayed outside of the window viewport?
	 * @property {integer|string} [width=400] - The dialog width.
	 * @property {integer|string} [height=auto] - The dialog height.
	 * @private
	 */
	Dialog._defaultOptions = {
		content: null,
		destroyOnClose: true,
		url: null,
		data: null,
		id: null,
		iframe: null,
		autoOpen: false,
		title: null,
		modal: true,
		dialogClass: 'dialog',
		resizable: false,
		draggable: false,
		constrainToViewport: false,
		width: 440,
		height: 'auto',
		minHeight: 'auto',
		show: 'fade'
	};

	/**
	 * Creates and stores the dialog container, and creates a new jQuery UI
	 * instance on the container.
	 * @name OpenEyes.UI.Dialog#create
	 * @method
	 * @private
	 */
	Dialog.prototype.create = function() {

		// Create the dialog content div.
		this.content = $('<div />', { id: this.options.id });

		// Add default content (if any exists)
		this.setContent(this.options.content);

		// Create the jQuery UI dialog.
		this.content.dialog(this.options);

		// Store a reference to the jQuery UI dialog instance.
		this.instance = this.content.data('ui-dialog');
	};

	/**
	 * Add content to the dialog.
	 * @name OpenEyes.UI.Dialog#setContent
	 * @method
	 * @public
	 */
	Dialog.prototype.setContent = function(content) {
		if (typeof(this.getContent) == 'function') {
			var options = $.extend({}, this.options, {content: content});
			content = this.getContent(options);
		}
		this.content.html(content);
	};

	/**
	 * Binds common dialog event handlers.
	 * @name OpenEyes.UI.Dialog#create
	 * @method
	 * @private
	 */
	Dialog.prototype.bindEvents = function() {
		this.content.on({
			dialogclose: this.onDialogClose.bind(this),
			dialogopen: this.onDialogOpen.bind(this)
		});
	};

	/**
	 * Gets a script template from the DOM, compiles it using Mustache, and
	 * returns the HTML.
	 * @name OpenEyes.UI.Dialog#compileTemplate
	 * @method
	 * @private
	 * @param {object} options - An options object container the template selector and data.
	 * @returns {string}
	 */
	Dialog.prototype.compileTemplate = function(options) {

		var template = $(options.selector).html();

		if (!template) {
			throw new Error('Unable to compile dialog template. Template not found: ' + options.selector);
		}

		return Mustache.render(template, options.data || {});
	};

	/**
	 * Sets the dialog to be in a loading state.
	 * @name OpenEyes.UI.Dialog#setLoadingState
	 * @method
	 * @private
	 */
	Dialog.prototype.setLoadingState = function() {
		this.content.addClass('loading');
		this.setTitle('Loading...');
	};

	/**
	 * Removes the loading state from the dialog.
	 * @name OpenEyes.UI.Dialog#removeLoadingState
	 * @method
	 * @private
	 */
	Dialog.prototype.removeLoadingState = function() {
		this.content.removeClass('loading');
	};

	/**
	 * Sets a 'loading' message and retrieves the dialog content via AJAX.
	 * @name OpenEyes.UI.Dialog#loadContent
	 * @method
	 * @private
	 */
	Dialog.prototype.loadContent = function() {

		this.setLoadingState();

		this.xhr = $.ajax({
			url: this.options.url,
			data: this.options.data
		});

		this.xhr.done(this.onContentLoadSuccess.bind(this));
		this.xhr.fail(this.onContentLoadFail.bind(this));
		this.xhr.always(this.onContentLoad.bind(this));
	};

	/**
	 * Sets a 'loading' message and creates an iframe with the appropriate src attribute.
	 * @name OpenEyes.UI.Dialog#loadIframeContent
	 * @method
	 * @private
	 */
	Dialog.prototype.loadIframeContent = function() {

		this.setLoadingState();

		this.iframe = $('<iframe />', {
			width: '100%',
			height: '99%',
			frameborder: 0
		}).hide();

		// We're intentionally setting the load handler before setting the src.
		this.iframe.on('load', this.onIframeLoad.bind(this));
		this.iframe.attr({
			src: this.options.iframe,
		});

		// Add the iframe to the DOM.
		this.setContent(this.iframe);
	};

	/**
	 * Sets the dialog title.
	 * @name OpenEyes.UI.Dialog#setTitle
	 * @method
	 * @public
	 */
	Dialog.prototype.setTitle = function(title) {
		this.instance.option('title', title);
	};

	/**
	 * Repositions the dialog in the center of the page.
	 * @name OpenEyes.UI.Dialog#reposition
	 * @method
	 * @public
	 */
	Dialog.prototype.reposition = function() {
		this.instance._position(this.instance._position());
	};

	/**
	 * Calculates the dialog dimensions. If OpenEyes.UI.Dialog#options.constrainToViewport is
	 * set, then the dimensions will be calculated so that the dialog will not be
	 * displayed outside of the browser viewport.
	 * @name OpenEyes.UI.Dialog#getDimensions
	 * @method
	 * @private
	 */
	Dialog.prototype.getDimensions = function() {

		var dimensions = {
			width: this.options.width,
			height: this.options.height
		};

		// We're just ensuring the maximum height of the dialog does not exceed either
		// the specified height (set in the options), or the height of the viewport. We're
		// not 'fitting' to the viewport.
		if (this.options.constrainToViewport) {
			var actualDimensions = this.getActualDimensions();
			var offset = 40;
			dimensions.width = Math.min(actualDimensions.width, $(window).width() - offset);
			dimensions.height = Math.min(actualDimensions.height, $(window).height() - offset);
		}

		return dimensions;
	};

	/**
	 * Gets the actual dimensions of the dialog. We need to ensure the dialog
	 * is open to calculate the dimensions.
	 * @return {object} An object containing the width and height dimensions.
	 */
	Dialog.prototype.getActualDimensions = function() {

		var isOpen = this.instance.isOpen();
		var destroyOnClose = this.options.destroyOnClose;

		if (!isOpen) {
			this.options.destroyOnClose = false;
			this.instance.open();
		}

		var dimensions = {
			width: parseInt(this.options.width, 10) || this.instance.uiDialog.outerWidth(),
			height: parseInt(this.options.height, 10) || this.instance.uiDialog.outerHeight()
		};

		if (!isOpen) {
			this.instance.close();
			this.options.destroyOnClose = destroyOnClose;
		}

		return dimensions;
	};

	/**
	 * Calculates and sets the dialog dimensions.
	 * @name OpenEyes.UI.Dialog#setDimensions
	 * @method
	 * @private
	 */
	Dialog.prototype.setDimensions = function() {
		var dimensions = this.getDimensions();
		this.instance.option('width', dimensions.width);
		this.instance.option('height', dimensions.height);
	};

	/**
	 * Opens (shows) the dialog.
	 * @name OpenEyes.UI.Dialog#open
	 * @method
	 * @public
	 */
	Dialog.prototype.open = function() {
		this.setDimensions();
		this.instance.open();
		this.reposition();
	};

	/**
	 * Closes (hides) the dialog.
	 * @name OpenEyes.UI.Dialog#close
	 * @method
	 * @public
	 */
	Dialog.prototype.close = function() {
		this.instance.close();
	};

	/**
	 * Destroys the dialog. Removes all elements from the DOM and detaches all
	 * event handlers.
	 * @name OpenEyes.UI.Dialog#destroy
	 * @fires OpenEyes.UI.Dialog#destroy
	 * @method
	 * @public
	 *
	 */
	Dialog.prototype.destroy = function() {

		if (this.xhr) {
			this.xhr.abort();
		}
		if (this.iframe) {
			this.iframe.remove();
		}

		this.instance.destroy();
		this.content.remove();

		/**
		 * Emitted after the dialog has been destroyed and completed removed from the DOM.
		 *
		 * @event OpenEyes.UI.Dialog#destroy
		 */
		this.emit('destroy');
	};

	/** Event handlers */

	/**
	 * Emit the 'open' event after the dialog has opened.
	 * @name OpenEyes.UI.Dialog#onDialogOpen
	 * @fires OpenEyes.UI.Dialog#open
	 * @method
	 * @private
	 */
	Dialog.prototype.onDialogOpen = function() {
		/**
		 * Emitted after the dialog has opened.
		 *
		 * @event OpenEyes.UI.Dialog#open
		 */
		this.emit('open');
	};

	/**
	 * Emit the 'close' event after the dialog has closed, and optionally destroy
	 * the dialog.
	 * @name OpenEyes.UI.Dialog#onDialogClose
	 * @fires OpenEyes.UI.Dialog#close
	 * @method
	 * @private
	 */
	Dialog.prototype.onDialogClose = function() {
		/**
		 * Emitted after the dialog has closed.
		 *
		 * @event OpenEyes.UI.Dialog#close
		 */
		this.emit('close');

		if (this.options.destroyOnClose) {
			this.destroy();
		}
	};

	/**
	 * Content load handler. This method is always executed *after* the content
	 * request completes (whether there was an error or not), and is executed after
	 * any success or fail handlers. This method removes the loading state of the
	 * dialog, and repositions it in the center of the screen.
	 * @name OpenEyes.UI.Dialog#onContentLoad
	 * @method
	 * @private
	 */
	Dialog.prototype.onContentLoad = function() {
		this.removeLoadingState();
		this.setDimensions();
		this.reposition();
	};

	/**
	 * Content load success handler. Sets the dialog content to be the response of
	 * the content request.
	 * @name OpenEyes.UI.Dialog#onContentLoadSuccess
	 * @method
	 * @private
	 */
	Dialog.prototype.onContentLoadSuccess = function(response) {
		this.setTitle(this.options.title);
		this.setContent(response);
	};

	/**
	 * Content load fail handler. This method is executed if the content request
	 * fails, and shows an error message.
	 * @name OpenEyes.UI.Dialog#onContentLoadFail
	 * @method
	 * @private
	 */
	Dialog.prototype.onContentLoadFail = function() {
		this.setTitle('Error');
		this.setContent('Sorry, there was an error retrieving the content. Please try again.');
	};

	/**
	 * iFrame load handler. This method is always executed after the iFrame
	 * source is loaded. This method removes the loading state of the
	 * dialog, and repositions it in the center of the screen.
	 * @name OpenEyes.UI.Dialog#onIframeLoad
	 * @method
	 * @private
	 */
	Dialog.prototype.onIframeLoad = function() {
		this.setTitle(this.options.title);
		this.iframe.show();
		this.onContentLoad();
	};

	exports.Dialog = Dialog;

}(OpenEyes.UI, OpenEyes.Util, OpenEyes.Util.EventEmitter));
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */
(function(exports, Util) {

	'use strict';

	// Base Dialog.
	var Dialog = exports;

	/**
	 * AlertDialog constructor. The AlertDialog extends the base Dialog and provides
	 * an 'Ok' button for the user to click on.
	 * @constructor
	 * @name OpenEyes.UI.Dialog.Alert
	 * @tutorial dialog_alert
	 * @extends OpenEyes.UI.Dialog
	 * @example
	 * var alert = new OpenEyes.UI.Dialog.Alert({
	 *   content: 'Here is some content.'
	 * });
	 * alert.open();
	 */
	function AlertDialog(options) {

		options = $.extend(true, {}, AlertDialog._defaultOptions, options);

		Dialog.call(this, options);
	}

	Util.inherits(Dialog, AlertDialog);

	/**
	 * The default alert dialog options. These options will be merged into the
	 * default dialog options.
	 * @name OpenEyes.UI.Dialog.Alert#_defaultOptions
	 * @private
	 */
	AlertDialog._defaultOptions = {
		modal: true,
		width: 400,
		minHeight: 'auto',
		title: 'Alert',
		dialogClass: 'dialog alert'
	};

	/**
	 * Get the dialog content. Do some basic content formatting, then compile
	 * and return the alert dialog template.
	 * @name OpenEyes.UI.Dialog.Alert#getContent
	 * @method
	 * @private
	 * @param {string} content - The main alert dialog content to display.
	 * @returns {string}
	 */
	AlertDialog.prototype.getContent = function(options) {

		// Replace new line characters with html breaks
		options.content = (options.content || '').replace(/\n/g, '<br/>');

		// Compile the template, get the HTML
		return this.compileTemplate({
			selector: '#dialog-alert-template',
			data: {
				content: options.content
			}
		});
	};

	/**
	 * Bind events
	 * @name OpenEyes.UI.Dialog.Alert#bindEvents
	 * @method
	 * @private
	 */
	AlertDialog.prototype.bindEvents = function() {
		Dialog.prototype.bindEvents.apply(this, arguments);
		this.content.on('click', '.ok', this.onButtonClick.bind(this));
	};

	/** Event handlers */

	/**
	 * 'OK' button click handler. Simply close the dialog on click.
	 * @name OpenEyes.UI.Dialog.Alert#onButtonClick
	 * @method
	 * @private
	 */
	AlertDialog.prototype.onButtonClick = function() {
		this.close();
		/**
		 * Emitted after the use has clicked on the 'OK' button.
		 *
		 * @event OpenEyes.UI.Dialog.Alert#ok
		 */
		this.emit('ok');
	};

	exports.Alert = AlertDialog;

}(OpenEyes.UI.Dialog, OpenEyes.Util));
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */
(function(exports, Util) {

	'use strict';

	// Base Dialog.
	var Dialog = exports;

	/**
	 * ConfirmDialog constructor. The ConfirmDialog extends the base Dialog and provides
	 * an 'Ok' and 'Cancel' button for the user to click on.
	 * @constructor
	 * @name OpenEyes.UI.Dialog.Confirm
	 * @tutorial dialog_confirm
	 * @extends OpenEyes.UI.Dialog
	 * @example
	 * var alert = new OpenEyes.UI.Dialog.Confirm({
	 *   content: 'Here is some content.'
	 * });
	 * alert.open();
	 */
	function ConfirmDialog(options) {

		options = $.extend(true, {}, ConfirmDialog._defaultOptions, options);
		options.content = !options.url ? options.content : '';

		Dialog.call(this, options);
	}

	Util.inherits(Dialog, ConfirmDialog);

	/**
	 * The default confirm dialog options. These options will be merged into the
	 * default dialog options.
	 * @name OpenEyes.UI.Dialog.Confirm#_defaultOptions
	 * @private
	 */
	ConfirmDialog._defaultOptions = {
		modal: true,
		width: 400,
		minHeight: 'auto',
		title: 'Confirm',
		dialogClass: 'dialog confirm',
		okButton: 'OK',
		cancelButton: 'Cancel'
	};

	/**
	 * Get the dialog content. Do some basic content formatting, then compile
	 * and return the alert dialog template.
	 * @name OpenEyes.UI.Dialog.Confirm#getContent
	 * @method
	 * @private
	 * @param {string} content - The main alert dialog content to display.
	 * @returns {string}
	 */
	ConfirmDialog.prototype.getContent = function(options) {
		// Compile the template, get the HTML
		return this.compileTemplate({
			selector: '#dialog-confirm-template',
			data: {
				content: options.content,
				okButton: options.okButton,
				cancelButton: options.cancelButton
			}
		});
	};

	/**
	 * Bind events
	 * @name OpenEyes.UI.Dialog.Confirm#bindEvents
	 * @method
	 * @private
	 */
	ConfirmDialog.prototype.bindEvents = function() {
		Dialog.prototype.bindEvents.apply(this, arguments);
		this.content.on('click', '.ok', this.onOKButtonClick.bind(this));
		this.content.on('click', '.cancel', this.onCancelButtonClick.bind(this));
	};

	/** Event handlers */

	ConfirmDialog.prototype.onDialogClose = function(e) {

		Dialog.prototype.onDialogClose.apply(this, arguments);

		// If user pressed escape key.
		if (e && e.keyCode && e.keyCode === 27) {
			this.emit('cancel');
		}

		// If user clicked on close button.
		if ($(e.srcElement).hasClass('ui-dialog-titlebar-close')) {
			this.emit('cancel');
		}
	}

	/**
	 * 'OK' button click handler. Simply close the dialog on click.
	 * @name OpenEyes.UI.Dialog.Confirm#onButtonClick
	 * @fires OpenEyes.UI.Dialog.Confirm#ok
	 * @method
	 * @private
	 */
	ConfirmDialog.prototype.onOKButtonClick = function() {

		this.close();

		/**
		 * Emitted after the use has clicked on the 'OK' button.
		 *
		 * @event OpenEyes.UI.Dialog.Confirm#ok
		 */
		this.emit('ok');
	};

	/**
	 * 'Cancel' button click handler. Simply closes the dialog on click.
	 * @name OpenEyes.UI.Dialog.Confirm#onButtonClick
	 * @fires OpenEyes.UI.Dialog.Confirm#cancel
	 * @method
	 * @private
	 */
	ConfirmDialog.prototype.onCancelButtonClick = function() {

		this.close();

		/**
		 * Emitted after the use has clicked on the 'Cancel' button.
		 *
		 * @event OpenEyes.UI.Dialog.Confirm#cancel
		 */
		this.emit('cancel');
	};

	/**
	 * Content load success handler. Sets the dialog content to be the response of
	 * the content request.
	 * @name OpenEyes.UI.Dialog.Confirm#onContentLoadSuccess
	 * @method
	 * @private
	 */
	ConfirmDialog.prototype.onContentLoadSuccess = function(response) {
		this.options.content = response;
		Dialog.prototype.onContentLoadSuccess.call(this, this.getContent(this.options));
	};

	exports.Confirm = ConfirmDialog;

}(OpenEyes.UI.Dialog, OpenEyes.Util));
/**
 * (C) OpenEyes Foundation, 2014
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (C) 2014, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

(function (exports) {
	/**
	 * OpenEyes UI Widgets namespace
	 * @namespace OpenEyes.UI.Widgets
	 */
	exports.Widgets = {};
}(this.OpenEyes.UI));

/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

(function(exports, Util, EventEmitter) {

	'use strict';
	/**
	 * FieldImages constructor.
	 * @constructor
	 * @name OpenEyes.UI.FieldImages
	 * @memberOf OpenEyes.UI
	 * @extends OpenEyes.Util.EventEmitter
	 * @example
	 * var fieldImages = new OpenEyes.UI.FieldImages({
	 *	images: OpenEyes.Util.FieldImagesList,
	 *	idToImages: array('ElementHTML_id' => OpenEyes.Util.FieldImagesList.property)
	 * });
	 */
	function FieldImages(options) {
		EventEmitter.call(this);
		this.options = $.extend(true, {}, FieldImages._defaultOptions, options);
	}

	Util.inherits(EventEmitter, FieldImages);

	/**
	 * The default dialog options. Custom options will be merged with these.
	 * @name OpenEyes.UI.FieldImages#_defaultOptions
		 * @property {string} [title=null] - Dialog title
		 * @property {array} [images=null] - Images available for fields
	 * @property {array} [idToImages=null] - Html ID to images relation
	 */
	FieldImages._defaultOptions = {
		title: "Field Images",
		images: null,
		idToImages:null,
		dialogInstance:null
	};

	/**
	 * Creates and stores the dialog container, and creates a new jQuery UI
	 * instance on the container.
	 * @name OpenEyes.UI.FieldImages#create
	 * @method
	 * @private
	 */
	FieldImages.prototype.createDiag = function(fieldElId) {
		this.options.dialog = new OpenEyes.UI.Dialog({
			title: this.options.title,
			content: this.createImagesDiv(this.options.idToImages[fieldElId ], fieldElId)
		});
		this.options.dialog.open();
	};

		/**
		 * Creates the Images container
		 * @name OpenEyes.UI.FieldImages#createImagesDiv
		 * @method
		 * @private
		 */
	FieldImages.prototype.createImagesDiv = function(fieldElId, selectId) {
		var wrapper = $('<div/>', {
			'class':  "fieldsWrapper"
		});
		for(var sval in fieldElId['selects']){
			var imgPath = null;
			if(fieldElId['id'] in this.options.images){
				if(sval in this.options.images[fieldElId['id']]){
					imgPath = this.options.images[fieldElId['id']][sval];
				}
			}
			var el = $('<div/>', {
					class: 'ui-field-image'
			}).click({
				selectId: selectId,
				val: sval,
				fieldImgInstance: this
			},function(e) {
				$( "#"+ e.data.selectId).val(e.data.val);
				e.data.fieldImgInstance.options.dialog.close();
			});
			var valPar = $('<p class="ui-field-image-val">' + sval + '</p>');
			if(imgPath){
				$(el).css("background-image", "url("+ imgPath + ")");
				$(valPar).appendTo(el);
			}
			else{
				$(el).css("background-color", "#999");
				$(valPar).appendTo(el);
				$('<p class="ui-field-image-no-preview">No Preview</p>').appendTo(el)
			}

			$(el).appendTo(wrapper);
		}
		return wrapper;
	};

	/**
	 * Sets fields  buttons in dom
	 * @name OpenEyes.UI.FieldImages#setFieldButtons
	 * @method
	 * @private
	 */
	FieldImages.prototype.setFieldButtons = function() {
		for (var selectId in this.options.idToImages) {

			if($('#' + selectId)){
				this.options.idToImages[selectId]
				$('<img/>', {
					id: selectId + "_cog",
					src: OE_core_asset_path + '/img/_elements/icons/event/small/images_photo.png',
					alt: 'Opens ' + selectId + ' field images',
					'class': 'ui-field-images-icon'
				}).insertAfter( '#' + selectId );

				var this_ = this;

				$( '#' + selectId + "_cog").click( function() {
					var sId = this.id.substr(0, (this.id.length - 4) );
					this_.createDiag( sId);
				});
			}
		}
	};

	exports.FieldImages = FieldImages;

}(OpenEyes.UI, OpenEyes.Util, OpenEyes.Util.EventEmitter));