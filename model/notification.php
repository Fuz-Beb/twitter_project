<?php
namespace Model\Notification;
use \Db;
use \PDOException;
/**
 * Notification model
 *
 * This file contains every db action regarding the notifications
 */

/**
 * Get a liked notification in db
 * @param uid the id of the user in db
 * @return a list of objects for each like notification
 * @warning the post attribute is a post object
 * @warning the liked_by attribute is a user object
 * @warning the date attribute is a DateTime object
 * @warning the reading_date attribute is either a DateTime object or null (if it hasn't been read)
 */
function get_liked_notifications($uid) {

  try {
      $i = 0;
      $db = \Db::dbc();
      $sth = $db->prepare("SELECT `AIMER`.`ID_TWEET`,`DATE_NOTIF`, `DATE_READ` FROM `AIMER` INNER JOIN TWEET ON `AIMER`.`ID_TWEET` = `TWEET`.`ID_TWEET` WHERE `TWEET`.`ID_USER` = :uid");
      $sth->execute(array(':uid' => $uid));

      if($sth->rowCount() < 1)
          return $arrayObj = [];

      $arrayObj[] = (object) array();

      while($array = $sth->fetch()) {

          $arrayObj[$i]->type = "liked";
          $arrayObj[$i]->post = \Model\Post\get($array[0]);
          $arrayObj[$i]->liked_by = \Model\Post\get_likes($array[0]);
          $arrayObj[$i]->date = new \DateTime($array[1]);

          if($array[2] == NULL)
              $arrayObj[$i]->reading_date = NULL;
          else
              $arrayObj[$i]->reading_date = new \DateTime($array[2]);

          $i++;
      }
      return $arrayObj;

  } catch (\PDOException $e) {
      print $e->getMessage();
      return NULL;
  }
}

/**
 * Mark a like notification as read (with date of reading)
 * @param pid the post id that has been liked
 * @param uid the user id that has liked the post
 * @return true if everything went ok, false else
 */
function liked_notification_seen($pid, $uid) {

  try {
      $db = \Db::dbc();
      $sth = $db->prepare("SELECT `NOTIF` FROM `AIMER` WHERE `ID_TWEET` = :uid AND `ID_USER` = :pid");
      $sth->execute(array(':uid' => $uid, ':pid' => $pid));

      $respond = $sth->fetch();

      if($respond = 1)
      {
          $db = \Db::dbc();
          $sth = $db->prepare("UPDATE `AIMER` SET `NOTIF` = '0', `DATE_READ` = CURRENT_TIME() WHERE `AIMER`.`ID_TWEET` = :pid AND `AIMER`.`ID_USER` = :uid;");
          $sth->execute(array(':uid' => $uid, ':pid' => $pid));
      }

      return true;

  } catch (\PDOException $e) {
      print $e->getMessage();
      return false;
  }
}

/**
 * Get a mentioned notification in db
 * @param uid the id of the user in db
 * @return a list of objects for each like notification
 * @warning the post attribute is a post object
 * @warning the mentioned_by attribute is a user object
 * @warning the reading_date object is either a DateTime object or null (if it hasn't been read)
 */
function get_mentioned_notifications($uid) {

  try {
      $i = 0;
      $db = \Db::dbc();
      $sth = $db->prepare("SELECT `MENTIONNER`.`ID_TWEET`, `DATE_NOTIF`, `DATE_READ`, `TWEET`.`ID_USER` AS AUTEUR FROM `MENTIONNER` INNER JOIN `TWEET` ON `MENTIONNER`.`ID_TWEET` = `TWEET`.`ID_TWEET` WHERE `MENTIONNER`.`ID_USER` = :uid");
      $sth->execute(array(':uid' => $uid));

      if($sth->rowCount() < 1)
          return $arrayObj = [];

      $arrayObj[] = (object) array();

      while($array = $sth->fetch()) {
          $arrayObj[$i]->type = "mentioned";
          $arrayObj[$i]->post = \Model\Post\get($array[0]);
          $arrayObj[$i]->mentioned_by = \Model\User\get($array[3]);
          $arrayObj[$i]->date = new \DateTime($array[1]);

          // PERMET DE METTRE A NULL SI PAS D'ARGUMENT SINON METTRE VALEUR
          if($array[2] == NULL)
              $arrayObj[$i]->reading_date = NULL;
          else
              $arrayObj[$i]->reading_date = new \DateTime($array[2]);

          $i++;
      }

      return $arrayObj;

  } catch (\PDOException $e) {
      print $e->getMessage();
      return NULL;
  }
}

