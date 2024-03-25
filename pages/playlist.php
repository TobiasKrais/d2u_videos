<?php
$func = rex_request('func', 'string');
$entry_id = rex_request('entry_id', 'int');
$message = rex_get('message', 'string');

// Print comments
if ('' !== $message) {
    echo rex_view::success(rex_i18n::msg($message));
}

// save settings
if (1 === (int) filter_input(INPUT_POST, 'btn_save') || 1 === (int) filter_input(INPUT_POST, 'btn_apply')) {
    $form = rex_post('form', 'array', []);

    $success = true;
    $playlist_id = $form['playlist_id'];
    $playlist = new TobiasKrais\D2UVideos\Playlist($form['playlist_id']);
    $playlist->name = $form['name'];
    $video_ids = $form['video_ids'] ?? [];
    $playlist->videos = [];
    foreach ($video_ids as $video_id) {
        $playlist->videos[$video_id] = new \TobiasKrais\D2UVideos\Video($video_id, (int) rex_config::get('d2u_helper', 'default_lang'));
    }

    // message output
    $message = 'form_save_error';
    if ($playlist->save()) {
        $message = 'form_saved';
    }

    // Redirect to make reload and thus double save impossible
    if (1 === (int) filter_input(INPUT_POST, 'btn_apply') && $playlist->playlist_id > 0) {
        header('Location: '. rex_url::currentBackendPage(['entry_id' => $playlist->playlist_id, 'func' => 'edit', 'message' => $message], false));
    } else {
        header('Location: '. rex_url::currentBackendPage(['message' => $message], false));
    }
    exit;
}
// Delete
if (1 === (int) filter_input(INPUT_POST, 'btn_delete') || 'delete' === $func) {
    $playlist_id = $entry_id;
    if (0 === $playlist_id) {
        $form = rex_post('form', 'array', []);
        $playlist_id = $form['playlist_id'];
    }
    $playlist = new TobiasKrais\D2UVideos\Playlist($playlist_id);
    $playlist->delete();

    $func = '';
}

// Form
if ('edit' === $func || 'add' === $func) {
?>
	<form action="<?= rex_url::currentBackendPage() ?>" method="post">
		<div class="panel panel-edit">
			<header class="panel-heading"><div class="panel-title"><?= rex_i18n::msg('d2u_videos_playlists') ?></div></header>
			<div class="panel-body">
				<input type="hidden" name="form[playlist_id]" value="<?= $entry_id ?>">
				<fieldset>
					<legend><?= rex_i18n::msg('d2u_helper_data_all_lang') ?></legend>
					<div class="panel-body-wrapper slide">
						<?php
                            $playlist = new TobiasKrais\D2UVideos\Playlist($entry_id);
                            $readonly = true;
                            if (\rex::getUser() instanceof rex_user && (\rex::getUser()->isAdmin() || \rex::getUser()->hasPerm('d2u_videos[edit_data]'))) {
                                $readonly = false;
                            }

                            \TobiasKrais\D2UHelper\BackendHelper::form_input('d2u_helper_name', 'form[name]', $playlist->name, true, $readonly, 'text');
                            $options_videos = [];
                            foreach (TobiasKrais\D2UVideos\Video::getAll((int) rex_config::get('d2u_helper', 'default_lang')) as $video) {
                                $options_videos[$video->video_id] = $video->name;
                            }
                            \TobiasKrais\D2UHelper\BackendHelper::form_select('d2u_videos_videos', 'form[video_ids][]', $options_videos, array_keys($playlist->videos), 20, true, $readonly);
                        ?>
					</div>
				</fieldset>
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
        echo \TobiasKrais\D2UHelper\BackendHelper::getCSS();
        echo \TobiasKrais\D2UHelper\BackendHelper::getJS();
        echo \TobiasKrais\D2UHelper\BackendHelper::getJSOpenAll();
}

if ('' === $func) {
    $query = 'SELECT playlist_id, name FROM '. \rex::getTablePrefix() .'d2u_videos_playlists '
        .'ORDER BY `name`';
    $list = rex_list::factory($query, 1000);

    $list->addTableAttribute('class', 'table-striped table-hover');

    $tdIcon = '<i class="rex-icon fa-list-ol"></i>';
    $thIcon = '';
    if (\rex::getUser() instanceof rex_user && (\rex::getUser()->isAdmin() || \rex::getUser()->hasPerm('d2u_videos[edit_data]'))) {
        $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '" title="' . rex_i18n::msg('add') . '"><i class="rex-icon rex-icon-add-module"></i></a>';
    }
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'entry_id' => '###playlist_id###']);

    $list->setColumnLabel('playlist_id', rex_i18n::msg('id'));
    $list->setColumnLayout('playlist_id', ['<th class="rex-table-id">###VALUE###</th>', '<td class="rex-table-id">###VALUE###</td>']);

    $list->setColumnLabel('name', rex_i18n::msg('d2u_helper_name'));
    $list->setColumnParams('name', ['func' => 'edit', 'entry_id' => '###playlist_id###']);

    $list->addColumn(rex_i18n::msg('module_functions'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('module_functions'), ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('module_functions'), ['func' => 'edit', 'entry_id' => '###playlist_id###']);

    if (\rex::getUser() instanceof rex_user && (\rex::getUser()->isAdmin() || \rex::getUser()->hasPerm('d2u_videos[edit_data]'))) {
        $list->addColumn(rex_i18n::msg('delete_module'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
        $list->setColumnLayout(rex_i18n::msg('delete_module'), ['', '<td class="rex-table-action">###VALUE###</td>']);
        $list->setColumnParams(rex_i18n::msg('delete_module'), ['func' => 'delete', 'entry_id' => '###playlist_id###']);
        $list->addLinkAttribute(rex_i18n::msg('delete_module'), 'data-confirm', rex_i18n::msg('d2u_helper_confirm_delete'));
    }

    $list->setNoRowsMessage(rex_i18n::msg('d2u_videos_no_playlists_found'));

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('d2u_videos_videos'), false);
    $fragment->setVar('content', $list->get(), false);
    echo $fragment->parse('core/page/section.php');
}
