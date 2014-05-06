<div class="row data-row reference-table-row">
	<div class="large-1 column"><span>Drg</span></div>
	<div class="large-9 column"><div class="data-value">
			<?php
			if(!isset($id)) {
				$date = new DateTime();
				$id = 'rtn-'.$date->getTimestamp();
			}
			else{
				$id = 'rto-'.$id;
			}
			echo "<input name='".$id."' class='reference-table-inputs' type=text value='".$name."' />";
			?>
		</div>
	</div>
	<div class="large-1 column end insert"><span>Ins</span></div>
	<div class="large-1 column end del"><span>Del</span></div>
</div>

<script>
	$('.insert').unbind().click(function() {
		var div = $(this).closest('.reference-table-row');
		$.ajax({
			'type': 'GET',
			'url': baseUrl+'/admin/addReferenceTableRow',
			'success': function(html) {
				$(html).insertBefore(div);
			}
		});
	});
	$('.del').unbind().click(function() {
		$(this).closest('.reference-table-row').remove();
	});
</script>