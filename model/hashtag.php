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
    // Regarde si le hashtag fourni existe
    $db = \Db::dbc();
    $sql = "SELECT `ID_HASHTAGS` FROM `HASHTAGS` WHERE `NAME` LIKE :hashtag_name";
    $sth = $db->prepare($sql);
    $sth->execute(array(':hashtag_name' => $hashtag_name));

    // S'il n'existe pas
    if($sth->rowCount() < 1) {
        $sql = "INSERT INTO `HASHTAGS` (`ID_HASHTAGS`, `NAME`) VALUES (NULL, :hashtag_name);";
        $sth = $db->prepare($sql);
        $sth->execute(array(':hashtag_name' => $hashtag_name));

        $sql = "SELECT `ID_HASHTAGS` FROM `HASHTAGS` WHERE `NAME` LIKE :hashtag_name";
        $sth = $db->prepare($sql);
        $sth->execute(array(':hashtag_name' => $hashtag_name));
    }

    $respond = $sth->fetch();
    $sql = "INSERT INTO `CONCERNER` (`ID_TWEET`, `ID_HASHTAGS`) VALUES (:pid, :respond);";
    $sth = $db->prepare($sql);
    $sth->execute(array(':pid' => $pid, ':respond' => $respond[0]));

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
    $i = 0;
    $db = \Db::dbc();
    $sql = "SELECT NAME FROM `HASHTAGS`";
    $sth = $db->query($sql);

    $array = array();
    while ($result = $sth->fetch()) {
        $array[$i] = $result[0];
        $i++;
    }
    return $array;

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

    try {
        $i = 0;
        $db = \Db::dbc();
        $sth = $db->prepare("SELECT `NAME` FROM `HASHTAGS` NATURAL JOIN `CONCERNER` GROUP BY `CONCERNER`.`ID_HASHTAGS` ORDER BY COUNT(`ID_TWEET`) DESC LIMIT {$length}");
        $sth->execute();

        $array = array();
        while ($result = $sth->fetch()) {
            $array[$i] = $result[0];
            $i++;
        }

    return $array;

    } catch (\PDOException $e) {
    print $e->getMessage();
    return NULL;
    }
}

/**
 * Get posts for a hashtag
 * @param hashtag the hashtag name
 * @return a list of posts objects or null if the hashtag doesn't exist
 */
function get_posts($hashtag_name) {

// Propable qu'elle ne fonctionne pas avec l'histoire des objets postObj

  try {
    $i = 0;
    $db = \Db::dbc();
    $sql="SELECT `ID_TWEET` FROM `CONCERNER` INNER JOIN `HASHTAGS` ON `CONCERNER`.`ID_HASHTAGS` = `HASHTAGS`.`ID_HASHTAGS` AND `HASHTAGS`.`NAME` = :hashtag_name";
    $sth = $db->prepare($sql);
    $sth->execute(array(':hashtag_name' => $hashtag_name));

    if($sth->rowCount() < 1)
        return NULL;

    /* Récupération des objects des posts du Hashtag choisi en param */
    $postObj[] = (object) array();
    $postObj = [];

    while($result = $sth->fetch()) {
        $postObj[$i] = \Model\Post\get_with_joins($result[0]);
        $i++;
    }

    return $postObj;

  } catch (\PDOException $e) {
  print $e->getMessage();
  return NULL;
  }

}

/** Get related hashtags
 * @param hashtag_name the hashtag name
 * @param length the size of the returned list at most
 * @return an array of hashtags names
 */
function get_related_hashtags($hashtag_name, $length) {

  try {
    $i = 0;
    $db = \Db::dbc();
    $sth = $db->prepare("SELECT `NAME` FROM `HASHTAGS` WHERE `NAME` <> :hashtag_name AND `ID_HASHTAGS` IN ( SELECT `ID_HASHTAGS` FROM `CONCERNER` WHERE `ID_TWEET` IN ( SELECT `ID_TWEET` FROM `CONCERNER` GROUP BY `ID_TWEET` HAVING (COUNT(`ID_TWEET`) > 1) )) LIMIT {$length}");
    $sth->execute(array(':hashtag_name' => $hashtag_name));

    $array = array();
    while ($result = $sth->fetch()) {
        $array[$i] = $result[0];
        $i++;
    }

    return $array;

  } catch (\PDOException $e) {
  print $e->getMessage();
  return NULL;
}

}


