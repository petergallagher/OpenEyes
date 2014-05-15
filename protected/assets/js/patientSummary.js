$(document).ready(function() {

	$('.removeDiagnosis').live('click',function(e) {
		e.preventDefault();

		$('#diagnosis_id').val($(this).attr('rel'));

		$('#confirm_remove_diagnosis_dialog').dialog({
			resizable: false,
			modal: true,
			width: 560
		});
	});

	$('button.btn_remove_diagnosis').click(function(e) {
		e.preventDefault();

		$("#confirm_remove_diagnosis_dialog").dialog("close");

		$.ajax({
			'type': 'GET',
			'url': baseUrl+'/patient/removediagnosis?patient_id='+OE_patient_id+'&diagnosis_id='+$('#diagnosis_id').val(),
			'success': function(html) {
				if (html == 'success') {
					$('a.removeDiagnosis[rel="'+$('#diagnosis_id').val()+'"]').parent().parent().remove();
				} else {
					new OpenEyes.UI.Dialog.Alert({
						content: "Sorry, an internal error occurred and we were unable to remove the diagnosis.\n\nPlease contact support for assistance."
					}).open();
				}
			},
			'error': function() {
				new OpenEyes.UI.Dialog.Alert({
					content: "Sorry, an internal error occurred and we were unable to remove the diagnosis.\n\nPlease contact support for assistance."
				}).open();
			}
		});
	});

	$('button.btn_cancel_remove_diagnosis').click(function(e) {
		e.preventDefault();
		$("#confirm_remove_diagnosis_dialog").dialog("close");
	});

	$('tr.all-episode').unbind('click').click(function(e) {
		e.preventDefault();
		window.location.href = baseUrl+'/patient/episode/'+$(this).attr('id');
	});

	$('a.removeContact').die('click').live('click',function(e) {
		e.preventDefault();

		var row = $(this).parent().parent();
		var pca_id = row.attr('data-attr-pca-id');

		// If we're currently editing this contact, hide the edit form
		var edit_contact = $("#edit_contact:visible");
		if (edit_contact.find("[name='pca_id']").val() == pca_id) {
				edit_contact.slideToggle('fast');
		}

		$.ajax({
			'type': 'GET',
			'url': baseUrl+'/patient/unassociateContact?pca_id='+pca_id,
			'success': function(resp) {
				if (resp == "1") {
					if (row.attr('data-attr-location-id')) {
						currentContacts['locations'].splice(currentContacts['locations'].indexOf(row.attr('data-attr-location-id')),1);
					} else {
						currentContacts['contacts'].splice(currentContacts['contacts'].indexOf(row.attr('data-attr-contact-id')),1);
					}
					row.remove();
				} else {
					new OpenEyes.UI.Dialog.Alert({
						content: "There was an error removing the contact association, please try again or contact support for assistance."
					}).open();
				}
			}
		});
	});

	$('#contactfilter').change(function() {
		$('#contactname').focus();
	});

	$('.patient-info .edit-patient-details').click(function(e) {
		e.preventDefault();

		if ($('section.patient-details .view-mode').is(':visible')) {
			$('.patient-details .view-mode').hide();
			$('.patient-details .edit-mode').show();

			$(this).text('view');

		} else {
			$('.patient-details .edit-mode').hide();
			$('.patient-details .view-mode').show();
			$('section.patient-details .alert-box').hide();

			resetPatientDetailsForm();

			$(this).text('edit');
		}

		enableButtons();
	});

	$('#btn-cancel-edit-patient-details').click(function(e) {
		e.preventDefault();

		$('section.patient-details .toggle-edit-patient-details').click();
	});

	handleButton($('#btn-save-patient-details'),function(e) {
		e.preventDefault();

		$('section.patient-details .alert-box ul').html('');
		$('section.patient-details .alert-box').hide();

		$.ajax({
			'type': 'POST',
			'url': baseUrl+'/patient/validatePatientDetails/'+OE_patient_id,
			'data': $('#patient-details-edit').serialize()+"&YII_CSRF_TOKEN="+YII_CSRF_TOKEN,
			'dataType': 'json',
			'success': function(data) {
				for (var field in data) {
					$('section.patient-details .alert-box ul').append('<li>'+data[field][0]+'</li>');
				}

				if ($('section.patient-details .alert-box ul li').length >0) {
					$('section.patient-details .alert-box').show();
					enableButtons();
				} else {
					$.ajax({
						'type': 'POST',
						'url': baseUrl+'/patient/updatePatientDetails/'+OE_patient_id,
						'data': $('#patient-details-edit').serialize()+"&YII_CSRF_TOKEN="+YII_CSRF_TOKEN,
						'success': function(resp) {
							if (resp != '1') {
								alert("Something went wrong trying to save the patient details, please try again or contact support for assistance.");
								enableButtons();
							} else {
								window.location.reload();
							}
						}
					});
				}
			}
		});
	});

	handleButton($('#btn-create-patient'));

	$('.patient-info .edit-patient-contact-details').click(function(e) {
		e.preventDefault();

		if ($('.patient-contact-details .view-mode').is(':visible')) {
			$('.patient-contact-details .view-mode').hide();
			$('.patient-contact-details .edit-mode').show();

			$(this).text('view');

		} else {
			$('.patient-contact-details .edit-mode').hide();
			$('.patient-contact-details .view-mode').show();
			$('section.patient-contact-details .alert-box').hide();

			resetContactDetailsForm();

			$(this).text('edit');
		}

		enableButtons();
	});

	$('#btn-cancel-edit-patient-contact-details').click(function(e) {
		e.preventDefault();

		$('.patient-info .edit-patient-contact-details').click();
	});

	handleButton($('#btn-save-patient-contact-details'),function(e) {
		e.preventDefault();

		$('section.patient-contact-details .alert-box ul').html('');
		$('section.patient-contact-details .alert-box').hide();

		$.ajax({
			'type': 'POST',
			'url': baseUrl+'/patient/validatePatientContactDetails/'+OE_patient_id,
			'data': $('#patient-contact-details-edit').serialize()+"&YII_CSRF_TOKEN="+YII_CSRF_TOKEN,
			'dataType': 'json',
			'success': function(data) {
				for (var field in data) {
					$('section.patient-contact-details .alert-box ul').append('<li>'+data[field][0]+'</li>');
				}

				if ($('section.patient-contact-details .alert-box ul li').length >0) {
					$('section.patient-contact-details .alert-box').show();
					enableButtons();
				} else {
					$.ajax({
						'type': 'POST',
						'url': baseUrl+'/patient/updatePatientContactDetails/'+OE_patient_id,
						'data': $('#patient-contact-details-edit').serialize()+"&YII_CSRF_TOKEN="+YII_CSRF_TOKEN,
						'success': function(resp) {
							if (resp != '1') {
								alert("Something went wrong trying to save the patients contact details, please try again or contact support for assistance.");
								enableButtons();
							} else {
								window.location.reload();
							}
						}
					});
				}
			}
		});
	});

	$('.patient-info .edit-patient-gp-details').click(function(e) {
		e.preventDefault();

		if ($('section.patient-gp-details .view-mode').is(':visible')) {
			$('.patient-gp-details .view-mode').hide();
			$('.patient-gp-details .edit-mode').show();

			$(this).text('view');
			$('#gp_search').select().focus();

		} else {
			$('.patient-gp-details .edit-mode').hide();
			$('.patient-gp-details .view-mode').show();
			$('section.patient-gp-details .alert-box').hide();

			resetPatientGPDetailsForm();

			$(this).text('edit');
		}

		enableButtons();
	});

	$('#btn-cancel-edit-patient-gp-details').click(function(e) {
		e.preventDefault();

		$('.patient-info .edit-patient-gp-details').click();
	});

	handleButton($('#btn-save-patient-gp-details'),function(e) {
		e.preventDefault();

		$('section.patient-gp-details .alert-box ul').html('');
		$('section.patient-gp-details .alert-box').hide();

		$.ajax({
			'type': 'POST',
			'url': baseUrl+'/patient/updatePatientGPAndPracticeDetails/'+OE_patient_id,
			'data': $('#patient-gp-details-edit').serialize()+"&YII_CSRF_TOKEN="+YII_CSRF_TOKEN,
			'success': function(resp) {
				if (resp != '1') {
					alert("Something went wrong trying to save the patient details, please try again or contact support for assistance.");
					enableButtons();
				} else {
					window.location.reload();
				}
			}
		});
	});

	$('#clear_gp').live('click',function(e) {
		e.preventDefault();

		$('#gp_id').val('');
		$('#gp_name').text('Unknown');
		$('#gp_address').text('Unknown');
		$('#gp_telephone').text('Unknown');
	});

	$('#clear_practice').live('click',function(e) {
		e.preventDefault();

		$('#practice_id').val('');
		$('#gp_practice_address').text('Unknown');
		$('#gp_practice_telephone').text('Unknown');
	});

	handleButton($('#btn-delete-patient'),function(e) {
		e.preventDefault();

		window.location.href = baseUrl+'/patient/delete/'+OE_patient_id;
	});

	handleButton($('#btn-cancel-delete-patient'),function(e) {
		e.preventDefault();

		window.location.href = baseUrl+'/patient/view/'+OE_patient_id;
	});

	handleButton($('#btn-do-delete-patient'),function(e) {
		$.ajax({
			'type': 'POST',
			'url': baseUrl+'/patient/delete/'+OE_patient_id,
			'data': 'delete=1&YII_CSRF_TOKEN='+YII_CSRF_TOKEN,
			'success': function(resp) {
				if (resp == "1") {
					window.location.href = baseUrl+'/';
				} else {
					alert("Something went wrong trying to delete the patient.  Please try again or contact support for assistance.");
				}
			}
		});
	});

	$('#gp_search').die('keypress').live('keypress',function(e) {
		if (e.keyCode == 13) {
			return false;
		}

		return true;
	});

	$('#practice_search').die('keypress').live('keypress',function(e) {
		if (e.keyCode == 13) {
			return false;
		}

		return true;
	});
});

