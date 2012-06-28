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
			$db->query( "INSERT INTO album (name, date) VALUES ('$this->name', ".time().")" );
			$this->id = $this->getId();
		}
	}

	public function remove() {
		global $db;
		if ( ! empty( $this->id ) ) {
			$db->query( "DELETE FROM album WHERE name='$this->name'" );
			$db->query( "DELETE FROM imageAlbum WHERE albumId=$this->id" );
		} else {
			echo "Album was not in the database!";
		}
	}

	public function addImage( $image_name, $dirId ) {
		global $db;
		$image = new Image( $image_name, $dirId );
		$db->query( "INSERT INTO imageAlbum (imageId, albumId) VALUES ($image->id, $this->id)" );
	}

	public function removeImage( $image_name, $dirId ) {
		global $db;
		$image = new Image( $image_name, $dirId );
		$db->query( "DELETE FROM imageAlbum WHERE albumId=$this->id AND imageId=$image->id" );
	}

	private function getId() {
		global $db;
		return $db->get_var( "SELECT id FROM album WHERE name='$this->name'" );
	}

}

?>
