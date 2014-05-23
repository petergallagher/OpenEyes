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

function ProcedureSelection() {if (this.init) this.init.apply(this, arguments); }

ProcedureSelection.prototype = {
	init : function(params) {
		for (var key in params) {
			this[key] = params[key];
		}

		this.subSectionSelect = $('select.subSectionSelect[data-element="'+this.element+'"][data-field="'+this.field+'"]');
		this.procedureSelect = $('select.procedureSelect[data-element="'+this.element+'"][data-field="'+this.field+'"]');
		this.procedureList = $('.ProcedureSelectionProcedureList[data-element="'+this.element+'"][data-field="'+this.field+'"]');
		this.searchBox = $('input[type="text"][name="autocomplete_'+this.element+'_'+this.field+'"]');
		this.calculatedTotalDuration = $('.ProcedureSelectionProjectedDuration[data-element="'+this.element+'"][data-field="'+this.field+'"]');
		this.estimatedTotalDuration = $('.ProcedureSelectionEstimatedDuration[data-element="'+this.element+'"][data-field="'+this.field+'"]');
	},

	updateSubSection : function(subsection_id) {
		var us = this;

		if (subsection_id != '') {
			$.ajax({
				'url': baseUrl+'/procedure/list',
				'type': 'POST',
				'data': {
					subsection: subsection_id,
					YII_CSRF_TOKEN: YII_CSRF_TOKEN
				},
				'success': function(data) {
					us.procedureSelect.attr('disabled',false);
					us.procedureSelect.html(data);
					us.procedureSelect.children('option').map(function() {
						var obj = $(this);

						$.each(us.selected_procedures, function(key, value) {
							if (value["id"] == obj.val()) {
								obj.remove();
							}
						});
					});

					us.procedureSelect.parent().show();
				}
			});
		} else {
			us.procedureSelect.parent().hide();
		}
	},

	filterSearchResults : function(data) {
		var result = [];

		this.autoCompleteCache = data;

		for (var i = 0; i < data.length; i++) {
			if (!this.procedureSelected(data[i].id)) {
				result.push(data[i].term);
			}
		}

		return result;
	},

	procedureSelected : function(proc_id) {
		for (var i = 0; i < this.selected_procedures.length; i++) {
			if (this.selected_procedures[i].id == proc_id) {
				return true;
			}
		}

		return false;
	},

	selectProcedure : function(proc) {
		if (!this.verifyProcedureSelection(proc.id)) {
			this.procedureSelect.val('');
			this.searchBox.val('');
			return;
		}

		this.selected_procedures.push(proc);
		this.searchBox.val('');
		this.removeFromProcedureDropdown(proc);

		this.procedureList.find('.body').append(
			'<tr class="item" data-proc-id="' + proc.id + '">' +
			'	<td class="procedure">' +
			'		<span class="field"><input type="hidden" name="' + this.element + '[' + this.field + '][]" value="' + proc.id + '" /></span>' +
			'		<span class="value">' + proc.term + '</span>' +
			'	</td>' + (
				this.durations ?
					'<td class="duration">' + proc.default_duration + ' mins</td>'
					: false
				) +
			'<td><a href="#" class="removeProcedure" data-element="' + this.element + '" data-field="' + this.field + '" data-proc-id="' + proc.id + '">Remove</a></td></tr>');

		this.procedureList.show();

		if (this.durations) {
			this.updateTotalDuration();
			this.procedureList.find('.durations').show();
		}

		if (typeof(window.callbackAddProcedure) == 'function') {
			callbackAddProcedure(proc.id);
		}
	},

	selectProcedureFromDropdown : function(proc_id, proc_name, default_duration) {
		this.selectProcedure({
			id: proc_id,
			term: proc_name,
			default_duration: default_duration,
			is_common: true,
		});
	},

	removeFromProcedureDropdown : function(proc) {
		this.procedureSelect.children('option').map(function() {
			if ($(this).val() == proc.id) {
				$(this).remove();
			}
		});
	},

	findProcFromCache : function(proc_name) {
		for (var i = 0; i < this.autoCompleteCache.length; i++) {
			if (this.autoCompleteCache[i].term == proc_name) {
				return this.autoCompleteCache[i];
			}
		}

		return false;
	},

	verifyProcedureSelection : function(proc_id) {
		if (typeof(window.callbackVerifyAddProcedure) == 'function') {
			window.callbackVerifyAddProcedure(proc_id,this.durations,function(result) {
				if (result != true) {
					return false;
				}
			});
		}

		return true;
	},

	updateTotalDuration : function() {
		var totalDuration = 0;

		for (var i = 0; i < this.selected_procedures.length; i++) {
			totalDuration += parseInt(this.selected_procedures[i].default_duration);
		}

		this.calculatedTotalDuration.text(totalDuration+' mins');
		this.estimatedTotalDuration.val(totalDuration);
	},

	removeProcedure : function(proc_id) {
		this.procedureList.find('tr.item[data-proc-id="' + proc_id + '"]').remove();

		if (this.procedureList.find('tbody').children('tr').length == 0) {
			this.procedureList.hide();
		}

		var new_selected_procedures = [];

		for (var i = 0; i < this.selected_procedures.length; i++) {
			if (this.selected_procedures[i].id != proc_id) {
				new_selected_procedures.push(this.selected_procedures[i]);
			} else if (this.selected_procedures[i].is_common) {
				this.addToProcedureDropdown(this.selected_procedures[i]);
			}
		}

		this.selected_procedures = new_selected_procedures;

		this.updateTotalDuration();

		if (typeof(window.callbackRemoveProcedure) == 'function') {
			callbackRemoveProcedure(proc_id);
		}
	},

	addToProcedureDropdown : function(proc) {
		this.procedureSelect.append('<option value="' + proc.id + '" data-default-duration="' + proc.default_duration + '">' + proc.term + '</option>');
		sort_selectbox(this.procedureSelect);
	}
}

$(document).ready(function() {
	$('div.procedure-selection .subSectionSelect').die('change').live('change',function(e) {
		window['ProcedureSelection_'+$(this).attr('data-element')+'_'+$(this).attr('data-field')].updateSubSection($(this).val());
	});
	$('div.procedure-selection .procedureSelect').die('change').live('change',function(e) {
		if ($(this).val() != '') {
			window['ProcedureSelection_'+$(this).attr('data-element')+'_'+$(this).attr('data-field')].selectProcedureFromDropdown($(this).val(),$(this).children('option:selected').text(),$(this).children('option:selected').data('default-duration'));
		}
	});
	$('div.procedure-selection .removeProcedure').die('click').live('click',function(e) {
		e.preventDefault();
		window['ProcedureSelection_'+$(this).attr('data-element')+'_'+$(this).attr('data-field')].removeProcedure($(this).data('proc-id'));
	});
});
