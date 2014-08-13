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

		var div = $(this).closest('div');

		$('.add-allergy').slideDown('fast',function() {
			div.slideUp('fast');
		});
	});

	$('.cancelAllergy').unbind('click').click(function(e) {
		e.preventDefault();

		$('.add-allergy').slideUp('fast',function() {
			$('.add-allergy').prev('.box-actions').slideDown('fast');
			$('#allergy_id').val('');
		});
	});

	$('.saveAllergy').unbind('click').click(function(e) {
		e.preventDefault();

		if (OE_allergies_post) {
			$('#add-allergy').submit();
		} else {
			if ($('#no_allergies').is(':checked')) {
				Allergies_confirm_none();
			} else {
				if ($('#allergy_id').val() != '') {
					Allergies_add();
				} else {
					Allergies_unconfirm_none();
				}
			}
		}
	});

	$('#no_allergies').click(function(e) {
		if ($(this).is(':checked')) {
			$('#allergy_field').slideUp('fast');
		} else {
			$('#allergy_field').slideDown('fast');
		}
	});

	$('button.btn_cancel_remove_allergy').unbind('click').click(function(e) {
		e.preventDefault();
		$("#confirm_remove_allergy_dialog").dialog("close");
	});

	$('.removeAllergy').die('click').live('click',function(e) {
		e.preventDefault();

		if (OE_allergies_post) {
			$('#remove_allergy_id').val($(this).attr('rel'));

			$('#confirm_remove_allergy_dialog').dialog({
				resizable: false,
				modal: true,
				width: 560
			});
		} else {
			Allergies_remove_row($(this).closest('tr'));
		}
	});

	$('button.btn_remove_allergy').click(function() {
		$("#confirm_remove_allergy_dialog").dialog("close");

		var aa_id = $('#remove_allergy_id').val();

		$.ajax({
			'type': 'GET',
			'url': baseUrl+'/patient/removeAllergy?patient_id=' + OE_patient_id + '&assignment_id=' + aa_id,
			'success': function(html) {
				if (html == 'success') {
					var row = $('.currentAllergies tr[data-assignment-id="' + aa_id + '"]');
					var allergy_id = row.data('allergy-id');
					var allergy_name = row.data('allergy-name');

					Allergies_remove_row(row);
				} else {
					new OpenEyes.UI.Dialog.Alert({
						content: "Sorry, an internal error occurred and we were unable to remove the allergy.\n\nPlease contact support for assistance."
					}).open();
				}
			},
			'error': function() {
				new OpenEyes.UI.Dialog.Alert({
					content: "Sorry, an internal error occurred and we were unable to remove the allergy.\n\nPlease contact support for assistance."
				}).open();
			}
		});

		return false;
	});

	$('#allergy_id').unbind('change').change(function(e) {
		if ($(this).children('option:selected').text() == 'Other') {
			$('.allergyOther').show();
			if (!OE_allergies_post) {
				$('.allergyOtherButton').show();
			}
			$('#allergy_other').val('');
			$('#allergy_other').focus();
		} else {
			$('.allergyOther').hide();
			$('.allergyOtherButton').hide();

			if ($(this).val() != '' && !OE_allergies_post) {
				$('#no_allergies').removeAttr('checked');
				Allergies_add();
			}
		}
	});

	$('#no_allergies').unbind('click').click(function(e) {
		if (!OE_allergies_post) {
			if ($(this).is(':checked')) {
				Allergies_confirm_none();
			} else {
				Allergies_unconfirm_none();
			}
		}
	});
});

