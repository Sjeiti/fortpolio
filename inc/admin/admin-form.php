<div class="wrap wp-<?php echo $pluginId; ?>-settings">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2><?php echo $pluginName.' '.__('options','fortpolio'); ?></h2>

	<?php echo $errors; ?>

	<div class="postbox-container main" style="width:65%;">
		<div class="metabox-holder">
			<div class="meta-box-sortables ui-sortable">
				<p style="max-width:700px;"><?php _e('_explainFortpolio','fortpolio') ?></p>
				<p style="max-width:700px;"><?php _e('_explainTemplates','fortpolio') ?></p>
				<form method="post" action="options.php"><?php
					settings_fields(FPL_SETTINGS);
					do_settings_sections(FPL_PAGE);
					?><p>
						<br><input type="submit" name="submit" class="button-primary" value="Save changes" title="Ctrl+§§§S or Cmd+S to click">
					</p>
				</form>
			</div>
		</div>
	</div>
	<div class="postbox-container side" style="width:20%;">
		<div class="metabox-holder">
			<div class="meta-box-sortables ui-sortable"><?php
				$this->template('like.php',array(
					'pluginName'=>$this->sPluginName
					,'pluginVersion'=>$this->sVersion
					,'pluginUri'=>$this->sPluginWpUri
				),'',$this->sAdminTemplates);
				?>
			</div>
		</div>
	</div>
</div>