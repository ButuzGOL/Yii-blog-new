CREATE TABLE User
(
    id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(32) NOT NULL,
    email VARCHAR(64) NOT NULL,
    url VARCHAR(64),
    status INTEGER NOT NULL,
    banned INTEGER NOT NULL,
    avatar VARCHAR(20),
    passwordLost VARCHAR(10),
    confirmRegistration VARCHAR(10),
    about TEXT
);

CREATE TABLE Post
(
    id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(128) NOT NULL,
    titleLink VARCHAR(128),
    slug VARCHAR(32) NOT NULL,
    contentshort TEXT NOT NULL,
    contentbig TEXT,
    tags TEXT,
    status INTEGER NOT NULL,
    createTime INTEGER,
    updateTime INTEGER,
    commentCount INTEGER DEFAULT 0,
    categoryId INTEGER,
    authorId INTEGER NOT NULL,
    authorName VARCHAR(50) NOT NULL
);

CREATE TABLE Comment
(
    id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
    content TEXT NOT NULL,
    contentDisplay TEXT,
    status INTEGER NOT NULL,
    createTime INTEGER,
    authorName VARCHAR(50) NOT NULL,
    email VARCHAR(64) NOT NULL,
    postId INTEGER NOT NULL,
    authorId INTEGER,
    CONSTRAINT FK_comment_post FOREIGN KEY (postId)
        REFERENCES Post (id) ON DELETE CASCADE ON UPDATE RESTRICT
);

CREATE TABLE Tag
(
    id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(64) NOT NULL
);

CREATE TABLE PostTag
(
    postId INTEGER NOT NULL,
    tagId INTEGER NOT NULL,
    PRIMARY KEY (postId, tagId),
    CONSTRAINT FK_post FOREIGN KEY (postId)
        REFERENCES Post (id) ON DELETE CASCADE ON UPDATE RESTRICT,
    CONSTRAINT FK_tag FOREIGN KEY (tagId)
        REFERENCES Tag (id) ON DELETE CASCADE ON UPDATE RESTRICT
);

CREATE TABLE Bookmark
(
    id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
    postId INTEGER NOT NULL,
    userId INTEGER NOT NULL,
    CONSTRAINT FK_bookmark_post FOREIGN KEY (postId)
        REFERENCES Post (id) ON DELETE CASCADE ON UPDATE RESTRICT,
    CONSTRAINT FK_bookmark_user FOREIGN KEY (userId)
        REFERENCES User (id) ON DELETE CASCADE ON UPDATE RESTRICT
);

CREATE TABLE File
(
    id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(64) NOT NULL,
    type VARCHAR(32),
    createTime INTEGER,
    alt VARCHAR(32)
);

CREATE TABLE Category
(
    id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(64) NOT NULL,
    slug VARCHAR(32) NOT NULL
);

CREATE TABLE Page
(
    id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(128) NOT NULL,
    slug VARCHAR(32) NOT NULL,
    content TEXT NOT NULL,
    status INTEGER NOT NULL,
    createTime INTEGER,
    updateTime INTEGER,
    authorId INTEGER NOT NULL,
    authorName VARCHAR(50) NOT NULL,
    CONSTRAINT FK_page_author FOREIGN KEY (authorId)
        REFERENCES User (id) ON DELETE CASCADE ON UPDATE RESTRICT
);

INSERT INTO User (username, password, email, status, banned) VALUES ('r0n9.GOL', md5('1'), 'ron9.gol@gmail.com', 0, 0);