function resetPatientDetailsForm()
{
	for (var field in PatientSummary_original_values) {
		$('#'+field).val(PatientSummary_original_values[field]);
		$('#'+field+'[type="text"]').val(PatientSummary_original_values[field]);
	}

	$('input[name="gender_id"]').map(function() {
		if ($(this).val() == PatientSummary_original_values[field]) {
			$(this).click();
		}
	});
}

function resetContactDetailsForm()
{
	$('#primary_phone').val($('#_primary_phone').val());
	$('#email').val($('#_email').val());
}

function resetPatientGPDetailsForm()
{
	$('#gp_id').val($('#_gp_id').val());
	if ($('#_gp_id').val() != '') {
		$('#gp_name').html($('#_gp_name').val()+' (<a href="#" id="clear_gp">clear</a>)');
	} else {
		$('#gp_name').text($('#_gp_name').val());
	}
	$('#gp_address').text($('#_gp_address').val());
	$('#gp_telephone').text($('#_gp_telephone').val());
	$('#practice_id').val($('#_practice_id').val());
	if ($('#_practice_id').val() != '') {
		$('#gp_practice_address').html($('#_gp_practice_address').val()+' (<a href="#" id="clear_practice">clear</a>)');
	} else {
		$('#gp_practice_address').text($('#_gp_practice_address').val());
	}
	$('#gp_practice_telephone').text($('#_gp_practice_telephone').val());
}

var contactCache = {};
