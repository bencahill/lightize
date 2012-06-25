CREATE TABLE image(
	id INTEGER PRIMARY KEY NOT NULL,
	name TEXT NOT NULL,
	directoryId INTEGER NOT NULL,
	date TEXT,
	info TEXT,
	rating INTEGER NOT NULL,
	edits TEXT,
	UNIQUE (name, directoryId),
	FOREIGN KEY(directoryId) REFERENCES directory(id) ON DELETE CASCADE
);

CREATE TABLE directory(
	id INTEGER PRIMARY KEY NOT NULL,
	name TEXT NOT NULL,
	date INTEGER NOT NULL
);

CREATE TABLE album(
	id INTEGER PRIMARY KEY NOT NULL,
	name TEXT NOT NULL,
	date INTEGER NOT NULL
);

CREATE TABLE imageAlbum(
	imageId INTEGER NOT NULL,
	albumId INTEGER NOT NULL,
	FOREIGN KEY(imageId) REFERENCES image(id) ON DELETE CASCADE,
	FOREIGN KEY(albumId) REFERENCES album(id) ON DELETE CASCADE,
	PRIMARY KEY(imageId, albumId)
);
