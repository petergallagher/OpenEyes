
(function (exports) {

	var NAMESPACE = 'FuzzyDateAndAge';
	var DATE_DELIMITERS = '-\\/ ';
	var MONTH_NAMES = [
		"january", "february", "march",
		"april", "may", "june",
		"july", "august", "september",
		"october", "november", "december"
	];

	function FuzzyDateAndAge(options)
	{
		this.options = $.extend(true, {}, FuzzyDateAndAge._defaultOptions, options);
		this.init();
	}

	FuzzyDateAndAge._defaultOptions = {
		// if dob is provided, will match for age over two digit year
		dob: null,
		dateFormat: 'DD/MM/YYYY',
		startField: 'selector string, or jquery instance, or DOM element',
		endField: 'selector string, or jquery instance, or DOM element',
		yearcutoff: 1920,
		defaultErrorMsg: 'Invalid Fuzzy Date',
		errorSpanClasses: 'fuzzy-date-error',
		rangeSpanClasses: 'fuzzy-date-range',
		containerElement: null

	};

	FuzzyDateAndAge.prototype.init = function()
	{
		var self = this;
		if (!$(self.options.startField).length) {
			throw new Error('startField selector invalid');
		}
		if (!$(self.options.endField).length) {
			throw new Error('endField selector invalid');
		}
		self._startField = $(self.options.startField);
		self._endField = $(self.options.endField);
		if (!self._startField || !self._endField) {
			throw new Error('Require start and end form fields for widget');
		}

		self._entryField = $('<input type="text" />');
		self._endField.after(self._entryField);
		self._errorSpan = $('<span class="' + self.options.errorSpanClasses + '"/>');
		self._entryField.after(self._errorSpan);
		self._errorSpan.hide();
		self._rangeSpan = $('<span class="' + self.options.rangeSpanClasses + '"/>');
		self.setRangeSpan();
		if (self.options.containerElement !== null) {
			self._startField.hide();
			self._endField.hide();

			$(self.options.containerElement).html('');
			$(self.options.containerElement).append(
					self._startField,
					self._endField,
					self._entryField,
					self._errorSpan,
					self._rangeSpan);
		}

		self._entryField.on('input.' + NAMESPACE, function() {
			self.updateValue(self._entryField.val());
		});

		//TODO: need to link date formats correctly on this object
		if (self.options.dob) {
			self._dob = moment(self.options.dob, self.options.dateFormat, true);
			if (!self._dob.isValid()) {
				throw new Error('invalid DOB ' +self.options.dob+' for date format '+self.options.dateFormat);
			}
		}
		else {
			self._dob = null;
		}

		self.setDatePattern();

	}

	FuzzyDateAndAge.prototype.setDatePattern = function() {
		var date_delimiter_set = '[' + DATE_DELIMITERS + ']';
		var day_exp = '(?:(\\d{1,2})' + date_delimiter_set + ')';
		// 1 or 2 digits, or an alphanumeric word
		var month_exp = '(?:((?:\\d{1,2})|(?:[a-z]+))' + date_delimiter_set + ')';
		// 2 or 4 digit number
		var year_exp = '((?:\\d{2})|(?:\\d{4}))';
		if (this._dob) {
			// 1 - 4 digits (to catch an age)
			year_exp = '(\\d{1,4})';
		}

		// only match for the day when a month is present - ensures consistent bracket matching
		var expression = '^(?:' + day_exp + '(?='+month_exp+'))?' +
				month_exp + '?' +
				year_exp + '$';
		
		this._pattern = new RegExp(expression,'i');
	}

	FuzzyDateAndAge.prototype.getFuzzyMatches = function(fuzzyDate) {

		var match = fuzzyDate.match(this._pattern);
		if (match) {
			return {'day': match[1], 'month': match[3], 'year': match[4]};
		}
		return null;
	}

	/**
	 * Updates the start and end date values based on the fuzzyDate parameter
	 * @todo: should the fields be set to empty values if the entry is invalid
	 * @param fuzzyDate
	 */
	FuzzyDateAndAge.prototype.updateValue = function(fuzzyDate) {
		values = this.getFuzzyMatches(fuzzyDate);

		if (values === null) {
			this.invalidValues('unrecognised entry');
			return;
		}
		var start_date, end_date;

		if (this._dob
					&& values.year.length < 4
					&& !values.day) {
			var vals = this.ageCalc(values.month, values.year);
			if (vals) {
				this.setValues(vals[0], vals[1]);
				return;
			}
		}
		// no age/dob match ...
		var year = this.interpretYear(parseInt(values.year));
		var month = this.interpretMonth(values.month);
		if (values.month && month === null) {
			this.invalidValues('invalid month ' + values.month);
			return;
		}

		start_date = moment({year: year, month: 0, day: 1});
		end_date = start_date.clone().date(31);

		//work out the month
		if (month) {
			start_date.month(month-1);
			if (values.day) {
				start_date.date(values.day);
				end_date = start_date.clone();
			}
			else {
				end_date.month(start_date.month());
			}
		}
		else {
			// set to end of the year
			end_date.month(11);
		}
		this.setValues(start_date, end_date);

	}

	FuzzyDateAndAge.prototype.ageCalc = function(start, end) {
		// expect to be looking at age values
		var start_add, end_add;
		if (!start) {
			start_add = parseInt(values.year);
			end_add = start_add+1;
		} else {
			if (parseInt(start)) {
				start_add = parseInt(values.month);
				end_add = parseInt(values.year);
			}
			else {
				return null;
			}
		}
		return [this._dob.clone().add('y', start_add), this._dob.clone().add('y', end_add).subtract('d', 1)];
	}

	FuzzyDateAndAge.prototype.interpretYear = function(year) {
		if (year < 100) {
			year += 1900;
			if (year < this.options.yearcutoff) {
				year+=100;
			}
		}
		return year;
	}

	FuzzyDateAndAge.prototype.interpretMonth = function(month) {
		if (month) {
			numeric = parseInt(month);
			if (numeric > 0 && numeric < 13) {
				return numeric;
			}
			for (mth in MONTH_NAMES) {
				if (month.toLowerCase().substring(0,3) == MONTH_NAMES[mth].substring(0,3)) {
					return parseInt(mth)+1;
				}
			}
		}
		return null;
	}

	FuzzyDateAndAge.prototype.invalidValues = function(reason) {
		this._startField.val('');
		this._endField.val('');
		this.setRangeSpan();
		this.showErrorMessage(reason);
	}

	FuzzyDateAndAge.prototype.showErrorMessage = function(msg) {
		msg = msg || this.options.defaultErrorMsg;
		this._errorSpan.text(msg);
		this._errorSpan.show();
	}

	FuzzyDateAndAge.prototype.setValues = function(st_moment, end_moment) {
		this._startField.val(st_moment.format(this.options.dateFormat));
		this._endField.val(end_moment.format(this.options.dateFormat));
		this.setRangeSpan();
	}

	FuzzyDateAndAge.prototype.setRangeSpan = function() {
		var st = this._startField.val();
		var ed = this._endField.val();
		var range = st;
		if (st != ed) {
			range += ' - ' + ed;
		}
		this._rangeSpan.text(range);
		this._errorSpan.hide();
	}

	/**
	 * OpenEyes UI Widgets namespace
	 * @namespace OpenEyes.UI.Widgets
	 */
	exports.FuzzyDateAndAge = FuzzyDateAndAge;
}(this.OpenEyes.UI.Widgets));