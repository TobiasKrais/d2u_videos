<?php
// save settings
if ('save' === filter_input(INPUT_POST, 'btn_save')) {
    $settings = rex_post('settings', 'array', []);

    // Special treatment for media fields
    $input_media = rex_post('REX_INPUT_MEDIA', 'array', []);
    $settings['player_js'] = $input_media['player_js'];

    // Save settings
    if (rex_config::set('d2u_videos', $settings)) {
        echo rex_view::success(rex_i18n::msg('form_saved'));
    } else {
        echo rex_view::error(rex_i18n::msg('form_save_error'));
    }
}
?>
<form action="<?= rex_url::currentBackendPage() ?>" method="post">
	<div class="panel panel-edit">
		<header class="panel-heading"><div class="panel-title"><?= rex_i18n::msg('d2u_helper_settings') ?></div></header>
		<div class="panel-body">
			<fieldset>
				<legend><small><i class="rex-icon rex-icon-language"></i></small> <?= rex_i18n::msg('d2u_helper_settings') ?></legend>
				<div class="panel-body-wrapper slide">
					<?php
                        $player_options = [
                            'ultimate' => rex_i18n::msg('d2u_videos_settings_ultimate'),
                            'plyr' => rex_i18n::msg('d2u_videos_settings_plyr') .(rex_addon::get('plyr')->isAvailable() ? '' : ' '. rex_i18n::msg('d2u_videos_settings_plyr_install')),
                        ];
                        \TobiasKrais\D2UHelper\BackendHelper::form_select('d2u_videos_settings_player', 'settings[player]', $player_options, [(string) rex_config::get('d2u_videos', 'player')]);

                        // Fields only for ultimate video player
                        \TobiasKrais\D2UHelper\BackendHelper::form_mediafield('d2u_videos_player_file', 'player_js', (string) rex_config::get('d2u_videos', 'player_js'));
                        \TobiasKrais\D2UHelper\BackendHelper::form_input('d2u_videos_max_height', 'settings[max_height]', (string) rex_config::get('d2u_videos', 'max_height'), false, false, 'number');
                        \TobiasKrais\D2UHelper\BackendHelper::form_input('d2u_videos_max_width', 'settings[max_width]', (string) rex_config::get('d2u_videos', 'max_width'), false, false, 'number');
                    ?>
					<script>
						function player_type_changer(value) {
							if (value === "ultimate") {
								$("dl[id='MEDIA_player_js']").fadeIn();
								$("dl[id='settings[max_height]']").fadeIn();
								$("dl[id='settings[max_width]']").fadeIn();
							}
							else {
								$("dl[id='MEDIA_player_js']").hide();
								$("dl[id='settings[max_height]']").hide();
								$("dl[id='settings[max_width]']").hide();
							}
						}

						// Hide on document load
						$(document).ready(function() {
							player_type_changer($("select[name='settings[player]']").val());
						});

						// Hide on selection change
						$("select[name='settings[player]']").on('change', function(e) {
							player_type_changer($(this).val());
						});
					</script>
				</div>
			</fieldset>
		</div>
		<footer class="panel-footer">
			<div class="rex-form-panel-footer">
				<div class="btn-toolbar">
					<button class="btn btn-save rex-form-aligned" type="submit" name="btn_save" value="save"><?= rex_i18n::msg('form_save') ?></button>
				</div>
			</div>
		</footer>
	</div>
</form>
<?php
    echo \TobiasKrais\D2UHelper\BackendHelper::getCSS();
    echo \TobiasKrais\D2UHelper\BackendHelper::getJS();
    echo \TobiasKrais\D2UHelper\BackendHelper::getJSOpenAll();
