<?php

class Event {

	private $name;
	private $dirId;
	private $dirName;
	public $id;

	public function __construct( $name, $dirName ) {
		global $db;
		$this->name = $name;
		$this->dirName = $dirName;
		$dir = new LDirectory( $dirName );
		$this->dirId = $dir->id;
		$this->id = $this->getId();
	}

	public function add() {
		global $db;
		if ( empty( $this->id ) ) {
			echo $this->id;
			$sth = $db->prepare( "INSERT INTO event (name, directoryId, date) VALUES (:name, :directoryId, :date)" );
			$sth->execute( array( "name" => $this->name, "directoryId" => $this->dirId, "date" => time() ) );
			$this->id = $this->getId();
		}
	}

	public function remove() {
		global $db;
		if ( ! empty( $this->id ) ) {
			$sth = $db->prepare( "DELETE FROM event WHERE name=:name AND directoryId=:directoryId" );
			$sth->execute( array( "name" => $this->name, "directoryId" => $this->dirId ) );
			$uevent = $this->getUnknown();
			$sth = $db->prepare( "UPDATE image SET eventId=:ueventId WHERE eventId=:eventId AND directoryId=:directoryId" );
			$sth->execute( array( "ueventId" => $uevent->id, "eventId" => $this->id, "directoryId" => $this->dirId ) );
		} else {
			echo "Event was not in the database!";
		}
	}

	public function addImage( $image_name ) {
		global $db;
		$sth = $db->prepare( "UPDATE image SET eventId=:eventId WHERE name=:name AND directoryId=:directoryId" );
		$sth->execute( array( "eventId" => $this->id, "name" => $image_name, "directoryId" => $this->dirId ) );
	}

	public function removeImage( $image_name ) {
		global $db;
		$uevent = $this->getUnknown();
		$sth = $db->prepare( "UPDATE image SET eventId=:eventId WHERE name=:name AND directoryId=:directoryId" );
		$sth->execute( array( "eventId" => $uevent->id, "name" => $image_name, "directoryId" => $this->dirId ) );
	}

	private function getId() {
		global $db;
		$sth = $db->prepare( "SELECT id FROM event WHERE name=:name AND directoryId=:directoryId" );
		$sth->execute( array( "name" => $this->name, "directoryId" => $this->dirId ) );
		return $sth->fetchColumn();
	}

	private function getUnknown() {
		$uevent = new Event( "Unknown", $this->dirName );
		$uevent->add();
		return $uevent;
	}

}

?>