/**
 * Mark a mentioned notification as read (with date of reading)
 * @param uid the user that has been mentioned
 * @param pid the post where the user was mentioned
 * @return true if everything went ok, false else
 */
function mentioned_notification_seen($uid, $pid) {

  try {
      $db = \Db::dbc();
      $sth = $db->prepare("SELECT `NOTIF` FROM `MENTIONNER` WHERE `ID_TWEET` = :uid AND `ID_USER` = :pid");
      $sth->execute(array(':uid' => $uid, ':pid' => $pid));

      $respond = $sth->fetch();

      if($respond = 1)
      {
          $db = \Db::dbc();
          $sth = $db->prepare("UPDATE `MENTIONNER` SET `NOTIF` = '0', `DATE_READ` = CURRENT_TIME() WHERE `MENTIONNER`.`ID_TWEET` = :pid AND `MENTIONNER`.`ID_USER` = :uid;");
          $sth->execute(array(':uid' => $uid, ':pid' => $pid));
      }

      return true;

  } catch (\PDOException $e) {
      print $e->getMessage();
      return false;
  }
}

/**
 * Get a followed notification in db
 * @param uid the id of the user in db
 * @return a list of objects for each like notification
 * @warning the user attribute is a user object which corresponds to the user following.
 * @warning the reading_date object is either a DateTime object or null (if it hasn't been read)
 */
function get_followed_notifications($uid) {

  try {
      $i = 0;
      $db = \Db::dbc();
      $sth = $db->prepare("SELECT `ID_USER`, `DATE_NOTIF`, `DATE_READ` FROM `SUIVRE` WHERE `ID_USER_1` = :uid");
      $sth->execute(array(':uid' => $uid));

      if($sth->rowCount() < 1)
          return $arrayObj = [];

      $arrayObj[] = (object) array();

      while($array = $sth->fetch()) {

          $arrayObj[$i]->type = "followed";
          $arrayObj[$i]->user = \Model\User\get($array[0]);
          $arrayObj[$i]->date = new \DateTime($array[1]);

          // PERMET DE METTRE A NULL SI PAS D'ARGUMENT SINON METTRE VALEUR
          if($array[2] == NULL)
              $arrayObj[$i]->reading_date = NULL;
          else
              $arrayObj[$i]->reading_date = new \DateTime($array[2]);

          $i++;
      }

      return $arrayObj;

  } catch (\PDOException $e) {
      print $e->getMessage();
      return NULL;
  }
}

/**
 * Mark a followed notification as read (with date of reading)
 * @param followed_id the user id which has been followed
 * @param follower_id the user id that is following
 * @return true if everything went ok, false else
 */
function followed_notification_seen($followed_id, $follower_id) {

  try {
      $db = \Db::dbc();
      $sth = $db->prepare("SELECT `NOTIF` FROM `SUIVRE` WHERE `ID_USER` = :follower_id AND `ID_USER_1` = :followed_id");
      $sth->execute(array(':followed_id' => $followed_id, ':follower_id' => $follower_id));

      $respond = $sth->fetch();

      if($respond = 1)
      {
          $db = \Db::dbc();
          $sth = $db->prepare("UPDATE `SUIVRE` SET `NOTIF` = '0', `DATE_READ` = CURRENT_TIME() WHERE `SUIVRE`.`ID_USER` = :follower_id AND `SUIVRE`.`ID_USER_1` = :followed_id;");
          $sth->execute(array(':followed_id' => $followed_id, ':follower_id' => $follower_id));
      }

      return true;

  } catch (\PDOException $e) {
      print $e->getMessage();
      return false;
  }
}

/**
 * Get all the notifications sorted by time (descending order)
 * @param uid the user id
 * @return a sorted list of every notifications objects
 */
function list_all_notifications($uid) {
    $ary = array_merge(get_liked_notifications($uid), get_followed_notifications($uid), get_mentioned_notifications($uid));
    usort(
        $ary,
        function($a, $b) {
            return $b->date->format('U') - $a->date->format('U');
        }
    );
    return $ary;
}

/**
 * Mark a notification as read (with date of reading)
 * @param uid the user to whom modify the notifications
 * @param notification the notification object to mark as seen
 * @return true if everything went ok, false else
 */
function notification_seen($uid, $notification) {
    switch($notification->type) {
        case "liked":
            return liked_notification_seen($notification->post->id, $notification->liked_by->id);
        break;
        case "mentioned":
            return mentioned_notification_seen($uid, $notification->post->id);
        break;
        case "followed":
            return followed_notification_seen($uid, $notification->user->id);
        break;
    }
    return false;
}
