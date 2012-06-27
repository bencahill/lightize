<?php

class Event {

	private $name;
	private $dirId;
	public $id;

	public function __construct( $name, $dirId ) {
		$this->name = $name;
		$this->dirId = $dirId;
		if ( getId() ) {
			$this->id = $db->get_var( null );
		}
	}

	public function add() {
		if ( empty( $this->id ) ) {
			$db->query( "INSERT INTO event (name, directoryId, date) VALUES ('$this->name', $this->dirId, ".time().")" );
			$this->id = getId();
		}
	}

	public function remove() {
		if ( ! empty( $this->id ) ) {
			$db->query( "DELETE FROM event WHERE name='$this->name' AND directoryId=$this->dirId" );
			$uevent = getUnknown();
			$db->query( "UPDATE image SET eventId=$uevent->id WHERE eventId=$this->id AND directoryId=$this->dirId" );
		} else {
			echo "Event was not in the database!";
		}
	}

	public function addImage( $image_name ) {
		$db->query( "UPDATE image SET eventId=$this->id WHERE name='$image_name' AND directoryId=$this->dirId" );
	}

	public function removeImage( $image_name ) {
		$uevent = getUnknown();
		$db->query( "UPDATE image SET eventId=$uevent->id WHERE name='$image_name' AND directoryId=$this->dirId" );
	}

	private function getId() {
		return $db->get_var( "SELECT id FROM event WHERE name='$this->name' AND directoryId='$this->dirId'" );
	}

	private function getUnknown() {
		$uevent = new Event( "Unknown", $this->dirId );
		$uevent->add();
		return $uevent;
	}

}

?>
