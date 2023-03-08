<?php
$func = rex_request('func', 'string');
$entry_id = (int) rex_request('entry_id', 'int');
$message = rex_get('message', 'string');

// Print comments
if ('' !== $message) {
    echo rex_view::success(rex_i18n::msg($message));
}

// save settings
if (1 === (int) filter_input(INPUT_POST, 'btn_save') || 1 === (int) filter_input(INPUT_POST, 'btn_apply')) {
    $form = rex_post('form', 'array', []);

    // Media fields and links need special treatment
    $input_media = rex_post('REX_INPUT_MEDIA', 'array', []);

    $success = true;
    $video = false;
    $video_id = $form['video_id'];
    foreach (rex_clang::getAll() as $rex_clang) {
        if (false === $video) {
            $video = new Video($video_id, $rex_clang->getId(), false);
            $video->video_id = $video_id; // Ensure correct ID in case first language has no object
            $video->picture = $input_media[1];
            $video->priority = $form['priority'];
            $video->video_type = $form['video_type'];
            $video->redaxo_file = $input_media[2];
            $video->youtube_video_id = $form['youtube_video_id'];
        } else {
            $video->clang_id = $rex_clang->getId();
        }
        $video->name = $form['lang'][$rex_clang->getId()]['name'];
        $video->teaser = $form['lang'][$rex_clang->getId()]['teaser'];
        $video->video_type_lang = null !== $form['video_type_lang'] ? $form['video_type_lang'] : '';
        $video->redaxo_file_lang = $input_media['1'. $rex_clang->getId()];
        $video->youtube_video_id_lang = $form['lang'][$rex_clang->getId()]['youtube_video_id_lang'];
        $video->translation_needs_update = $form['lang'][$rex_clang->getId()]['translation_needs_update'];

        if ('delete' === $video->translation_needs_update) {
            $video->delete(false);
        } elseif ($video->save() > 0) {
            $success = false;
        } else {
            // remember id, for each database lang object needs same id
            $video_id = $video->video_id;
        }
    }

    // message output
    $message = 'form_save_error';
    if ($success) {
        $message = 'form_saved';
    }

    // Redirect to make reload and thus double save impossible
    if (1 === filter_input(INPUT_POST, 'btn_apply') && false !== $video) {
        header('Location: '. rex_url::currentBackendPage(['entry_id' => $video->video_id, 'func' => 'edit', 'message' => $message], false));
    } else {
        header('Location: '. rex_url::currentBackendPage(['message' => $message], false));
    }
    exit;
}
// Delete
if (1 === filter_input(INPUT_POST, 'btn_delete') || 'delete' === $func) {
    $video_id = $entry_id;
    if (0 === $video_id) {
        $form = rex_post('form', 'array', []);
        $video_id = $form['video_id'];
    }
    $video = new Video($video_id, (int) rex_config::get('d2u_helper', 'default_lang'), false);
    $video->video_id = $video_id; // Ensure correct ID in case first language has no object
    $playlists = $video->getPlaylists();
    if (count($playlists) > 0) {
        $message = '<ul>';
        foreach ($playlists as $playlist) {
            $message .= '<li><a href="index.php?page=d2u_videos/playlist&func=edit&entry_id='. $playlist->playlist_id .'">'. $playlist->name.'</a></li>';
        }
        $message .= '</ul>';

        echo rex_view::error(rex_i18n::msg('d2u_helper_could_not_delete') . $message);
    } else {
        $video->delete();
    }

    $func = '';
}

