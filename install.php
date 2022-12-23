<?php
// Install database
\rex_sql_table::get(\rex::getTable('d2u_videos_videos'))
	->ensureColumn(new rex_sql_column('video_id', 'INT(11) unsigned', false, null, 'auto_increment'))
	->setPrimaryKey('video_id')
	->ensureColumn(new \rex_sql_column('priority', 'INT(10)', false))
	->ensureColumn(new \rex_sql_column('picture', 'VARCHAR(255)'))
	->ensureColumn(new \rex_sql_column('video_type', 'VARCHAR(10)'))
	->ensureColumn(new \rex_sql_column('youtube_video_id', 'VARCHAR(50)'))
	->ensureColumn(new \rex_sql_column('redaxo_file', 'VARCHAR(255)'))
	->ensure();
\rex_sql_table::get(\rex::getTable('d2u_videos_videos_lang'))
	->ensureColumn(new rex_sql_column('video_id', 'INT(11)', false))
	->ensureColumn(new \rex_sql_column('clang_id', 'INT(11)', false, (string) rex_clang::getStartId()))
	->setPrimaryKey(['video_id', 'clang_id'])
	->ensureColumn(new \rex_sql_column('name', 'VARCHAR(255)'))
	->ensureColumn(new \rex_sql_column('teaser', 'VARCHAR(255)'))
	->ensureColumn(new \rex_sql_column('video_type', 'VARCHAR(10)'))
	->ensureColumn(new \rex_sql_column('youtube_video_id', 'VARCHAR(255)'))
	->ensureColumn(new \rex_sql_column('redaxo_file', 'VARCHAR(255)'))
	->ensureColumn(new \rex_sql_column('translation_needs_update', 'VARCHAR(7)'))
	->ensure();

\rex_sql_table::get(\rex::getTable('d2u_videos_playlists'))
	->ensureColumn(new rex_sql_column('playlist_id', 'INT(11) unsigned', false, null, 'auto_increment'))
	->setPrimaryKey('playlist_id')
	->ensureColumn(new \rex_sql_column('name', 'VARCHAR(255)'))
	->ensureColumn(new \rex_sql_column('video_ids', 'TEXT'))
	->ensure();

$sql = rex_sql::factory();