function Allergies_add_old()
{
	if ($('#no_allergies').is(':checked')) {
		$('#allergies_none').val(1);

		$('.currentAllergies').children('tbody').children('tr').map(function() {
			$(this).find('a').click();
		});

		$('.allergy-status-unknown').hide();
		$('.allergy-status-none').show();

		if (OE_allergies_post) {
			$('.add-allergy').slideUp('fast',function() {
				$('.add-allergy').prev('.box-actions').slideDown('fast');
			});
		}
	} else {
		$('#allergies_none').val(0);
		$('.allergy-status-none').hide();

		if ($('#allergy_id').val() != '') {
			$('.allergy-status-unknown').hide();

			var other = $('#allergy_id').children('option:selected').text() == 'Other'
				? $('#allergy_other').val()
					.replace(/&/g, "&amp;")
					.replace(/</g, "&lt;")
					.replace(/>/g, "&gt;")
					.replace(/"/g, "&quot;")
					.replace(/'/g, "&#039;")
				: '';

			var text = $('#allergy_id').children('option:selected').text() == 'Other'
				? other
				: $('#allergy_id').children('option:selected').text();

			$('.currentAllergies').children('tbody').append('<tr data-assignment-id="" data-allergy-id="' + $('#allergy_id').val() + '" data-allergy-name="' + $('#allergy_id').children('option:selected').text() + '" data-allergy-other="' + other + '"><td>' + text + '</td><td><a href="#" class="small removeAllergy">Remove</a><input type="hidden" name="Allergies[]" value="' + $('#allergy_id').val() + '" /><input type="hidden" name="AllergiesOther[]" value="' + other + '" /></td></tr>');

			$('.currentAllergies').show();

			$('#allergy_id').children('option:selected').remove();
			$('#allergy_other').val('');
			$('.allergyOther').hide();
		} else {
			$('.allergy-status-unknown').show();

			if (OE_allergies_post) {
				$('.add-allergy').slideUp('fast',function() {
					$('.add-allergy').prev('.box-actions').slideDown('fast');
				});
			}
		}
	}
}

function Allergies_add()
{
	$('#allergies_none').val(0);
	$('.allergy-status-none').hide();

	$('.allergy-status-unknown').hide();

	var other = $('#allergy_id').children('option:selected').text() == 'Other'
		? $('#allergy_other').val()
			.replace(/&/g, "&amp;")
			.replace(/</g, "&lt;")
			.replace(/>/g, "&gt;")
			.replace(/"/g, "&quot;")
			.replace(/'/g, "&#039;")
		: '';

	var text = $('#allergy_id').children('option:selected').text() == 'Other'
		? other
		: $('#allergy_id').children('option:selected').text();

	$('.currentAllergies').children('tbody').append('<tr data-assignment-id="" data-allergy-id="' + $('#allergy_id').val() + '" data-allergy-name="' + $('#allergy_id').children('option:selected').text() + '" data-allergy-other="' + other + '"><td>' + text + '</td><td><a href="#" class="small removeAllergy">Remove</a><input type="hidden" name="Allergies[]" value="' + $('#allergy_id').val() + '" /><input type="hidden" name="AllergiesOther[]" value="' + other + '" /></td></tr>');

	$('.currentAllergies').show();

	$('#allergy_id').children('option:selected').remove();
	$('#allergy_other').val('');
	$('.allergyOther').hide();
}

function Allergies_confirm_none()
{
	$('#allergies_none').val(1);

	$('.currentAllergies').children('tbody').children('tr').map(function() {
		$(this).find('a').click();
	});

	$('.allergy-status-unknown').hide();
	$('.allergy-status-none').show();

	if (OE_allergies_post) {
		$('.add-allergy').slideUp('fast',function() {
			$('.add-allergy').prev('.box-actions').slideDown('fast');
		});
	}
}

function Allergies_unconfirm_none()
{
	$('#allergies_none').val(0);
	$('.allergy-status-none').hide();

	$('.allergy-status-unknown').show();

	if (OE_allergies_post) {
		$('.add-allergy').slideUp('fast',function() {
			$('.add-allergy').prev('.box-actions').slideDown('fast');
		});
	}
}

function Allergies_remove_row(row)
{
	var tbody = $('.currentAllergies').children('tbody');

	$('#allergy_id').append('<option value="' + row.data('allergy-id') + '">' + row.data('allergy-name') + '</option>');
	sort_selectbox($('#allergy_id'));
	row.remove();

	if (tbody.children('tr').length == 0) {
		tbody.closest('table').hide();
		if ($('#allergies_none').val() == 1) {
			$('.allergy-status-none').show();
		} else {
			$('.allergy-status-unknown').show();
		}
	}
}
