<?php

include "bootstrap.php";

$currentDir = !empty($_POST['dir'])?$_POST['dir']:NULL;

$dir = new LDirectory( $currentDir );
$images = $dir->getImages();

?>
<!DOCTYPE html>
<meta charset="utf-8">

<title>Lightize - <?php echo $dir->name; ?></title>

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

	var $buttons = $('input[name=sort]').add('input[name=ratingFilter]').add('input[name=hiddenFilter]');

	var $originalData = $('#imagelist');
	var $data = $originalData.clone();

	$buttons.change(function(e) {
		filterSort();
	});

	function filterSort() {
		var $filteredData = $data.find('li');

		var ratingFilter = $('input[name=ratingFilter]:checked').val();
		// hide by default
		var hiddenFilter = $('input[name=hiddenFilter]').attr('checked') ? false : true;

		if ( hiddenFilter ) {
			$filteredData = $filteredData.filter('[data-hidden=0]');
		}

		$filteredData = $filteredData.filter(function() {
			return $(this).attr('data-rating') >= ratingFilter;
		});

		$filteredData = $filteredData.show();

		var sortType = $('input[name=sort]:checked').val().toLowerCase();
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
	}

	$('#imagelist').selectable({
		filter: "li.image",
		change: function(event, ui) {
			$sel = ui.selection;
			var len = ui.selection.length;
			if ( len == 1 ) {
				var date = moment($sel.attr('data-date')*1000).format('ddd, MMM D YYYY h:mm:ss A');
				var name = $sel.attr('data-name');
				var $images = $('#imagelist li.image');
				var currentImage = $images.index($sel)+1;
				var totalImages = $images.length;
				var dimensions = $sel.attr('data-dimensions');
				var size = ($sel.attr('data-size') / (1024*1024)).toFixed(1)+'M';
				$('#status li.date').text(date);
				$('#status li.name').text(name);
				$('#status li.number').text(currentImage+' of '+totalImages);
				$('#status li.dimensions').text(dimensions);
				$('#status li.bytes').text(size);

				var rating = $sel.attr('data-rating');
				$('.rating #star'+rating).attr('checked', true);
				var hidden = ($sel.attr('data-hidden') == 1) ? true : false;
				$('#hideImage input').attr('checked', hidden);
				$sel.scrollIntoView();
			} else {
				$('#status li').text('');
				$('#status li.name').text(len+' items selected');
				$('input[name=rating]:checked').attr('checked', false);
				$('input[name=hidden]').attr('checked', false);
			}
		}
	});

	$('input[name=rating]').change(function() {
		var rating = $(this).filter(':checked').val();
		action( 'rating', rating );
	});

	$('input[name=hidden]').change(function() {
		var hidden = $(this).attr('checked') ? 1 : 0;
		action( 'hidden', hidden );
	});

	function action( name, value ) {
		if( $sel.length > 0 ) {
			$('#loading').fadeIn( 100 );

			var params = { 'images': $sel.map(function() { return $(this).attr('data-name'); }), 'directory': '<?php echo $dir->name; ?>' }
			params[name] = value;

			$.get(document.URL+'action.php', params, function() {
				$sel.each(function() {
					var $this = $(this);
					$originalData.find("li[data-id='"+$this.attr('data-id')+"']").attr('data-'+name, value);
					$data.find("li[data-id='"+$this.attr('data-id')+"']").attr('data-'+name, value);
					$this.attr('data-'+name, value);
				});
				$('#loading').fadeOut( 100 );
				filterSort();
			});
		}
	}

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
			<ul>
				<li class="rating">
					<input type="radio" id="star0Filter" name="ratingFilter" value="0" /><label for="star0Filter" title="0 stars">0 stars</label>
					<input type="radio" id="star1Filter" name="ratingFilter" value="1" checked /><label for="star1Filter" title="1 star">1 star</label>
					<input type="radio" id="star2Filter" name="ratingFilter" value="2" /><label for="star2Filter" title="2 stars">2 stars</label>
					<input type="radio" id="star3Filter" name="ratingFilter" value="3" /><label for="star3Filter" title="3 stars">3 stars</label>
					<input type="radio" id="star4Filter" name="ratingFilter" value="4" /><label for="star4Filter" title="4 stars">4 stars</label>
					<input type="radio" id="star5Filter" name="ratingFilter" value="5" /><label for="star5Filter" title="5 stars">5 stars</label>
				</li>
				<li class="hideImage"><input type="checkbox" id="hiddenFilter" name="hiddenFilter" value="1" /><label for="hiddenFilter" title="Hidden">Hidden</label></li>
				<li id="sort">
					<input id="sortDate" name="sort" type="radio" value="date" checked /><label for="sortDate">Date</label>
					<input id="sortName" name="sort" type="radio" value="name" /><label for="sortName">Name</label>
				</li>
				<li>
				<input id="size" name="size" type="range" min="50" max="180" value="112">
				</li>
			</ul>
		</div>
		<div id="content">
			<ul id="imagelist">
