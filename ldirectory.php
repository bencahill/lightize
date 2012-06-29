<?php

class LDirectory {

	public $name;
	public $id;
	private $imageDir;
	private $cacheDir;

	public function __construct( $name, $autoAdd = true ) {
		global $db;
		// set the name as a variable so we can access it later
		$this->name = $name;
		$this->id = $this->getId();
		$this->imageDir = L_IMAGE_DIR."/$this->name";
		$this->cacheDir = L_CACHE_DIR."/$this->name";
		$this->autoAdd = $autoAdd;
		if ( $this->id <= 0 && $this->autoAdd ) {
			$ins = $db->prepare( "INSERT INTO directory (name, date) VALUES (:name, :date)" );
			$ins->execute( array( "name" => $this->name, "date" => time() ) );
			$this->id = $this->getId();
		}
	}

	public function getImages() {
		global $db;
		$sth = $db->prepare( "SELECT id,name,date,info,rating,edits FROM image WHERE directoryId=?" );
		$sth->execute( array( $this->id ) );
		$result = $sth->fetchAll( PDO::FETCH_CLASS, 'Image' );
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
		$sth = $db->prepare( "SELECT name FROM image WHERE directoryId=?" );
		$sth->execute( array( $this->id ) );
		$newFiles = array_diff( $files, $sth->fetchAll( PDO::FETCH_COLUMN, 0 )?:array() );
		sort( $newFiles );
		return $newFiles;
	}

	public function addImages( $images ) {
		global $db;
		// check if $images is a non-empty array
		if ( is_array( $images ) && ! empty( $images ) ) {

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
		// initialize the "Unknown" event for this dir, possibly creating it
		$event = new Event( "Unknown", $this->id );
		$event->add();
		foreach ( $images as $image_name ) {
			// instantiate the image to get EXIF info
			$image = new Image( $image_name, $this->name );
			$image->getInfo();
			$sth = $db->prepare( "INSERT INTO image (name, rating, directoryId, hiddenInDirectory, info, date, eventId) VALUES (:name, :rating, :directoryId, :hiddenInDirectory, :info, :date, :eventId)" );
			$sth->execute( array( "name" => $image->name, "rating" => 0, "directoryId" => $this->id, "hiddenInDirectory" => 0, "info" => serialize( $image->info ), "date" => $image->date, "eventId" => $event->id ) );
		}
	}

	private function getId() {
		global $db;
		$sth = $db->prepare( "SELECT id FROM directory WHERE name=?" );
		$sth->execute( array( $this->name ) );
		return $sth->fetchColumn();
	}

}

?>
