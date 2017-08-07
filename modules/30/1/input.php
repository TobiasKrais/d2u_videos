<div class="row">
	<div class="col-xs-4">
		Weiterleitung zu ...
	</div>
	<div class="col-xs-8">
		<?php
		$select_link = new rex_select(); 
		$select_link->setName('REX_INPUT_VALUE[1]'); 
		$select_link->setSize(1);
		$select_link->setAttribute('class', 'form-control');
		$select_link->setAttribute('id', 'selector');

		$select_link->addOption("Playlist", "playlist"); 
		$select_link->addOption("Video", "video"); 

		$select_link->setSelected("REX_VALUE[1]");

		echo $select_link->show();
		?>
	</div>
</div>
<div class="row">
	<div class="col-xs-12">&nbsp;</div>
</div>

<div class="row" id="playlist">
	<div class="col-xs-4">
		D2U Videomanager Playlist
	</div>
	<div class="col-xs-8">
		<?php
			$select_playlist = new rex_select(); 
			$select_playlist->setName('REX_INPUT_VALUE[2]'); 
			$select_playlist->setSize(1);
			$select_playlist->setAttribute('class', 'form-control');

			$playlists = Playlist::getAll(rex_clang::getCurrentId());
			foreach($playlists as $playlist)  {
				$select_playlist->addOption($playlist->name, $playlist->playlist_id); 
			}

			$select_playlist->setSelected("REX_VALUE[2]");

			echo $select_playlist->show();
		?>
	</div>
</div>

<div class="row" id="video">
	<div class="col-xs-4">
		D2U Videomanager Video:
	</div>
	<div class="col-xs-8">
		<?php
			$select_video = new rex_select();
			$select_video->setName('REX_INPUT_VALUE[3]'); 
			$select_video->setSize(1);
			$select_video->setAttribute('class', 'form-control');

			$videos = Video::getAll(rex_clang::getCurrentId());
			foreach($videos as $video)  {
				$select_video->addOption($video->name, $video->video_id); 
			}

			$select_video->setSelected("REX_VALUE[3]");

			echo $select_video->show();
		?>
	</div>
</div>

<script>
	function changeType() {
		if($('#selector').val() === "playlist") {
			$('#playlist').show();
			$('#video').hide();
		}
		else if($('#selector').val() === "video") {
			$('#playlist').hide();
			$('#video').show();
		}
	}
	
	// On init
	changeType();
	
	// On change
	$('#selector').on('change', function() {
		changeType();
	});
</script>