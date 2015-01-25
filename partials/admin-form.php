<div class="wrap wp-<?php echo $pluginId; ?>-settings">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2><?php echo $pluginName.' '.__('options','fortpolio'); ?></h2>

	<?php echo $errors; ?>

	<div class="postbox-container main" style="width:65%;">
		<div class="metabox-holder">
			<div class="meta-box-sortables ui-sortable">
				<p style="max-width:700px;">_explainFortpolio</p>
				<form method="post" action="options.php">
					<?php
	settings_fields(FPL_SETTINGS);
	do_settings_sections(FPL_PAGE);
					?>
					<p>
						<br><input type="submit" name="submit" class="button-primary" value="Save changes" title="Ctrl+§§§S or Cmd+S to click">
					</p>
				</form>
			</div>
		</div>
	</div>
	<div class="postbox-container side" style="width:20%;">
		<div class="metabox-holder">
			<div class="meta-box-sortables ui-sortable">
				<div id="donate" class="postbox">
					<h3 class="hndle"><span><strong class="red">Fortpolio 1.1.0</strong></span></h3>

					<div class="inside"><strong>If you like Fortpolio:</strong>

						<p><a href="http://wordpress.org/extend/plugins/fortpolio/" target="wp">Please rate it</a></p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>