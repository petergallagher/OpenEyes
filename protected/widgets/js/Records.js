
$(document).ready(function() {
	$('.addRecordItem').click(function(e) {
		e.preventDefault();

		$('.addRecordItemDiv').slideDown('fast',function() {
			$('.addItemButton').slideUp('fast');
		});
	});

	$('.cancelRecordItem').unbind('click').click(function(e) {
		e.preventDefault();

		$('.addRecordItemDiv').slideUp('fast',function() {
			$('.addItemButton').slideDown('fast');
		});
	});
});
