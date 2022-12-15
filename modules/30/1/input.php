<div class="row">
	<div class="col-xs-4">
		Breite des Blocks:
	</div>
	<div class="col-xs-8">
		<select name="REX_INPUT_VALUE[20]" class="form-control">
		<?php
		$values = [12=>"12 von 12 Spalten (ganze Breite)", 8=>"8 von 12 Spalten", 6=>"6 von 12 Spalten", 4=>"4 von 12 Spalten", 3=>"3 von 12 Spalten"];
		foreach($values as $key => $value) {
			echo '<option value="'. $key .'" ';
	
			if ("REX_VALUE[20]" === $key) { /** @phpstan-ignore-line */
				echo 'selected="selected" ';
			}
			echo '>'. $value .'</option>';
		}
		?>
		</select>
	</div>
</div>
<div class="row">
	<div class="col-xs-12">&nbsp;</div>
</div>
<div class="row">
	<div class="col-xs-4">
		Auf größeren Bildschirmen zentrieren?
	</div>
	<div class="col-xs-8">
		<select name="REX_INPUT_VALUE[17]" class="form-control">
		<?php
		$values = array(0=>"Nicht zentrieren.", 1=>"Zentrieren, wenn freie Breite von anderem Inhalt nicht genutzt wird");
		foreach($values as $key => $value) {
			echo '<option value="'. $key .'" ';
	
			if ("REX_VALUE[17]" === $key) { /** @phpstan-ignore-line */
				echo 'selected="selected" ';
			}
			echo '>'. $value .'</option>';
		}
		?>
		</select>
	</div>
</div>
<script>
	function offset_changer(value) {
		if (value === "12") {
			$("select[name='REX_INPUT_VALUE[17]']").parent().parent().slideUp();
		}
		else {
			$("select[name='REX_INPUT_VALUE[17]']").parent().parent().slideDown();
		}
	}

	// Hide on document load
	$(document).ready(function() {
		offset_changer($("select[name='REX_INPUT_VALUE[20]']").val());
	});

	// Hide on selection change
	$("select[name='REX_INPUT_VALUE[20]']").on('change', function(e) {
		offset_changer($(this).val());
	});
</script>
<div class="row">
	<div class="col-xs-12">&nbsp;</div>
</div>
<div class="row">
	<div class="col-xs-4">
		Eingebunden werden soll ...
	</div>
	<div class="col-xs-8">
		<?php
		$select_link = new rex_select(); 
		$select_link->setName('REX_INPUT_VALUE[1]'); // do not change, see boot.php
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
			$select_playlist->setName('REX_INPUT_VALUE[2]'); // do not change, see boot.php
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
			$select_video->setName('REX_INPUT_VALUE[3]'); // do not change, see boot.php
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