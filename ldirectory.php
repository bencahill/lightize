<?php

class LDirectory {

	public $name;
	public $id;
	private $imageDir;
	private $cacheDir;

	public function __construct( $name ) {
		global $db;
		// set the name as a variable so we can access it later
		$this->name = $name;
		$this->id = $db->get_var( "SELECT id FROM directory WHERE name='$this->name'" );
		$this->imageDir = L_IMAGE_DIR."/$this->name";
		$this->cacheDir = L_CACHE_DIR."/$this->name";
	}

	public function getImages() {
		global $db;
		$result = $db->get_results( "SELECT id,name,date,info,rating,edits FROM image WHERE directoryId='$this->id'" );
		foreach ( $result as &$image ) {
			$image->info = unserialize( $image->info );
		}
		return $result;
	}

	public function getNewImages() {
		global $db;
		$files = array ( );
		$dirHandle = opendir( $this->imageDir );
		while ( $file = readdir($dirHandle) ) {
			if ( preg_match( '/\.jpg$/i', $file ) == 1 && $file != '.' && $file != '..' ) {
				$files[] = $file;
			}
		}
		closedir($dirHandle);
		$newFiles = array_diff( $files, $db->get_results( "SELECT name FROM image WHERE directoryId='$this->id'" )?:array() );
		sort( $newFiles );
		return $newFiles;
	}

	public function addImages( $images ) {
		global $db;
		// check if $images is a non-empty array
		if ( is_array( $images ) && ! empty( $images ) ) {
			// check if this dir does not exist in the db; if so, run the query again
			if ( ! $db->get_var( "SELECT id FROM directory WHERE name='$this->name'" ) ) {
				$db->query( "INSERT INTO directory (name, date) VALUES ('$this->name', ".time().")" );
				$db->get_var( "SELECT id FROM directory WHERE name='$this->name'" );
			}
			// set the id variable with ezSQL's cached results
			$this->id = $db->get_var( null );

			// initialize the "Unknown" event for this dir, possibly creating it
			$event = new Event( "Unknown", $this->id );
			// ensure that the cache dir exists
			mkdir( $this->cacheDir, 0700, true );

			$this->createThumbnails( $images );

			$this->addToDb( $images );
		} elseif ( is_array( $images ) ) {
			echo "\$images array is empty!";
		} else {
			echo "\$images is not an array!";
		}
	}

	private function createThumbnails( $images ) {
		foreach ( $images as $image_name ) {
			$imagePath = pathinfo( "$this->imageDir/$image_name" );
			$gimage = new Gmagick( "$this->imageDir/$image_name" );
			$gimage->thumbnailImage( 900, 600, true );
			$gimage->writeImage( "$this->cacheDir/{$imagePath['filename']}_900.{$imagePath['extension']}" );
			$gimage->thumbnailImage( 150, 150, true );
			$gimage->writeImage( "$this->cacheDir/{$imagePath['filename']}_150.{$imagePath['extension']}" );
		}
	}

	private function addToDb( $images ) {
		global $db;
		foreach ( $images as $image_name ) {
			// instantiate the image to get EXIF info
			$image = new Image( $image_name, $this->name );
			$image->getInfo();
			$db->query( "INSERT INTO image (name, rating, directoryId, info, date, eventId) VALUES ('$image->name', 0, $this->id, '".serialize( $image->info )."', '$image->date', '$event->id'" );
		}
	}

}

?>