// Media Manager media types
$sql->setQuery("SELECT * FROM ". \rex::getTablePrefix() ."media_manager_type WHERE name = 'd2u_videos_preview'");
if(intval($sql->getRows()) === 0) {
	$sql->setQuery("INSERT INTO ". \rex::getTablePrefix() ."media_manager_type (`status`, `name`, `description`) VALUES
		(1, 'd2u_videos_preview', 'D2U Videos großes Vorschaubild des Videos im Playerfenster');");
	$last_id = $sql->getLastId();
	$sql->setQuery("INSERT INTO ". \rex::getTablePrefix() ."media_manager_type_effect (`type_id`, `effect`, `parameters`, `priority`, `createdate`, `createuser`) VALUES
		(". $last_id .", 'resize', '{\"rex_effect_crop\":{\"rex_effect_crop_width\":\"\",\"rex_effect_crop_height\":\"\",\"rex_effect_crop_offset_width\":\"\",\"rex_effect_crop_offset_height\":\"\",\"rex_effect_crop_hpos\":\"center\",\"rex_effect_crop_vpos\":\"middle\"},\"rex_effect_filter_blur\":{\"rex_effect_filter_blur_amount\":\"80\",\"rex_effect_filter_blur_radius\":\"8\",\"rex_effect_filter_blur_threshold\":\"3\"},\"rex_effect_filter_sharpen\":{\"rex_effect_filter_sharpen_amount\":\"80\",\"rex_effect_filter_sharpen_radius\":\"0.5\",\"rex_effect_filter_sharpen_threshold\":\"3\"},\"rex_effect_flip\":{\"rex_effect_flip_flip\":\"X\"},\"rex_effect_img_interlace\":{\"rex_effect_img_interlace_jpg_interlace\":\"off\",\"rex_effect_img_interlace_png_interlace\":\"off\",\"rex_effect_img_interlace_gif_interlace\":\"off\"},\"rex_effect_insert_image\":{\"rex_effect_insert_image_brandimage\":\"\",\"rex_effect_insert_image_hpos\":\"left\",\"rex_effect_insert_image_vpos\":\"top\",\"rex_effect_insert_image_padding_x\":\"-10\",\"rex_effect_insert_image_padding_y\":\"-10\"},\"rex_effect_jpg_quality\":{\"rex_effect_jpg_quality_quality\":\"90\"},\"rex_effect_mirror\":{\"rex_effect_mirror_height\":\"\",\"rex_effect_mirror_set_transparent\":\"colored\",\"rex_effect_mirror_bg_r\":\"\",\"rex_effect_mirror_bg_g\":\"\",\"rex_effect_mirror_bg_b\":\"\"},\"rex_effect_resize\":{\"rex_effect_resize_width\":\"1200\",\"rex_effect_resize_height\":\"510\",\"rex_effect_resize_style\":\"minimum\",\"rex_effect_resize_allow_enlarge\":\"enlarge\"},\"rex_effect_roundcorners\":{\"rex_effect_roundcorners_radius\":\"20\",\"rex_effect_roundcorners_transparency\":\"127\",\"rex_effect_roundcorners_color\":\"ffffff\",\"rex_effect_roundcorners_conversion\":\"none\"},\"rex_effect_workspace\":{\"rex_effect_workspace_width\":\"\",\"rex_effect_workspace_height\":\"\",\"rex_effect_workspace_hpos\":\"left\",\"rex_effect_workspace_vpos\":\"top\",\"rex_effect_workspace_set_transparent\":\"colored\",\"rex_effect_workspace_bg_r\":\"\",\"rex_effect_workspace_bg_g\":\"\",\"rex_effect_workspace_bg_b\":\"\"}}', 1, CURRENT_TIMESTAMP, 'd2u_videos'),
		(". $last_id .", 'crop', '{\"rex_effect_crop\":{\"rex_effect_crop_width\":\"1200\",\"rex_effect_crop_height\":\"510\",\"rex_effect_crop_offset_width\":\"\",\"rex_effect_crop_offset_height\":\"\",\"rex_effect_crop_hpos\":\"center\",\"rex_effect_crop_vpos\":\"middle\"},\"rex_effect_filter_blur\":{\"rex_effect_filter_blur_amount\":\"80\",\"rex_effect_filter_blur_radius\":\"8\",\"rex_effect_filter_blur_threshold\":\"3\"},\"rex_effect_filter_sharpen\":{\"rex_effect_filter_sharpen_amount\":\"80\",\"rex_effect_filter_sharpen_radius\":\"0.5\",\"rex_effect_filter_sharpen_threshold\":\"3\"},\"rex_effect_flip\":{\"rex_effect_flip_flip\":\"X\"},\"rex_effect_img_interlace\":{\"rex_effect_img_interlace_jpg_interlace\":\"off\",\"rex_effect_img_interlace_png_interlace\":\"off\",\"rex_effect_img_interlace_gif_interlace\":\"off\"},\"rex_effect_insert_image\":{\"rex_effect_insert_image_brandimage\":\"\",\"rex_effect_insert_image_hpos\":\"left\",\"rex_effect_insert_image_vpos\":\"top\",\"rex_effect_insert_image_padding_x\":\"-10\",\"rex_effect_insert_image_padding_y\":\"-10\"},\"rex_effect_jpg_quality\":{\"rex_effect_jpg_quality_quality\":\"90\"},\"rex_effect_mirror\":{\"rex_effect_mirror_height\":\"\",\"rex_effect_mirror_set_transparent\":\"colored\",\"rex_effect_mirror_bg_r\":\"\",\"rex_effect_mirror_bg_g\":\"\",\"rex_effect_mirror_bg_b\":\"\"},\"rex_effect_resize\":{\"rex_effect_resize_width\":\"\",\"rex_effect_resize_height\":\"\",\"rex_effect_resize_style\":\"maximum\",\"rex_effect_resize_allow_enlarge\":\"enlarge\"},\"rex_effect_roundcorners\":{\"rex_effect_roundcorners_radius\":\"20\",\"rex_effect_roundcorners_transparency\":\"127\",\"rex_effect_roundcorners_color\":\"ffffff\",\"rex_effect_roundcorners_conversion\":\"none\"},\"rex_effect_workspace\":{\"rex_effect_workspace_width\":\"\",\"rex_effect_workspace_height\":\"\",\"rex_effect_workspace_hpos\":\"left\",\"rex_effect_workspace_vpos\":\"top\",\"rex_effect_workspace_set_transparent\":\"colored\",\"rex_effect_workspace_bg_r\":\"\",\"rex_effect_workspace_bg_g\":\"\",\"rex_effect_workspace_bg_b\":\"\"}}', 2, CURRENT_TIMESTAMP, 'd2u_videos')");
}
$sql->setQuery("SELECT * FROM ". \rex::getTablePrefix() ."media_manager_type WHERE name = 'd2u_videos_thumb'");
if(intval($sql->getRows()) === 0) {
	$sql->setQuery("INSERT INTO ". \rex::getTablePrefix() ."media_manager_type (`status`, `name`, `description`) VALUES
		(1, 'd2u_videos_thumb', 'D2U Videos kleines Vorschaubild des Videos in der Liste');");
	$last_id = $sql->getLastId();
	$sql->setQuery("INSERT INTO ". \rex::getTablePrefix() ."media_manager_type_effect (`type_id`, `effect`, `parameters`, `priority`, `createdate`, `createuser`) VALUES
		(". $last_id .", 'resize', '{\"rex_effect_crop\":{\"rex_effect_crop_width\":\"780\",\"rex_effect_crop_height\":\"404\",\"rex_effect_crop_offset_width\":\"\",\"rex_effect_crop_offset_height\":\"\",\"rex_effect_crop_hpos\":\"center\",\"rex_effect_crop_vpos\":\"middle\"},\"rex_effect_filter_blur\":{\"rex_effect_filter_blur_amount\":\"80\",\"rex_effect_filter_blur_radius\":\"8\",\"rex_effect_filter_blur_threshold\":\"3\"},\"rex_effect_filter_sharpen\":{\"rex_effect_filter_sharpen_amount\":\"80\",\"rex_effect_filter_sharpen_radius\":\"0.5\",\"rex_effect_filter_sharpen_threshold\":\"3\"},\"rex_effect_flip\":{\"rex_effect_flip_flip\":\"X\"},\"rex_effect_img_interlace\":{\"rex_effect_img_interlace_jpg_interlace\":\"off\",\"rex_effect_img_interlace_png_interlace\":\"off\",\"rex_effect_img_interlace_gif_interlace\":\"off\"},\"rex_effect_insert_image\":{\"rex_effect_insert_image_brandimage\":\"\",\"rex_effect_insert_image_hpos\":\"left\",\"rex_effect_insert_image_vpos\":\"top\",\"rex_effect_insert_image_padding_x\":\"-10\",\"rex_effect_insert_image_padding_y\":\"-10\"},\"rex_effect_jpg_quality\":{\"rex_effect_jpg_quality_quality\":\"90\"},\"rex_effect_mirror\":{\"rex_effect_mirror_height\":\"\",\"rex_effect_mirror_set_transparent\":\"colored\",\"rex_effect_mirror_bg_r\":\"\",\"rex_effect_mirror_bg_g\":\"\",\"rex_effect_mirror_bg_b\":\"\"},\"rex_effect_resize\":{\"rex_effect_resize_width\":\"780\",\"rex_effect_resize_height\":\"404\",\"rex_effect_resize_style\":\"minimum\",\"rex_effect_resize_allow_enlarge\":\"enlarge\"},\"rex_effect_roundcorners\":{\"rex_effect_roundcorners_radius\":\"20\",\"rex_effect_roundcorners_transparency\":\"127\",\"rex_effect_roundcorners_color\":\"ffffff\",\"rex_effect_roundcorners_conversion\":\"none\"},\"rex_effect_workspace\":{\"rex_effect_workspace_width\":\"\",\"rex_effect_workspace_height\":\"\",\"rex_effect_workspace_hpos\":\"left\",\"rex_effect_workspace_vpos\":\"top\",\"rex_effect_workspace_set_transparent\":\"colored\",\"rex_effect_workspace_bg_r\":\"\",\"rex_effect_workspace_bg_g\":\"\",\"rex_effect_workspace_bg_b\":\"\"}}', 1, CURRENT_TIMESTAMP, 'd2u_videos'),
		(". $last_id .", 'crop', '{\"rex_effect_crop\":{\"rex_effect_crop_width\":\"780\",\"rex_effect_crop_height\":\"404\",\"rex_effect_crop_offset_width\":\"\",\"rex_effect_crop_offset_height\":\"\",\"rex_effect_crop_hpos\":\"center\",\"rex_effect_crop_vpos\":\"middle\"},\"rex_effect_filter_blur\":{\"rex_effect_filter_blur_amount\":\"80\",\"rex_effect_filter_blur_radius\":\"8\",\"rex_effect_filter_blur_threshold\":\"3\"},\"rex_effect_filter_sharpen\":{\"rex_effect_filter_sharpen_amount\":\"80\",\"rex_effect_filter_sharpen_radius\":\"0.5\",\"rex_effect_filter_sharpen_threshold\":\"3\"},\"rex_effect_flip\":{\"rex_effect_flip_flip\":\"X\"},\"rex_effect_img_interlace\":{\"rex_effect_img_interlace_jpg_interlace\":\"off\",\"rex_effect_img_interlace_png_interlace\":\"off\",\"rex_effect_img_interlace_gif_interlace\":\"off\"},\"rex_effect_insert_image\":{\"rex_effect_insert_image_brandimage\":\"\",\"rex_effect_insert_image_hpos\":\"left\",\"rex_effect_insert_image_vpos\":\"top\",\"rex_effect_insert_image_padding_x\":\"-10\",\"rex_effect_insert_image_padding_y\":\"-10\"},\"rex_effect_jpg_quality\":{\"rex_effect_jpg_quality_quality\":\"90\"},\"rex_effect_mirror\":{\"rex_effect_mirror_height\":\"\",\"rex_effect_mirror_set_transparent\":\"colored\",\"rex_effect_mirror_bg_r\":\"\",\"rex_effect_mirror_bg_g\":\"\",\"rex_effect_mirror_bg_b\":\"\"},\"rex_effect_resize\":{\"rex_effect_resize_width\":\"\",\"rex_effect_resize_height\":\"\",\"rex_effect_resize_style\":\"maximum\",\"rex_effect_resize_allow_enlarge\":\"enlarge\"},\"rex_effect_roundcorners\":{\"rex_effect_roundcorners_radius\":\"20\",\"rex_effect_roundcorners_transparency\":\"127\",\"rex_effect_roundcorners_color\":\"ffffff\",\"rex_effect_roundcorners_conversion\":\"none\"},\"rex_effect_workspace\":{\"rex_effect_workspace_width\":\"\",\"rex_effect_workspace_height\":\"\",\"rex_effect_workspace_hpos\":\"left\",\"rex_effect_workspace_vpos\":\"top\",\"rex_effect_workspace_set_transparent\":\"colored\",\"rex_effect_workspace_bg_r\":\"\",\"rex_effect_workspace_bg_g\":\"\",\"rex_effect_workspace_bg_b\":\"\"}}', 2, CURRENT_TIMESTAMP, 'd2u_videos');");
}


// Update modules
if(class_exists('D2UModuleManager')) {
	$modules = [];
	$modules[] = new D2UModule("30-1",
		"D2U Videomanager - Video / Playlist",
		2);
	$d2u_module_manager = new D2UModuleManager($modules, "", "d2u_videos");
	$d2u_module_manager->autoupdate();
}