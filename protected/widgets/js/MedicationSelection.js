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
	$('.addMedication').die('click').live('click',function(e) {
		e.preventDefault();

		var input_name = $(this).data('input-name');

		$('.saveMedication[data-input-name="'+input_name+'"]').text('Add');
		$('#_'+input_name+'_edit_row_id').val('');

		$('.addMedicationFields[data-input-name="'+input_name+'"]').slideDown('fast');

		$(this).slideUp('fast');

		$('#'+input_name+'_autocomplete_drug_id').focus();
	});

	$('.saveMedication').die('click').live('click',function(e) {
		e.preventDefault();

		var input_name = $(this).data('input-name');

		var i = 0;

		$('.medications[data-input-name="'+input_name+'"] thead tr').map(function() {
			if ($(this).attr('id') && parseInt($(this).attr('id').replace(/t/,'')) >= i) {
				i = parseInt($(this).attr('id').replace(/t/,'')) + 1;
			}
		});

		$.ajax({
			'type': 'POST',
			'url': baseUrl+'/patient/validateMedication',
			'data': 'YII_CSRF_TOKEN='+YII_CSRF_TOKEN+'&drug_id='+$('#_'+input_name+'_medication_id').val()+'&route_id='+$('#'+input_name+'_route_id').val()+'&option_id='+$('#'+input_name+'_option_id').val()+'&frequency_id='+$('#'+input_name+'_frequency_id').val()+'&start_date='+$('#'+input_name+'_start_date').val()+'&i='+i+'&input_name='+input_name,
			'dataType': 'json',
			'success': function(resp) {
				$('.medicationErrorList[data-input-name="'+input_name+'"]').html('');

				if (resp['status'] == 'error') {
					for (var i in resp['errors']) {
						$('.medicationErrorList[data-input-name="'+input_name+'"]').append('<li>'+resp['errors'][i]);
					}

					$('.medicationErrors[data-input-name="'+input_name+'"]').show();
				} else {
					$('.medicationErrors[data-input-name="'+input_name+'"]').hide();

					if ($('#_'+input_name+'_edit_row_id').val() != '' || !medication_in_list($('#_'+input_name+'_medication_id').val(),$('#'+input_name+'_start_date').val(),input_name)) {
						$('.medications[data-input-name="'+input_name+'"] tr.no_medications').hide();

						if ($('#_'+input_name+'_edit_row_id').val() == '') {
							$('.medications[data-input-name="'+input_name+'"] tbody').append(resp['row']);
						} else {
							$('#'+$('#_'+input_name+'_edit_row_id').val()).replaceWith(resp['row']);
						}
						var i = 0;
						$('.medications[data-input-name="'+input_name+'"] tbody tr').map(function() {
							$(this).attr('id','t'+i);
							i += 1;
						});
						$('.cancelMedication[data-input-name="'+input_name+'"]').click();
					} else {
						$('.medicationErrorList[data-input-name="'+input_name+'"]').append('Medication is already in the list for the given date');
						$('.medicationErrors[data-input-name="'+input_name+'"]').show();
					}
				}
			}
		});
	});

	$('.editMedication').die('click').live('click',function(e) {
		e.preventDefault();

		var input_name = $(this).data('input-name');

		$('#_'+input_name+'_medication_id').val($(this).data('drug-id'));
		$('.medicationName[data-input-name="'+input_name+'"] span').text($(this).data('drug-name'));
		$('#'+input_name+'_route_id').val($(this).data('route-id'));
		MedicationSelection_options(input_name,$('#'+input_name+'_route_id').children('option:selected').text() == 'Eye');
		$('#'+input_name+'_option_id').val($(this).data('option-id'));
		$('#'+input_name+'_frequency_id').val($(this).data('frequency-id'));
		$('#'+input_name+'_start_date').val($(this).data('start-date'));
		$('#_'+input_name+'_edit_row_id').val($(this).closest('tr').attr('id'));

		$('.saveMedication[data-input-name="'+input_name+'"]').text('Update');

		$('.addMedicationFields[data-input-name="'+input_name+'"]').slideDown('fast');

		$('.addMedication[data-input-name="'+input_name+'"]').slideUp('fast');
	});

	$('.removeMedication').die('click').live('click',function(e) {
		e.preventDefault();

		var input_name = $(this).data('input-name');

		$(this).closest('tr').remove();

		if ($('.medications[data-input-name="'+input_name+'"] tbody tr').length == 1) {
			$('.medications[data-input-name="'+input_name+'"] tr.no_medications').show();
		}

		if ($('.addMedicationFields[data-input-name="'+input_name+'"]').is(':visible')) {
			$('.cancelMedication[data-input-name="'+input_name+'"]').click();
		}
	});

	$('.cancelMedication').die('click').live('click',function(e) {
		e.preventDefault();

		var input_name = $(this).data('input-name');

		$('.addMedicationFields[data-input-name="'+input_name+'"]').slideUp('fast');

		$('.addMedication[data-input-name="'+input_name+'"]').slideDown('fast');

		$('#'+input_name+'_medication_id').val('');
		$('#'+input_name+'_autocomplete_drug_id').val('');
		$('#'+input_name+'_route_id').val('');
		$('#'+input_name+'_option_id').html('<option value="">- Select -</option>');
		$('#'+input_name+'_option_id').val('');
		$('#'+input_name+'_frequency_id').val('');
		$('#'+input_name+'_start_date').val('');
		$('.medicationName[data-input-name="'+input_name+'"] span').html('None selected');
		$('#_'+input_name+'medication_id').val('');
	});

	$('.MedicationSelection-medication-id').die('change').live('change',function(e) {
		var input_name = $(this).data('input-name');

		if ($(this).val() != '') {
			$('.medicationName[data-input-name="'+input_name+'"] span').html($(this).children('option:selected').text());
			$('#_'+input_name+'_medication_id').val($(this).children('option:selected').val());
			$(this).val('');
		}
	});

	$('.MedicationSelection-route-id').die('change').live('change',function() {
		var input_name = $(this).data('input-name');

		MedicationSelection_options(input_name,$(this).children('option:selected').text() == 'Eye');
	});
});

function MedicationSelection_options(input_name,enable)
{
	if (enable) {
		$('#'+input_name+'_option_id').html('<option value="">- Select -</option><option value="1">Left</option><option value="2">Right</option><option value="3">Both</option></select>');
	} else {
		$('#'+input_name+'_option_id').html('<option value="">- Select -</option>');
	}
}

function medication_in_list(drug_id,start_date,input_name)
{
	var drug_ids = [];
	var start_dates = [];

	$('input[name="'+input_name+'_drug_ids[]"]').map(function() {
		drug_ids.push($(this).val());
	});

	$('input[name="'+input_name+'_start_dates[]"]').map(function() {
		start_dates.push($(this).parent().text().trim());
	});

	for (var i in drug_ids) {
		if (drug_ids[i] == drug_id && start_dates[i] == start_date) {
			return true;
		}
	}

	return false;
}
