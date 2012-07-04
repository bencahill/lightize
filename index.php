<?php

include "bootstrap.php";

$currentDir = !empty($_POST['dir'])?$_POST['dir']:NULL;

$dir = new LDirectory( $currentDir );
$images = $dir->getImages();

?>
<!DOCTYPE html>
<meta charset="utf-8">

<script src="js/jquery-1.4.3.min.js"></script>
<script src="js/jquery.quicksand.js"></script>
<script src="js/jquery.ui.core.js"></script>
<script src="js/jquery.ui.widget.js"></script>
<script src="js/jquery.ui.mouse.js"></script>
<script src="js/jquery.ui.selectable.js"></script>
<script src="js/jquery.scrollIntoView.js"></script>
<script src="js/moment.min.js"></script>
<script>
(function($) {
	$.fn.sorted = function(customOptions) {
		var options = {
			reversed: false,
			by: function(a) {
				return a.text();
			}
		};
		$.extend(options, customOptions);

		$data = $(this);
		arr = $data.get();
		arr.sort(function(a, b) {

			var valA = options.by($(a));
			var valB = options.by($(b));
			if (options.reversed) {
				return (valA < valB) ? 1 : (valA > valB) ? -1 : 0;
			} else {
				return (valA < valB) ? -1 : (valA > valB) ? 1 : 0;
			}
		});
		return $(arr);
	};
})(jQuery);
// DOMContentLoaded
$(function() {

	var $sortButtons = $('input[name=sort]');

	var $originalData = $('#imagelist');
	var $data = $originalData.clone();

	$sortButtons.change(function(e) {
		var $filteredData = $data.find('li');

		var sortType = $('input[name=sort]:checked').parent().text().toLowerCase();
		var $sortedData = $filteredData.sorted({
			by: function(v) {
				return $(v).attr('data-' + sortType);
			}
		});

		$originalData.quicksand($sortedData, function() {
			$('#content ul').find('li').before(' ');
			$('#imagelist').css('height', '').selectable("refresh");
			$('a').click(function(event) { event.preventDefault(); });
		});
	});

	$('#imagelist').selectable({
		filter: "li.image",
		change: function(event, ui) {
			var $sel = ui.selection;
			var len = ui.selection.length;
			if ( len == 1 ) {
				var date = moment($sel.attr('data-date')*1000).format('ddd, MMM D YYYY h:mm:ss A');
				var name = $sel.attr('data-name');
				var $images = $('#imagelist li.image');
				var currentImage = $images.index($sel)+1;
				var totalImages = $images.length;
				var dimensions = $sel.attr('data-dimensions');
				var size = ($sel.attr('data-size') / (1024*1024)).toFixed(1)+'M';
				$('#status').text(date+name+currentImage+' of '+totalImages+dimensions+size);
				$sel.scrollIntoView();
			} else {
				$('#status').text(len+' items selected');
			}
		}
	});

	$('input[name=size]').change(function() {
		var size = $(this).val();
		var imgSize = size - 12;
		$('#content ul li').width(size).height(size);
		$data.find('li').width(size).height(size);
		$('#content ul li a').css('line-height', size+'px');
		$data.find('li a').css('line-height', size+'px');
		$('#content ul li img').css('max-width', imgSize+'px').css('max-height', imgSize+'px');
		$data.find('li img').css('max-width', imgSize+'px').css('max-height', imgSize+'px');
	});

	$('input[name=size]').mouseup(function() {
		$('#imagelist').selectable("refresh");
	});

	$('a').click(function(event) { event.preventDefault(); });
});
</script>

<link rel="stylesheet" href="style.css">

<div id="main">
	<div id="dirs"></div>
	<div id="images">
		<div id="toolbar">
			<label><input name="sort" type="radio"></input>Date</label>
			<label><input name="sort" type="radio"></input>Name</label>
			<input name="size" type="range" min="50" max="180" value="112">
		</div>
		<div id="content">
			<ul id="imagelist">
<?php foreach( $images as $index=>$image ): ?>
				<li class="image" data-id="id-<?php echo $index+1; ?>" data-name="<?php echo $image->name; ?>" data-date="<?php echo $image->date; ?>" data-rating="<?php echo $image->rating; ?>" data-dimensions="<?php echo $image->info['dimensions']['width'].'x'.$image->info['dimensions']['height']; ?>" data-size="<?php echo $image->info['sizeInBytes']; ?>"><a href="#bla"><img class="center" src="<?php echo "cache/$dir->name/$image->thumbnail" ?>"></a></li> 
<?php endforeach; ?>
				<!-- To make justify work, up to ten items per row -->
				<li data-id="id-9000" data-name="zzz"></li>
				<li data-id="id-9001" data-name="zzz"></li>
				<li data-id="id-9002" data-name="zzz"></li>
				<li data-id="id-9003" data-name="zzz"></li>
				<li data-id="id-9004" data-name="zzz"></li>
				<li data-id="id-9005" data-name="zzz"></li>
				<li data-id="id-9006" data-name="zzz"></li>
				<li data-id="id-9007" data-name="zzz"></li>
				<li data-id="id-9008" data-name="zzz"></li>
				<li data-id="id-9010" data-name="zzz"></li>
				<li data-id="id-9011" data-name="zzz"></li>
				<li data-id="id-9012" data-name="zzz"></li>
				<li data-id="id-9013" data-name="zzz"></li>
				<li data-id="id-9014" data-name="zzz"></li>
				<li data-id="id-9015" data-name="zzz"></li>
				<li data-id="id-9016" data-name="zzz"></li>
				<li data-id="id-9017" data-name="zzz"></li>
				<li data-id="id-9018" data-name="zzz"></li>
				<li data-id="id-9019" data-name="zzz"></li>
				<li data-id="id-9020" data-name="zzz"></li>
			</ul>
		</div>
	</div>
</div>

<div id="status">0 items selected</div>