<?php foreach( $images as $index=>$image ): ?>
				<li class="image" data-id="id-<?php echo $index+1; ?>" data-name="<?php echo $image->name; ?>" data-date="<?php echo $image->date; ?>" data-rating="<?php echo $image->rating; ?>" data-hidden="<?php echo $image->hiddenInDirectory; ?>" data-dimensions="<?php echo $image->info['dimensions']['width'].'x'.$image->info['dimensions']['height']; ?>" data-size="<?php echo $image->info['sizeInBytes']; ?>"<?php if( $image->hiddenInDirectory == 1 || $image->rating == 0 ) { echo ' style="display:none;"'; } ?>><a><img class="center" src="<?php echo "cache/$dir->name/$image->thumbnail" ?>"></a></li> 
<?php endforeach; ?>
				<!-- To make justify work, up to ten items per row -->
				<li data-id="id-9000" data-name="zzz" data-rating="5" data-hidden="0"></li>
				<li data-id="id-9001" data-name="zzz" data-rating="5" data-hidden="0"></li>
				<li data-id="id-9002" data-name="zzz" data-rating="5" data-hidden="0"></li>
				<li data-id="id-9003" data-name="zzz" data-rating="5" data-hidden="0"></li>
				<li data-id="id-9004" data-name="zzz" data-rating="5" data-hidden="0"></li>
				<li data-id="id-9005" data-name="zzz" data-rating="5" data-hidden="0"></li>
				<li data-id="id-9006" data-name="zzz" data-rating="5" data-hidden="0"></li>
				<li data-id="id-9007" data-name="zzz" data-rating="5" data-hidden="0"></li>
				<li data-id="id-9008" data-name="zzz" data-rating="5" data-hidden="0"></li>
				<li data-id="id-9010" data-name="zzz" data-rating="5" data-hidden="0"></li>
				<li data-id="id-9011" data-name="zzz" data-rating="5" data-hidden="0"></li>
				<li data-id="id-9012" data-name="zzz" data-rating="5" data-hidden="0"></li>
				<li data-id="id-9013" data-name="zzz" data-rating="5" data-hidden="0"></li>
				<li data-id="id-9014" data-name="zzz" data-rating="5" data-hidden="0"></li>
				<li data-id="id-9015" data-name="zzz" data-rating="5" data-hidden="0"></li>
				<li data-id="id-9016" data-name="zzz" data-rating="5" data-hidden="0"></li>
				<li data-id="id-9017" data-name="zzz" data-rating="5" data-hidden="0"></li>
				<li data-id="id-9018" data-name="zzz" data-rating="5" data-hidden="0"></li>
				<li data-id="id-9019" data-name="zzz" data-rating="5" data-hidden="0"></li>
				<li data-id="id-9020" data-name="zzz" data-rating="5" data-hidden="0"></li>
			</ul>
		</div>
	</div>
</div>

<div id="status">
<div class="rating">
    <input type="radio" id="star5" name="rating" value="5" /><label for="star5" title="5 stars">5 stars</label>
    <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="4 stars">4 stars</label>
    <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="3 stars">3 stars</label>
    <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="2 stars">2 stars</label>
    <input type="radio" id="star1" name="rating" value="1" /><label for="star1" title="1 star">1 star</label>
    <input type="radio" id="star0" name="rating" value="0" /><label for="star0" title="0 stars">0 stars</label>
</div>
<ul>
	<li class="date"></li>
	<li class="name"></li>
	<li class="number">0 items selected</li>
	<li class="dimensions"></li>
	<li class="bytes"></li>
</ul>
<div class="hideImage"><input type="checkbox" id="hidden" name="hidden" value="1" /><label for="hidden" title="Hidden">Hidden</label></div>
<div id="loading"></div>
</div>
