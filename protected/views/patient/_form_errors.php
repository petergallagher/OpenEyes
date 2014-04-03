<div class="alert-box alert with-icon"<?php if (empty($errors)) {?> style="display: none"<?php }?>>
	<p>Please fix the following input errors:</p>
	<ul>
		<?php if (!empty($errors)) {
			foreach ($errors as $field => $errs) {
				foreach ($errs as $err) {?>
					<li>
						<?php echo $err?>
					</li>
				<?php }
			}
		}?>
	</ul>
</div>
