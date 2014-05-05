<div class="element-data">
	<div class="large-6 column end">
		<div class ="data-row  reference-table-edit">
			<?php
			foreach ($data as $row){
			$this->controller->renderFile(Yii::app()->basePath.'/widgets/views/_ReferenceTableRow.php',array('name'=>$row['name']));
			}
			?>
		</div>
		<div class ="data-row add">
		<span>Add</span>
		</div>
	</div>
</div>
<BR />
<div>
	<?php echo EventAction::button('Save', 'save', null, array('class' => 'small secondary'))->toHtml()?>&nbsp;
	<?php echo EventAction::button('Cancel', 'Cancel', null, array('class' => 'small warning'))->toHtml()?>
</div>

<script>
	$('.add').unbind().click(function() {
		var div = $('.reference-table-edit');
		$.ajax({
			'type': 'GET',
			'url': baseUrl+'/admin/addReferenceTableRow',
			'success': function(html) {
				div.append(html);
			}
		});
	});
	$('.reference-table-edit').sortable({helper:'clone'});
</script>