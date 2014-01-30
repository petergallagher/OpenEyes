describe('OpenEyes.UI.Widgets.FuzzyDateAndAge', function(){
	describe('Namespace', function(){
		it('should create a "Widgets" namespace on the "UI" namespace', function(){
			expect(typeof OpenEyes.UI.Widgets.FuzzyDateAndAge).to.equal('function');
		})
	})

	describe('With HTML Form', function() {

		beforeEach(function() {
			var el = document.createElement('div');
			el.id = 'fuzzy-date-test';
			document.body.appendChild(el);
			var start_date = document.createElement('input');
			start_date.type = 'text';
			start_date.id = 'start-date';
			el.appendChild(start_date);
			var end_date = document.createElement('input');
			end_date.type = 'text';
			end_date.id = 'end-date';
			el.appendChild(end_date);
		});

		afterEach(function() {
			var el = document.getElementById('fuzzy-date-test');
			el.parentElement.removeChild(el);
		});

		it ('should throw an exception when missing start field to attach to', function() {
			var test1 = function() {
				var widget = new OpenEyes.UI.Widgets.FuzzyDateAndAge();
			}
			expect(test1).to.throw(Error);
		})


		// initialisation function
		var fdwInit = function(options) {
			var minimum = {
				'startField': $('#start-date'),
				'endField': $('#end-date')
			};
			opts = $.extend(true, {}, minimum, options);

			var widget = new OpenEyes.UI.Widgets.FuzzyDateAndAge(opts);
			return widget;
		}

		it ('should set up the form correctly when initialised', function() {
			var fdw = fdwInit();

			//expect($('#start-date').is(':visible')).to.equal(false);
			//expect($('#end-date').is(':visible')).to.equal(false);
			expect(fdw._entryField.is(':visible')).to.equal(true);

		})

		describe('Testing Fuzzy Date matches', function() {
			var testValues = {
				'1/12/03': {
					'st': '01/12/2003',
					'en': '01/12/2003'
				},
				'March 79': {
					'st': '01/03/1979',
					'en': '31/03/1979'
				},
				'Feb 2012': {
					'st': '01/02/2012',
					'en': '29/02/2012'
				},
				'47': {
					'st': '01/01/1947',
					'en': '31/12/1947'
				},
				'05/24/81': {
					'st': '',
					'en': '',
					'err': 'invalid month 24'
				}
			};

			for (var fuzzy in testValues) {
				// because tests get executed later, need to define a scope enclosure for each entry
				// in the testValues array
				(function(fuzzy) {
					it ('should get the correct values for the entered fuzzy date ' + fuzzy, function() {

						var fdw = fdwInit();
						fdw._entryField.val(fuzzy).trigger('input');
						expect($('#start-date').val()).to.equal(testValues[fuzzy].st);
						expect($('#end-date').val()).to.equal(testValues[fuzzy].en);
						if (testValues[fuzzy].err) {
							expect(fdw._errorSpan.is(":visible")).to.equal(true);
							expect(fdw._errorSpan.text()).to.equal(testValues[fuzzy].err);
						}
						else {
							expect(fdw._errorSpan.is(":visible")).to.equal(false);
						}

					});
				})(fuzzy);
			}

		});

		describe('Testing fuzzy date and age matches', function() {
			var dobValues = {
				'12/03/1949': [{
					'tst': 44,
					'st': '12/03/1993',
					'en': '11/03/1994'
				}],
				'01/03/1977':
				[{
					'tst': 34,
					'st': '01/03/2011',
					'en': '29/02/2012'
				},
					{
						'tst': '3-5',
						'st': '01/03/1980',
						'en': '28/02/1982'
					}],
				'02/10/1965':
				[
					{
						'tst': '30-35',
						'st': '02/10/1995',
						'en': '01/10/2000'
					},
					{
						'tst': 'apr 78',
						'st': '01/04/1978',
						'en': '30/04/1978'
					},
					{
						'tst': '2001-2010',
						'st': '01/01/2001',
						'en': '31/12/2010'
					}
				]
			};

			for (var dob in dobValues) {
				for (var i = 0; i < dobValues[dob].length; i++) {
					test = dobValues[dob][i];

					(function(dob, test) {
						it ('should get the right dates for entering ' + test.tst + ' with dob ' + dob, function() {
							var fdw = fdwInit({
								'dob': dob
							});
							fdw._entryField.val(test.tst).trigger('input');
							expect($('#start-date').val()).to.equal(test.st);
							expect($('#end-date').val()).to.equal(test.en);
						});

					})(dob, test)

				}

			}

		});


	});


});
