CREATE DATABASE IF NOT EXISTS dbproject_app;
USE dbproject_app;
# -----------------------------------------------------------------------------
#       TABLE : UTILISATEUR
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS UTILISATEUR
 (
   ID_USER INTEGER NOT NULL AUTO_INCREMENT ,
   USERNAME CHAR(32) NOT NULL  ,
   NAME CHAR(32) NOT NULL  ,
   EMAIL CHAR(32) NOT NULL  ,
   PASSWORD CHAR(150) NOT NULL  ,
   AVATAR CHAR(32) NOT NULL  ,
   SIGN_UP DATE NOT NULL  
   , PRIMARY KEY (ID_USER) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : TWEET
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS TWEET
 (
   ID_TWEET INTEGER NOT NULL AUTO_INCREMENT ,
   ID_USER INTEGER NOT NULL  ,
   ID_TWEET_REPONSE INTEGER NOT NULL  ,
   CONTENT CHAR(140) NOT NULL  ,
   DATE_PUBLI CHAR(25) NOT NULL  
   , PRIMARY KEY (ID_TWEET) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : HASHTAGS
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS HASHTAGS
 (
   ID_HASHTAGS INTEGER NOT NULL AUTO_INCREMENT ,
   NAME CHAR(32) NOT NULL  
   , PRIMARY KEY (ID_HASHTAGS) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : SUIVRE
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS SUIVRE
 (
   ID_USER INTEGER NOT NULL  ,
   ID_USER_1 INTEGER NOT NULL  ,
   NOTIF BOOL NOT NULL  
   , PRIMARY KEY (ID_USER,ID_USER_1) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : MENTIONNER
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS MENTIONNER
 (
   ID_TWEET INTEGER NOT NULL  ,
   ID_USER INTEGER NOT NULL  ,
   NOTIF BOOL NOT NULL  
   , PRIMARY KEY (ID_TWEET,ID_USER) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : AIMER
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS AIMER
 (
   ID_TWEET INTEGER NOT NULL  ,
   ID_USER INTEGER NOT NULL  ,
   NOTIF BOOL NOT NULL  
   , PRIMARY KEY (ID_TWEET,ID_USER) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : CONCERNER
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS CONCERNER
 (
   ID_TWEET INTEGER NOT NULL  ,
   ID_HASHTAGS INTEGER NOT NULL  
   , PRIMARY KEY (ID_TWEET,ID_HASHTAGS) 
 ) 
 comment = "";


# -----------------------------------------------------------------------------
#       CREATION DES REFERENCES DE TABLE
# -----------------------------------------------------------------------------


ALTER TABLE TWEET 
  ADD FOREIGN KEY FK_TWEET_UTILISATEUR (ID_USER)
      REFERENCES UTILISATEUR (ID_USER) ;


ALTER TABLE TWEET 
  ADD FOREIGN KEY FK_TWEET_TWEET (ID_TWEET_REPONSE)
      REFERENCES TWEET (ID_TWEET) ;


ALTER TABLE SUIVRE 
  ADD FOREIGN KEY FK_SUIVRE_UTILISATEUR (ID_USER)
      REFERENCES UTILISATEUR (ID_USER) ;


ALTER TABLE SUIVRE 
  ADD FOREIGN KEY FK_SUIVRE_UTILISATEUR1 (ID_USER_1)
      REFERENCES UTILISATEUR (ID_USER) ;


ALTER TABLE MENTIONNER 
  ADD FOREIGN KEY FK_MENTIONNER_TWEET (ID_TWEET)
      REFERENCES TWEET (ID_TWEET) ;


ALTER TABLE MENTIONNER 
  ADD FOREIGN KEY FK_MENTIONNER_UTILISATEUR (ID_USER)
      REFERENCES UTILISATEUR (ID_USER) ;


ALTER TABLE AIMER 
  ADD FOREIGN KEY FK_AIMER_TWEET (ID_TWEET)
      REFERENCES TWEET (ID_TWEET) ;


ALTER TABLE AIMER 
  ADD FOREIGN KEY FK_AIMER_UTILISATEUR (ID_USER)
      REFERENCES UTILISATEUR (ID_USER) ;


ALTER TABLE CONCERNER 
  ADD FOREIGN KEY FK_CONCERNER_TWEET (ID_TWEET)
      REFERENCES TWEET (ID_TWEET) ;


ALTER TABLE CONCERNER 
  ADD FOREIGN KEY FK_CONCERNER_HASHTAGS (ID_HASHTAGS)
      REFERENCES HASHTAGS (ID_HASHTAGS) ;

