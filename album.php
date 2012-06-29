<?php

class Album {

	private $name;
	public $id;

	public function __construct( $name ) {
		global $db;
		$this->name = $name;
		$this->id = $this->getId();
	}

	public function add() {
		global $db;
		if ( empty( $this->id ) ) {
			$sth = $db->prepare( "INSERT INTO album (name, date) VALUES (:name, :date)" );
			$sth->execute( array( "name" => $this->name, "date" => time() ) );
			$this->id = $this->getId();
		}
	}

	public function remove() {
		global $db;
		if ( ! empty( $this->id ) ) {
			$sth = $db->prepare( "DELETE FROM album WHERE name=?" );
			$sth->execute( array( $this->name ) );
			$sth = $db->prepare( "DELETE FROM imageAlbum WHERE albumId=?" );
			$sth->execute( array( $this->id ) );
		} else {
			echo "Album was not in the database!";
		}
	}

	public function getImages() {
		global $db;
		$sth = $db->prepare( "SELECT id,name,date,info,rating,edits,imageAlbum.hiddenInAlbum FROM image INNER JOIN imageAlbum ON image.id = imageAlbum.imageId WHERE albumId=?" );
		$sth->execute( array( $this->id ) );
		$result = $sth->fetchAll( PDO::FETCH_CLASS, 'Image' );
		foreach ( $result as &$image ) {
			$image->info = unserialize( $image->info );
		}
		return $result;
	}

	public function addImage( $image_name, $dirName ) {
		global $db;
		$image = new Image( $image_name, $dirName );
		$sth = $db->prepare( "INSERT INTO imageAlbum (imageId, albumId, hiddenInAlbum) VALUES (:imageId, :albumId, :hiddenInAlbum)" );
		$sth->execute( array( "imageId" => $image->id, "albumId" => $this->id, "hiddenInAlbum" => 0 ) );
	}

	public function removeImage( $image_name, $dirId ) {
		global $db;
		$image = new Image( $image_name, $dirId );
		$sth = $db->prepare( "DELETE FROM imageAlbum WHERE imageId=:imageId AND albumId=:albumId" );
		$sth->execute( array( "imageId" => $image->id, "albumId" => $this->id ) );
	}

	private function getId() {
		global $db;
		$sth = $db->prepare( "SELECT id FROM album WHERE name=?" );
		$sth->execute( array( $this->name ) );
		return $sth->fetchColumn();
	}

}

?>
