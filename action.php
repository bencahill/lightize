<?php

include 'bootstrap.php';

extract( $_GET );

if( isset( $images ) && isset( $directory ) ) {
	foreach( $images as $image ) {
		if( isset( $rating ) || isset( $hidden ) ) {
			$im = new Image( $image, $directory );
			if( isset( $rating ) ) {
				$im->setRating( $rating );
			}
			if( isset( $hidden ) ) {
				$im->setHidden( $hidden );
			}
		} elseif( isset( $addToAlbum ) || isset( $removeFromAlbum ) ) {
			$albumName = isset( $addToAlbum ) ? $addToAlbum : $removeFromAlbum;
			$alb = new Album( $albumName );
			if( isset( $addToAlbum ) ) {
				$alb->addImage( $image, $directory );
			}
			if( isset( $removeFromAlbum ) ) {
				$alb->removeImage( $image, $directory );
			}
		}
	}
}
