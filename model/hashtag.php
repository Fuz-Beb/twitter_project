<?php
namespace Model\Hashtag;
use \Db;
use \PDOException;
/**
 * Hashtag model
 *
 * This file contains every db action regarding the hashtags
 */

/**
 * Attach a hashtag to a post
 * @param pid the post id to which attach the hashtag
 * @param hashtag_name the name of the hashtag to attach
 * @return true or false (if something went wrong)
 */
function attach($pid, $hashtag_name) {

  try {
    $db = \Db::dbc();
    $sql = "SELECT `ID_HASHTAGS` FROM `HASHTAGS` WHERE `NAME` LIKE ':hashtag_name'";
    $sth = $db->prepare($sql);
    $sth->execute(array(':hashtag_name' => $hashtag_name));

    if($sth->rowCount() < 1) {
        $sql = "INSERT INTO `HASHTAGS` (`ID_HASHTAGS`, `NAME`) VALUES ('0', ':hashtag_name');";
        $sth = $db->prepare($sql);
        $sth->execute(array(':hashtag_name' => $hashtag_name));

        $sql = "SELECT `ID_HASHTAGS` FROM `HASHTAGS` WHERE `NAME` LIKE ':hashtag_name'";
        $sth = $db->prepare($sql);
        $sth->execute(array(':hashtag_name' => $hashtag_name));
    }

    $respond = $sth->fetch();
    $sql = "SELECT `ID_TWEET` FROM `CONCERNER` WHERE `ID_HASHTAGS` LIKE ':respond'";
    $sth = $db->prepare($sql);
    $sth->execute(array(':respond' => $respond[0]));

    if($sth->rowCount() < 1) {
        $sql = "INSERT INTO `CONCERNER` (`ID_TWEET`, `ID_HASHTAGS`) VALUES (:pid, :respond);";
        $sth = $db->prepare($sql);
        $sth->execute(array(':pid' => $pid, ':respond' => $respond[0]));
    }

    else {
      return false;
    }

    return true;

  } catch (\PDOException $e) {
  print $e->getMessage();
  return false;
  }

}

/**
 * List hashtags
 * @return a list of hashtags names
 */
function list_hashtags() {

  try {
    $db = \Db::dbc();
    $sql = "SELECT NAME FROM `HASHTAGS`";
    $sth = $db->prepare($sql);
    $db->query($sql);

    $result = $sth->fetch();

    return $result[0];

  } catch (\PDOException $e) {
  print $e->getMessage();
  return NULL;
  }

}

/**
 * List hashtags sorted per popularity (number of posts using each)
 * @param length number of hashtags to get at most
 * @return a list of hashtags
 */
function list_popular_hashtags($length) {
    return ["Hallo"];
}

/**
 * Get posts for a hashtag
 * @param hashtag the hashtag name
 * @return a list of posts objects or null if the hashtag doesn't exist
 */
function get_posts($hashtag_name) {
    return [\Model\Post\get(1)];
}

/** Get related hashtags
 * @param hashtag_name the hashtag name
 * @param length the size of the returned list at most
 * @return an array of hashtags names
 */
function get_related_hashtags($hashtag_name, $length) {
    return ["Hello"];
}
