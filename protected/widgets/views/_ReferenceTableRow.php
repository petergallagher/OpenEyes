<div class="row data-row reference-table-row">
	<div class="large-1 column"><span>&uarr;&darr;</span></div>
	<div class="large-9 column"><div class="data-value">
			<?php
			$readOnly='';
			if(!isset($id)) {
				$date = new DateTime();
				$id = 'rtn-'.$date->getTimestamp();
			}
			else{
				$id = 'rto-'.$id;
				$readOnly = ' readonly';
			}
			echo "<input name='".$id."' class='reference-table-inputs' type=text value='".$name."'".$readOnly."/>";
			?>
		</div>
	</div>
	<div class="large-1 column end del"><?php if (!$readOnly) { ?><span><a href="#">Remove</a></span><?php } ?></div>
</div>
<script>
	$('.insert').unbind().click(function() {
		event.preventDefault();
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