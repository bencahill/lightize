<?php

class Image {

	public $name;
	public $id;
	private $dirName;
	public $date;
	public $info;

	public function __construct( $name, $dirName ) {
		global $db;
		$this->name = $name;
		$this->dirName = $dirName;
		$dir = new LDirectory( $dirName );
		$this->id = $db->get_var( "SELECT id FROM image WHERE name='$this->name' AND directoryId=$dir->id" );
	}

	public function getInfo() {
		$info = eval( "return ".shell_exec( "exiftool -php -q -d '%s' -model -focallength -shutterspeed -aperture -iso -datetimeoriginal -exposurecompensation -lens -whitebalance -flash -shootingmode -focusmode ".Config::imagePath."/".$dirName."/".$this->name ) )[0];

		$this->date = $info['DateTimeOriginal'];
		unset( $info['DateTimeOriginal'] );

		$this->info = $info;
	}

}

?>
