<?php
// save settings
if (filter_input(INPUT_POST, "btn_save") == 'save') {
	$settings = (array) rex_post('settings', 'array', []);

	// Special treatment for media fields
	$input_media = (array) rex_post('REX_INPUT_MEDIA', 'array', array());
	$settings['player_js'] = $input_media['player_js'];

	// Save settings
	if(rex_config::set("d2u_videos", $settings)) {
		echo rex_view::success(rex_i18n::msg('form_saved'));
	}
	else {
		echo rex_view::error(rex_i18n::msg('form_save_error'));
	}
}
?>
<form action="<?php print rex_url::currentBackendPage(); ?>" method="post">
	<div class="panel panel-edit">
		<header class="panel-heading"><div class="panel-title"><?php print rex_i18n::msg('d2u_helper_settings'); ?></div></header>
		<div class="panel-body">
			<fieldset>
				<legend><small><i class="rex-icon rex-icon-language"></i></small> <?php echo rex_i18n::msg('d2u_helper_settings'); ?></legend>
				<div class="panel-body-wrapper slide">
					<?php
						d2u_addon_backend_helper::form_mediafield('d2u_videos_player_file', 'player_js', $this->getConfig('player_js'));
						d2u_addon_backend_helper::form_input('d2u_videos_max_height', 'settings[max_height]', $this->getConfig('max_height'), FALSE, FALSE, "number");
						d2u_addon_backend_helper::form_input('d2u_videos_max_width', 'settings[max_width]', $this->getConfig('max_width'), FALSE, FALSE, "number");
					?>
				</div>
			</fieldset>
		</div>
		<footer class="panel-footer">
			<div class="rex-form-panel-footer">
				<div class="btn-toolbar">
					<button class="btn btn-save rex-form-aligned" type="submit" name="btn_save" value="save"><?php echo rex_i18n::msg('form_save'); ?></button>
				</div>
			</div>
		</footer>
	</div>
</form>
<?php
	print d2u_addon_backend_helper::getCSS();
	print d2u_addon_backend_helper::getJS();
	print d2u_addon_backend_helper::getJSOpenAll();