// Form
if ('edit' === $func || 'add' === $func) {
?>
	<form action="<?= rex_url::currentBackendPage() ?>" method="post">
		<div class="panel panel-edit">
			<header class="panel-heading"><div class="panel-title"><?= rex_i18n::msg('d2u_videos') ?></div></header>
			<div class="panel-body">
				<input type="hidden" name="form[video_id]" value="<?= $entry_id ?>">
				<fieldset>
					<legend><?= rex_i18n::msg('d2u_helper_data_all_lang') ?></legend>
					<div class="panel-body-wrapper slide">
						<?php
                            // Do not use last object from translations, because you don't know if it exists in DB
                            $video = new Video($entry_id, (int) rex_config::get('d2u_helper', 'default_lang'), false);
                            $readonly = true;
                            if (\rex::getUser() instanceof rex_user && (\rex::getUser()->isAdmin() || \rex::getUser()->hasPerm('d2u_videos[edit_data]'))) {
                                $readonly = false;
                            }

                            d2u_addon_backend_helper::form_input('header_priority', 'form[priority]', (string) $video->priority, true, $readonly, 'number');
                            d2u_addon_backend_helper::form_mediafield('d2u_videos_picture', '1', $video->picture, $readonly);
                            $options_link = [
                                'redaxo' => rex_i18n::msg('d2u_videos_videotype_mp4'),
                                'youtube' => rex_i18n::msg('d2u_videos_videotype_youtube'),
                            ];
                            d2u_addon_backend_helper::form_select('d2u_videos_videotype', 'form[video_type]', $options_link, [$video->video_type], 1, false, $readonly);
                            d2u_addon_backend_helper::form_mediafield('d2u_videos_redaxo_file', '2', $video->redaxo_file, $readonly);
                            d2u_addon_backend_helper::form_input('d2u_videos_youtube_video_id', 'form[youtube_video_id]', $video->youtube_video_id, false, $readonly, 'text');
                        ?>
						<script>
							function changeType() {
								if($('select[name="form\\[video_type\\]"]').val() === "youtube") {
									$('#MEDIA_2').hide();
									$('#form\\[youtube_video_id\\]').show();
								}
								else {
									$('#MEDIA_2').show();
									$('#form\\[youtube_video_id\\]').hide();
								}
							}

							// On init
							changeType();
							// On change
							$('select[name="form\\[video_type\\]"]').on('change', function() {
								changeType();
							});
						</script>
					</div>
				</fieldset>
				<?php
                    foreach (rex_clang::getAll() as $rex_clang) {
                        $video = new Video($entry_id, $rex_clang->getId(), false);
                        $required = $rex_clang->getId() === (int) (rex_config::get('d2u_helper', 'default_lang')) ? true : false;

                        $readonly_lang = true;
                        if (\rex::getUser() instanceof rex_user && (\rex::getUser()->isAdmin() || (\rex::getUser()->hasPerm('d2u_videos[edit_lang]') && \rex::getUser()->getComplexPerm('clang') instanceof rex_clang_perm && \rex::getUser()->getComplexPerm('clang')->hasPerm($rex_clang->getId())))) {
                            $readonly_lang = false;
                        }
                ?>
					<fieldset>
						<legend><?= rex_i18n::msg('d2u_helper_text_lang') .' "'. $rex_clang->getName() .'"' ?></legend>
						<div class="panel-body-wrapper slide">
							<?php
                                if ($rex_clang->getId() !== (int) rex_config::get('d2u_helper', 'default_lang')) {
                                    $options_translations = [];
                                    $options_translations['yes'] = rex_i18n::msg('d2u_helper_translation_needs_update');
                                    $options_translations['no'] = rex_i18n::msg('d2u_helper_translation_is_uptodate');
                                    $options_translations['delete'] = rex_i18n::msg('d2u_helper_translation_delete');
                                    d2u_addon_backend_helper::form_select('d2u_helper_translation', 'form[lang]['. $rex_clang->getId() .'][translation_needs_update]', $options_translations, [$video->translation_needs_update], 1, false, $readonly_lang);
                                } else {
                                    echo '<input type="hidden" name="form[lang]['. $rex_clang->getId() .'][translation_needs_update]" value="">';
                                }
                            ?>
							<script>
								// Hide on document load
								$(document).ready(function() {
									toggleClangDetailsView(<?= $rex_clang->getId() ?>);
								});

								// Hide on selection change
								$("select[name='form[lang][<?= $rex_clang->getId() ?>][translation_needs_update]']").on('change', function(e) {
									toggleClangDetailsView(<?= $rex_clang->getId() ?>);
								});
							</script>
							<div id="details_clang_<?= $rex_clang->getId() ?>">
								<?php
                                    d2u_addon_backend_helper::form_input('d2u_helper_name', 'form[lang]['. $rex_clang->getId() .'][name]', $video->name, $required, $readonly_lang, 'text');
                                    d2u_addon_backend_helper::form_textarea('d2u_videos_teaser', 'form[lang]['. $rex_clang->getId() .'][teaser]', $video->teaser, 5, false, $readonly_lang, false);
                                    d2u_addon_backend_helper::form_select('d2u_videos_videotype', 'form[lang]['. $rex_clang->getId() .'][video_type_lang]', $options_link, [$video->video_type_lang], 1, false, $readonly);
                                    d2u_addon_backend_helper::form_mediafield('d2u_videos_redaxo_file_lang', '1'. $rex_clang->getId(), $video->redaxo_file_lang, $readonly_lang);
                                    d2u_addon_backend_helper::form_input('d2u_videos_youtube_video_id_lang', 'form[lang]['. $rex_clang->getId() .'][youtube_video_id_lang]', $video->youtube_video_id_lang, false, $readonly_lang, 'text');
                                ?>
							</div>
						<script>
							function changeLangType() {
								<?php
                                foreach (rex_clang::getAllIds() as $rex_clang_id) {
                                ?>
								if($('select[name="form\\[lang\\]\\[<?= $rex_clang_id ?>\\]\\[video_type_lang\\]"]').val() === "youtube") {
									$('#MEDIA_1<?= $rex_clang_id ?>').hide();
									$('#form\\[lang\\]\\[<?= $rex_clang_id ?>\\]\\[youtube_video_id_lang\\]').show();
								}
								else {
									$('#MEDIA_1<?= $rex_clang_id ?>').show();
									$('#form\\[lang\\]\\[<?= $rex_clang_id ?>\\]\\[youtube_video_id_lang\\]').hide();
								}
								<?php
                                }
                                ?>
							}

							// On init
							changeLangType();
							// On change
							<?php
                            foreach (rex_clang::getAllIds() as $rex_clang_id) {
                            ?>
								$('select[name="form\\[lang\\]\\[<?= $rex_clang_id ?>\\]\\[video_type_lang\\]"]').on('change', function() {
									changeLangType();
								});
							<?php
                            }
                            ?>
						</script>
						</div>
					</fieldset>
				<?php
                    }
                ?>
			</div>
			<footer class="panel-footer">
				<div class="rex-form-panel-footer">
					<div class="btn-toolbar">
						<button class="btn btn-save rex-form-aligned" type="submit" name="btn_save" value="1"><?= rex_i18n::msg('form_save') ?></button>
						<button class="btn btn-apply" type="submit" name="btn_apply" value="1"><?= rex_i18n::msg('form_apply') ?></button>
						<button class="btn btn-abort" type="submit" name="btn_abort" formnovalidate="formnovalidate" value="1"><?= rex_i18n::msg('form_abort') ?></button>
						<?php
                            if (\rex::getUser() instanceof rex_user && (\rex::getUser()->isAdmin() || \rex::getUser()->hasPerm('d2u_videos[edit_data]'))) {
                                echo '<button class="btn btn-delete" type="submit" name="btn_delete" formnovalidate="formnovalidate" data-confirm="'. rex_i18n::msg('form_delete') .'?" value="1">'. rex_i18n::msg('form_delete') .'</button>';
                            }
                        ?>
					</div>
				</div>
			</footer>
		</div>
	</form>
	<br>
	<?php
        echo d2u_addon_backend_helper::getCSS();
        echo d2u_addon_backend_helper::getJS();
}

