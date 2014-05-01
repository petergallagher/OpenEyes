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

$(document).ready(function() {
	$('.addAllergy').unbind('click').click(function(e) {
		e.preventDefault();

		var input_name = $(this).data('input-name');

		$('.addAllergyFields[data-input-name="'+input_name+'"]').slideDown('fast');

		$(this).slideUp('fast');
	});

	$('.allergySelection').unbind('change').change(function(e) {
		var input_name = $(this).data('input-name');
		var no_allergies_field = $(this).data('no-allergies-field');

		if ($(this).val() != '') {
			$('.allergies[data-input-name="'+input_name+'"] tbody').append('<tr><td>'+$(this).children('option:selected').text()+'<input type="hidden" name="'+input_name+'_allergies[]" value="'+$(this).val()+'" /></td><td><a href="#" class="removeAllergy" data-input-name="'+input_name+'" data-no-allergies-field="'+no_allergies_field+'">remove</a></td></tr>');
			$('.allergies[data-input-name="'+input_name+'"] tbody tr.no_allergies').hide();

			if (no_allergies_field.length >0) {
				$('#'+no_allergies_field).removeAttr('checked');
				$('#'+no_allergies_field).attr('disabled','disabled');
			}

			$(this).children('option:selected').remove();

			$('.addAllergyFields[data-input-name="'+input_name+'"]').slideUp('fast');
			$('.addAllergy[data-input-name="'+input_name+'"]').slideDown('fast');
		}
	});

	$('.removeAllergy').die('click').live('click',function(e) {
		e.preventDefault();

		var input_name = $(this).data('input-name');
		var name = $(this).closest('tr').children('td:first').text().trim();
		var id = $(this).closest('tr').children('td:first').children('input').val();
		var no_allergies_field = $(this).data('no-allergies-field');

		$(this).closest('tr').remove();

		$('.allergySelection[data-input-name="'+input_name+'"]').append('<option value="'+id+'">'+name+'</option>');

		sort_selectbox($('.allergySelection[data-input-name="'+input_name+'"]'));

		if ($('.allergies[data-input-name="'+input_name+'"] tbody tr').length == 1) {
			$('.allergies[data-input-name="'+input_name+'"] tbody tr.no_allergies').show();

			if (no_allergies_field.length >0) {
				$('#'+no_allergies_field).removeAttr('disabled');
			}
		}
	});

	$('.cancelAllergy').unbind('click').click(function(e) {
		e.preventDefault();

		var input_name = $(this).data('input-name');

		$('.addAllergyFields[data-input-name="'+input_name+'"]').slideUp('fast');
		$('.addAllergy[data-input-name="'+input_name+'"]').slideDown('fast');
	});
});
