
<table id="<?php echo $id?>">
	<?php
	$row_number=0;
	foreach ($Options['data'] as $row){
	$row_number++;
	?>
	<tr id="<?php echo $row_number?>">
		<?php
		$cell_number=0;
		foreach ($row as $cell) {
		$cell_number++;
		if( $row_number==1 && $cell_number==1 && @$Options['horizontal-headers'] && @$Options['vertical-headers']) {
			?>
			<th></th>
			<?php
			$cell_number++;
		}
			$attributes = 'id="'.$cell_number.'" class="'.$id.'-col-'.$cell_number.' '.$id.'-row-'.$row_number.'" data-column="'.$cell_number.'" data-row="'.$row_number.'"';
			if(($cell_number==1 && @$Options['vertical-headers'])||($row_number==1 &&  @$Options['horizontal-headers'])){
				?>
				<th <?php echo $attributes?>>
					<?php echo $cell?>
				</th>
			<?php } else {
				?>
				<td <?php echo $attributes?>>
					<?php echo $cell?>
					<input type="hidden" <?php echo 'id="col-'.$cell_number.'-row-'.$row_number."'"?> value="<?php echo CHTML::encode($cell)?>" />
				</td>
			<?php }?>
		<?php }?>
	</tr>
<?php }?>
</table>