if ('' === $func) {
    $query = 'SELECT videos.video_id, name, priority '
        . 'FROM '. \rex::getTablePrefix() .'d2u_videos_videos AS videos '
        . 'LEFT JOIN '. \rex::getTablePrefix() .'d2u_videos_videos_lang AS lang '
            . 'ON videos.video_id = lang.video_id AND lang.clang_id = '. (int) rex_config::get('d2u_helper', 'default_lang') .' '
        .'ORDER BY `priority`';
    $list = rex_list::factory($query, 1000);

    $list->addTableAttribute('class', 'table-striped table-hover');

    $tdIcon = '<i class="rex-icon fa-video-camera"></i>';
    $thIcon = '';
    if (\rex::getUser() instanceof rex_user && (\rex::getUser()->isAdmin() || \rex::getUser()->hasPerm('d2u_videos[edit_data]'))) {
        $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '" title="' . rex_i18n::msg('add') . '"><i class="rex-icon rex-icon-add-module"></i></a>';
    }
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'entry_id' => '###video_id###']);

    $list->setColumnLabel('video_id', rex_i18n::msg('id'));
    $list->setColumnLayout('video_id', ['<th class="rex-table-id">###VALUE###</th>', '<td class="rex-table-id">###VALUE###</td>']);

    $list->setColumnLabel('name', rex_i18n::msg('d2u_helper_name'));
    $list->setColumnParams('name', ['func' => 'edit', 'entry_id' => '###video_id###']);

    $list->setColumnLabel('priority', rex_i18n::msg('header_priority'));

    $list->addColumn(rex_i18n::msg('module_functions'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('module_functions'), ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('module_functions'), ['func' => 'edit', 'entry_id' => '###video_id###']);

    if (\rex::getUser() instanceof rex_user && (\rex::getUser()->isAdmin() || \rex::getUser()->hasPerm('d2u_videos[edit_data]'))) {
        $list->addColumn(rex_i18n::msg('delete_module'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
        $list->setColumnLayout(rex_i18n::msg('delete_module'), ['', '<td class="rex-table-action">###VALUE###</td>']);
        $list->setColumnParams(rex_i18n::msg('delete_module'), ['func' => 'delete', 'entry_id' => '###video_id###']);
        $list->addLinkAttribute(rex_i18n::msg('delete_module'), 'data-confirm', rex_i18n::msg('d2u_helper_confirm_delete'));
    }

    $list->setNoRowsMessage(rex_i18n::msg('d2u_videos_no_videos_found'));

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('d2u_videos_videos'), false);
    $fragment->setVar('content', $list->get(), false);
    echo $fragment->parse('core/page/section.php');
}
