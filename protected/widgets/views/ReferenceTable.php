
<?php
$form = $this->beginWidget('BaseEventTypeCActiveForm', array(
		'id'=>'referenceTable',
		'enableAjaxValidation'=>false,
		'layoutColumns' => array(
				'label' => 2,
				'field' => 5
		)
))?>
<div class="element-data">
	<div class="large-6 column end">
		<div class ="data-row  reference-table-edit">
			<?php
			foreach ($data as $row){
			$this->controller->renderFile(Yii::app()->basePath.'/widgets/views/_ReferenceTableRow.php',array('name'=>$row['name'],'id'=>$row['id']));
			}
			?>
		</div>
		<div class ="data-row add">
		<span><a href="#">Add</a></span>
		</div>
	</div>
</div>
<input class='reference-table-array' type=hidden value=''>
<BR />
<div>
	<?php echo EventAction::button('Save', 'save', null, array('class' => 'save-reference-table small primary'))->toHtml()?>&nbsp;
	<?php echo EventAction::button('Cancel', 'Cancel', null, array('class' => 'small warning'))->toHtml()?>
</div>

<script>
	$('.add').unbind().click(function() {
		event.preventDefault();
		var div = $('.reference-table-edit');
		$.ajax({
			'type': 'GET',
			'url': baseUrl+'/admin/addReferenceTableRow',
			'success': function(html) {
				div.append(html);
			}
		});
	});
	$('.save-reference-table').click(function() {
		$('.reference-table-array').val($('input').serialize());
	});

	$('.reference-table-edit').sortable({helper:'clone'});
</script>

<?php $this->endWidget(); ?>