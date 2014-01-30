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
				}
			};

				it ('should get the correct values for the entered fuzzy date', function() {
					for (var fuzzy in testValues) {
						var fdw = fdwInit();
						fdw._entryField.val(fuzzy).trigger('input');
						expect($('#start-date').val()).to.equal(testValues[fuzzy].st);
						expect($('#end-date').val()).to.equal(testValues[fuzzy].en);
					}

				});
		});



		describe('It should set the dates based on age', function() {
			var dobValues = {
				'12/03/1949': {
					'tst': 44,
					'st': '12/03/1993',
					'en': '11/03/1994'
				},
				'01/03/1977':
				{
					'tst': 34,
					'st': '01/03/2011',
					'en': '29/02/2012'
				}
			}

			it ('should get the correct dates by calculating age from different DOBs', function() {
				for (var dob in dobValues) {

					var fdw = fdwInit({
						'dob': dob
					});

					fdw._entryField.val(dobValues[dob].tst).trigger('input');
					expect($('#start-date').val()).to.equal(dobValues[dob].st);
					expect($('#end-date').val()).to.equal(dobValues[dob].en);
				}
			});


		});


	});


});
