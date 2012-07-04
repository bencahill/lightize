<?php

class Image {

	public $name;
	public $id;
	private $dirName;
	public $date;
	public $info;
	private $imagePath;

	public function __construct( $name = '', $dirName = '' ) {
		global $db;
		if ( ! empty( $name ) && ! empty( $dirName ) ) {
			$this->name = $name;
			$this->dirName = $dirName;
			$dir = new LDirectory( $this->dirName );
			$sth = $db->prepare( "SELECT id FROM image WHERE name=:name AND directoryId=:directoryId" );
			$sth->execute( array( "name" => $this->name, "directoryId" => $dir->id ) );
			$this->id = $sth->fetchColumn();
			$this->imagePath = L_IMAGE_DIR."/$this->dirName/$this->name";
		}
		$this->info = !empty($this->info)?unserialize($this->info):NULL;
		$pathInfo = pathinfo( $this->name );
		$this->thumbnail = $pathInfo['filename']."_180.".$pathInfo['extension'];
		$this->preview = $pathInfo['filename']."_900.".$pathInfo['extension'];
	}

	public function getInfo() {
		$info = eval( "return ".shell_exec( "exiftool -php -q -d '%s' -model -focallength -shutterspeed -aperture -iso -datetimeoriginal -exposurecompensation -lens -whitebalance -flash -shootingmode -focusmode '$this->imagePath'" ) );
		$info = $info[0];

		if( isset( $info['DateTimeOriginal'] ) ) {
			$this->date = $info['DateTimeOriginal'];
			unset( $info['DateTimeOriginal'] );
		} else {
			$this->date = filemtime( $this->imagePath );
		}

		$dimensions = getimagesize( $this->imagePath );
		$info['dimensions'] = array( 'width' => $dimensions[0], 'height' => $dimensions[1] );
		$info['sizeInBytes'] = filesize( $this->imagePath );

		$this->info = $info;
	}

}

?>
