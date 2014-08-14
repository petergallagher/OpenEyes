$(document).ready(function() {

	var sortTable = (function() {

		function getColVal(row, col) {
			return $.trim(
				$(row).find('td:eq(' + col + ')').text()
			);
		}

		function getTime(row, col) {
			return (new Date(getColVal(row, col))).getTime();
		}

		function getType(row, col, type) {
			switch(type) {
				case 'date':
					return getTime(row, col);
				default:
					return 1;
			}
		}

		function sortRows(table, col, type) {
			var rows = table.find('tbody tr:visible');
			rows.sort(function(rowA, rowB) {
				return Number(
					getType(rowA, col, type) > getType(rowB, col, type)
				);
			});
			rows.each(function(i, row) {
				table.append(row);
			});
		}

		return sortRows;
	}());

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
		addRecordItemDiv.find('input.recordInput').val('');
		addRecordItemDiv.find('select.recordInput').val('');
		addRecordItemDiv.find('textarea.recordInput').val('');
		addRecordItemDiv.find('.multi-select-selections').children('li').children('.MultiSelectRemove').click();

		addRecordItemDiv.find('select.recordInput[data-cycle-on-add="1"]').map(function() {
			var name = $(this).attr('name');
			var values = [];
			$(this).closest('.recordsWidget').find('.recordsTable').children('tbody').children('tr').map(function() {
				values.push($(this).attr('data-' + name));
			});

			var all_values = [];
			$(this).children('option').map(function() {
				all_values.push($(this).val());
			});

			for (var i in all_values) {
				if ($.inArray(all_values[i],values) == -1) {
					$(this).val(all_values[i]);
					break;
				}
			}
		});

		addRecordItemDiv.children('.recordEditItem').val('');
		addRecordItemDiv.children('.recordEditItemID').val('');

		addRecordItemDiv.slideDown('fast',function() {
			var input = $(this).closest('.addRecordItemDiv').find('input.recordInput[type="text"]:first');
			if (input.length >0) {
				input.focus();
			} else {
				$(this).closest('.addRecordItemDiv').find('textarea.recordInput:first').focus();
			}
			addRecordItemDiv.next('.addItemButton').slideUp('fast');
		});
	});

	$('.cancelRecordItem').unbind('click').click(function(e) {
		e.preventDefault();

		var form_div = $(this).closest('.recordsWidget').find('.addRecordItemDiv');
		var error_div = form_div.find('.recordItemErrorsDiv');
		var error_list = error_div.find('.recordItemErrors');

		form_div.slideUp('fast',function() {
			form_div.next('.addItemButton').slideDown('fast',function() {
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
		var sort = $(this).data('sort-after-save');
		var data = 'timestamp=' + form_div.find('.recordTimestamp').val() + '&time=' + form_div.find('.recordTime').val();

		form_div.find('.recordInput').map(function() {
			data += '&' + $(this).attr('name') + '=' + $(this).val();
		});

		data += '&patient_id=' + OE_patient_id;

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
						if (sort) {
							sortTable(table, sort.column, sort.type);
						}
						form_div.find('.recordsUseLastItemRow').show();
					} else {
						table.children('tbody').children('tr[data-i="'+form_div.children('.recordEditItem').val()+'"]').replaceWith(errors['row']);
					}

					form_div.find('.multi-select-selections').children('li').children('.MultiSelectRemove').map(function() {
						$(this).click();
					});

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
		var button = form_div.find('.addItemButton');

		$(this).closest('tr').remove();

		if (tbody.children('tr').length == 1) {
			tbody.children('tr:first').show();
			form_div.find('.recordsUseLastItemRow').hide();
		}

		if (form_div.is(':visible') && i == form_div.children('.recordEditItem').val() && form_div.children('.recordEditItem').val() != '') {
			form_div.slideUp('fast',function() {
				button.slideDown('fast');
			});
		}
	});

	$('.editRecordItem').die('click').live('click',function(e) {
		e.preventDefault();

		var data = $(this).closest('tr').data();
		var addRecordItemDiv = $(this).closest('.recordsWidget').find('.addRecordItemDiv');
		var button = $(this).closest('.recordsWidget').find('.addItemButton');
		addRecordItemDiv.find('.multi-select-selections').children('li').children('.MultiSelectRemove').click();

		RecordsWidget_PopulateAddFormFromData(data, addRecordItemDiv);

		addRecordItemDiv.children('.recordEditItem').val(data['i']);
		addRecordItemDiv.children('.recordEditItemID').val(data['id']);

		addRecordItemDiv.slideDown('fast',function() {
			addRecordItemDiv.find('input.recordInput:first').focus();
			button.slideUp('fast');
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

		$(this).closest('div').find('.recordTime').val(h+':'+m);
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
		if (typeof(data[field]) == 'object') {
			for (var object_field in data[field]) {
				addRecordItemDiv.find('.recordInput[name="'+field+'['+object_field+']"]').val(data[field][object_field]);
			}
		} else {
			addRecordItemDiv.find('.recordInput[name="'+field+'"]').val(data[field]);
		}
	}

	addRecordItemDiv.find('.recordTimestamp').val(data['timestamp']);
	addRecordItemDiv.find('.recordTime').val(data['time']);

	if (typeof(data['multiselectFields']) != 'undefined') {
		var multiselect_fields = data['multiselectFields'].split(',');

		for (var i in multiselect_fields) {
			var values = data[multiselect_fields[i]].toString().split(',');

			for (var j in values) {
				if (typeof(data['multiselectExtrafields_' + multiselect_fields[i]]) != 'undefined') {
					var extra_values = data[data['multiselectExtrafields_' + multiselect_fields[i]]].toString().split(',');

					MultiSelect_SelectItem($('#'+multiselect_fields[i]),$('#'+multiselect_fields[i]).children('option[value="' + values[j] + '"]'),[extra_values[j]]);
				} else {
					MultiSelect_SelectItem($('#'+multiselect_fields[i]),$('#'+multiselect_fields[i]).children('option[value="' + values[j] + '"]'));
				}
			}
		}
	}
}
