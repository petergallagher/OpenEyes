/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Softwaree Foundation, either version 3 of the License, or (at your option) any later version.
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

$(function() {

	function pad(char, val) {
		return (char + String(val)).slice(-char.length);
	}

	function padTime(time) {
		var parts = time.split(':');
		time = pad('00', parts[0]);
		time += ':';
		time += pad('00', parts[1]);
		return time;
	}

	function isValidTime(time) {

		var parts = time.split(':');

		if (!$.trim(time).length) {
			return true;
		}
		if (!/^\d\d:\d\d$/.test(time)) {
			return false;
		}
		if (parseInt(parts[0], 10) > 23) {
			return false;
		}
		if (parseInt(parts[1], 10) > 59) {
			return false;
		}
		return true;
	}

	function makeTime(time) {

		time = $.trim(time);
		if (!time.length) return time;

		if (time.length < 5) {
			if (/:/.test(time)) {
				time = padTime(time);
			} else {
				var parts = time.split('');
				parts.splice(2, 0, ':');
				time = parts.join('');
				time = padTime(time);
			}
		}

		return time;
	}

	$('.time-picker-field').each(function() {

		var field = $(this);
		var errorMsg = field.siblings('.time-picker-error');

		field.mask('Hh:ms', {
			translation: {
				'H': { pattern: /[0-2]/ },
				'h': { pattern: /\d/ },
				'm': { pattern: /[0-5]/ },
				's': { pattern: /\d/ }
			}
		}).on('blur', function() {
			this.value = makeTime(this.value);
			if (!isValidTime(this.value)) {
				errorMsg.hide().fadeIn(400);
				this.focus();
			} else {
				errorMsg.hide();
			}
		});
	});

	$(this).on('click', '.time-now', function(e) {
		e.preventDefault();

		var d = new Date;

		var h = d.getHours();
		var m = d.getMinutes();

		if (h <10) {
			h = '0'+h;
		}
		if (m <10) {
			m = '0'+m;
		}

		var element = $(this).closest('section').data('element-type-class');

		$('#'+element+'_'+$(this).data('target')).val(h+':'+m).trigger('blur');
	});
});
