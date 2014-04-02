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

		if ($('.patient-info .view-mode').is(':visible')) {
			resetPatientDetailsForm();

			$('.patient-details .view-mode').hide();
			$('.patient-details .edit-mode').show();

			$(this).text('view');

		} else {
			$('.patient-info .edit-mode').hide();
			$('.patient-info .view-mode').show();

			$(this).text('edit');
		}

		enableButtons();
	});

	handleButton($('#btn-cancel-edit-patient-details'),function(e) {
		e.preventDefault();

		$('.patient-info .toggle-edit-patient-details').click();
	});

	handleButton($('#btn-save-patient-details'),function(e) {
		e.preventDefault();

		$('#patient-details-edit span.error').val('');

		$.ajax({
			'type': 'POST',
			'url': baseUrl+'/patient/validatePatientDetails/'+OE_patient_id,
			'data': $('#patient-details-edit').serialize()+"&YII_CSRF_TOKEN="+YII_CSRF_TOKEN,
			'dataType': 'json',
			'success': function(data) {
				var errors = false;

				for (var field in data) {
					errors = true;

					$('#'+field+'_error').text(data[field][0]);
				}

				if (errors) {
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
			resetContactDetailsForm();

			$('.patient-contact-details .view-mode').hide();
			$('.patient-contact-details .edit-mode').show();

			$(this).text('view');

		} else {
			$('.patient-contact-details .edit-mode').hide();
			$('.patient-contact-details .view-mode').show();

			$(this).text('edit');
		}

		enableButtons();
	});

	handleButton($('#btn-cancel-edit-patient-contact-details'),function(e) {
		e.preventDefault();

		$('.patient-info .edit-patient-contact-details').click();
	});

	handleButton($('#btn-save-patient-contact-details'),function(e) {
		e.preventDefault();

		$('#patient-contact-details-edit span.error').val('');

		$.ajax({
			'type': 'POST',
			'url': baseUrl+'/patient/validatePatientContactDetails/'+OE_patient_id,
			'data': $('#patient-contact-details-edit').serialize()+"&YII_CSRF_TOKEN="+YII_CSRF_TOKEN,
			'dataType': 'json',
			'success': function(data) {
				var errors = false;

				for (var field in data) {
					errors = true;

					$('#'+field+'_error').text(data[field][0]);
				}

				if (errors) {
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
});

function resetPatientDetailsForm()
{
	$('#title').val($('#_title').val());
	$('#first_name').val($('#_first_name').val());
	$('#last_name').val($('#_last_name').val());
	$('#address1').val($('#_address1').val());
	$('#address2').val($('#_address2').val());
	$('#city').val($('#_city').val());
	$('#county').val($('#_county').val());
	$('#postcode').val($('#_postcode').val());
	$('#country_id').val($('#_country_id').val());
	$('#dob').val($('#_dob').val());
	$('#date_of_birth').val($('#_date_of_birth').val());
	$('#yob').val($('#_yob').val());

	$('input[name="gender_id"]').map(function() {
		if ($(this).val() == $('#_gender_id').val()) {
			$(this).click();
		}
	});
}

function resetContactDetailsForm()
{
	$('#primary_phone').val($('#_primary_phone').val());
	$('#email').val($('#_email').val());
}

var contactCache = {};
