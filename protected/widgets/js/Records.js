
$(document).ready(function() {
	$('.addRecordItem').click(function(e) {
		e.preventDefault();

		var d = new Date;
		var h = d.getHours().toString();
		if (h.length <2) {
			h = '0'+h;
		}
		var m = d.getMinutes().toString();
		if (m.length <2) {
			m = '0'+m;
		}

		switch (d.getMonth()) {
			case 0: var month = 'Jan'; break;
			case 1: var month = 'Feb'; break;
			case 2: var month = 'Mar'; break;
			case 3: var month = 'Apr'; break;
			case 4: var month = 'May'; break;
			case 5: var month = 'Jun'; break;
			case 6: var month = 'Jul'; break;
			case 7: var month = 'Aug'; break;
			case 8: var month = 'Sep'; break;
			case 9: var month = 'Oct'; break;
			case 10: var month = 'Nov'; break;
			case 11: var month = 'Dec'; break;
		}

		var addRecordItemDiv = $(this).closest('.recordsWidget').find('.addRecordItemDiv');

		addRecordItemDiv.find('.recordTime').val(h+':'+m);
		addRecordItemDiv.find('.recordTimestamp').val(d.getDate()+' '+month+' '+d.getFullYear());
		addRecordItemDiv.find('.recordInput').val('');

		addRecordItemDiv.children('.recordEditItem').val('');

		addRecordItemDiv.slideDown('fast',function() {
			$(this).closest('.addRecordItemDiv').find('input.recordInput:first').focus();
			$('.addItemButton').slideUp('fast');
		});
	});

	$('.cancelRecordItem').unbind('click').click(function(e) {
		e.preventDefault();

		var form_div = $(this).closest('.recordsWidget').find('.addRecordItemDiv');
		var error_div = form_div.find('.recordItemErrorsDiv');
		var error_list = error_div.find('.recordItemErrors');

		$('.addRecordItemDiv').slideUp('fast',function() {
			$('.addItemButton').slideDown('fast',function() {
				error_list.html('');
				error_div.hide();
			});
		});
	});

	$('.saveRecordItem').unbind('click').click(function(e) {
		e.preventDefault();

		var validate_method = $(this).data('validate-method');
		var table = $(this).closest('.recordsWidget').find('.recordsTable');
		var form_div = $(this).closest('.recordsWidget').find('.addRecordItemDiv');
		var error_div = form_div.find('.recordItemErrorsDiv');
		var error_list = error_div.find('.recordItemErrors');

		var data = 'timestamp=' + form_div.find('.recordTimestamp').val() + '&time=' + form_div.find('.recordTime').val();

		form_div.find('.recordInput').map(function() {
			data += '&' + $(this).attr('name') + '=' + $(this).val();
		});

		var i = table.children('tbody').children('tr:last').data('i');

		if (typeof(i) == 'undefined') {
			i = 0;
		} else {
			i = parseInt(i) + 1;
		}

		$.ajax({
			'type': 'POST',
			'url': baseUrl + validate_method,
			'data': data + '&i=' + i + '&YII_CSRF_TOKEN=' + YII_CSRF_TOKEN,
			'dataType': 'json',
			'success': function(errors) {
				error_list.html('');

				if (typeof(errors['row']) != 'undefined') {
					error_div.hide();

					if (form_div.children('.recordEditItem').val() == '') {
						table.children('tbody').append(errors['row']);
						table.children('tbody').find('tr:first').hide();
						form_div.find('.recordsUseLastItemRow').show();
					} else {
						table.children('tbody').children('tr[data-i="'+form_div.children('.recordEditItem').val()+'"]').replaceWith(errors['row']);
					}

					$('.addRecordItemDiv').slideUp('fast',function() {
						$('.addItemButton').slideDown('fast');
					});
				} else {
					for (var i in errors) {
						error_list.append('<li>'+errors[i]+'</li>');
					}
					error_div.show();
				}
			}
		});
	});

	$('.deleteRecordItem').die('click').live('click',function(e) {
		e.preventDefault();

		var tbody = $(this).closest('tbody');
		var form_div = $(this).closest('.recordsWidget').find('.addRecordItemDiv');
		var i = $(this).closest('tr').data('i');

		$(this).closest('tr').remove();

		if (tbody.children('tr').length == 1) {
			tbody.children('tr:first').show();
			form_div.find('.recordsUseLastItemRow').hide();
		}

		if (form_div.is(':visible') && i == form_div.children('.recordEditItem').val() && form_div.children('.recordEditItem').val() != '') {
			form_div.slideUp('fast',function() {
				$('.addItemButton').slideDown('fast');
			});
		}
	});

	$('.editRecordItem').die('click').live('click',function(e) {
		e.preventDefault();

		var data = $(this).closest('tr').data();
		var addRecordItemDiv = $(this).closest('.recordsWidget').find('.addRecordItemDiv');

		RecordsWidget_PopulateAddFormFromData(data, addRecordItemDiv);

		addRecordItemDiv.children('.recordEditItem').val(data['i']);

		$('.addRecordItemDiv').slideDown('fast',function() {
			$(this).closest('.addRecordItemDiv').find('input.recordInput:first').focus();
			$('.addItemButton').slideUp('fast');
		});
	});

	$('.recordsTimeNow').unbind('click').click(function(e) {
		e.preventDefault();

		var d = new Date;

		var h = d.getHours().toString();
		if (h.length <2) {
			h = '0'+h;
		}
		var m = d.getMinutes().toString();
		if (m.length <2) {
			m = '0'+m;
		}

		$(this).prev('.recordTime').val(h+':'+m);
	});

	$('.recordsUseLastItemRow').unbind('click').click(function(e) {
		e.preventDefault();

		var table = $(this).closest('.recordsWidget').find('table');

		var data = table.find('tr:last').data();
		var addRecordItemDiv = $(this).closest('.recordsWidget').find('.addRecordItemDiv');

		RecordsWidget_PopulateAddFormFromData(data, addRecordItemDiv);
	});
});

function RecordsWidget_PopulateAddFormFromData(data, addRecordItemDiv)
{
	for (var field in data) {
		addRecordItemDiv.find('.recordInput[name="'+field+'"]').val(data[field]);
	}

	addRecordItemDiv.find('.recordTimestamp').val(data['timestamp']);
	addRecordItemDiv.find('.recordTime').val(data['time']);
}
