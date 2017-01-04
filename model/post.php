<?php
namespace Model\Post;
use \Db;
use \PDOException;
use \DateTime;
/**
 * Post
 *
 * This file contains every db action regarding the posts
 */

/**
 * Get a post in db
 * @param id the id of the post in db
 * @return an object containing the attributes of the post or false if error
 * @warning the author attribute is a user object
 * @warning the date attribute is a DateTime object
 */
function get($id) {

    try {
        $db = \Db::dbc();

        $sth = $db->prepare("SELECT `ID_USER`, `CONTENT`, `DATE_PUBLI` FROM `TWEET` WHERE `ID_TWEET` = :id");
        $sth->execute(array(':id' => $id));

        if ($array = $sth->fetch())
        {
            $obj = (object) array();
            $obj->id = $id;
            $obj->text = $array[1];
            $obj->date = new \DateTime($array[2]);
            $obj->author = \Model\User\get($array[0]);

            return $obj;
        }
        else
            return NULL;

    } catch (\PDOException $e) {
        print $e->getMessage();
        return NULL;
    }
}

/**
 * Get a post with its likes, responses, the hashtags used and the post it was the response of
 * @param id the id of the post in db
 * @return an object containing the attributes of the post or false if error
 * @warning the author attribute is a user object
 * @warning the date attribute is a DateTime object
 * @warning the likes attribute is an array of users objects
 * @warning the hashtags attribute is an of hashtags objects
 * @warning the responds_to attribute is either null (if the post is not a response) or a post object
 */
function get_with_joins($id) {

    try {
        $db = \Db::dbc();

        /* Récupération des 4 premiers attribut */
        $obj = get($id);
        $sth = $db->prepare("SELECT `ID_USER` FROM `AIMER` WHERE `ID_TWEET` = :id");
        $sth->execute(array(':id' => $id));

        /* Récupération des objects des personnes qui ont like le post */
        $likes[] = (object) array();
        while($result = $sth->fetch()) {
            $likes[$i] = \Model\User\get($result[0]);
            $i++;
        }

        $obj->likes = $likes;
        $obj->hashtags = extract_hashtags($obj->text);

        /* Récupération de l'object du tweet si c'est une réponse */
        $sth = $db->prepare("SELECT `ID_TWEET_REPONSE` FROM `TWEET` WHERE `ID_TWEET` = :id");
        $sth->execute(array(':id' => $id));

        $respond = $sth->fetch();

        if ($respond[0] == false || $respond[0] == FALSE || $respond[0] == null || $respond[0] == NULL)
            $obj->responds_to = NULL;
        else
            $obj->responds_to = get($respond[0]);

        return $obj;

    } catch (\PDOException $e) {
        print $e->getMessage();
        return NULL;
    }
}

/**
 * Create a post in db
 * @param author_id the author user's id
 * @param text the message
 * @param mentioned_authors the array of ids of users who are mentioned in the post
 * @param response_to the id of the post which the creating post responds to
 * @return the id which was assigned to the created post, null if anything got wrong
 * @warning this function computes the date
 * @warning this function adds the mentions (after checking the users' existence)
 * @warning this function adds the hashtags
 * @warning this function takes care to rollback if one of the queries comes to fail.
 */
function create($author_id, $text, $response_to=null) {

    try {

        /* Calcul de la date */
        $date = new DateTime('NOW');
        $newDate = $date->format('Y-m-dTH:i:sP');

        $db = \Db::dbc();

        if ($response_to == NULL)
            $sql = "INSERT INTO `TWEET` (`ID_TWEET`, `ID_USER`, `ID_TWEET_REPONSE`, `CONTENT`, `DATE_PUBLI`) VALUES (NULL, '$author_id', NULL, '$text', '$newDate')";
        else
            $sql = "INSERT INTO `TWEET` (`ID_TWEET`, `ID_USER`, `ID_TWEET_REPONSE`, `CONTENT`, `DATE_PUBLI`) VALUES (NULL, '$author_id', '$response_to', '$text', '$newDate')";

        $db->query($sql);

        $sql = "SELECT `ID_TWEET` FROM `TWEET` WHERE `ID_USER` = :id AND `DATE_PUBLI` LIKE :datePubli";
        $sth = $db->prepare($sql);
        $sth->execute(array(':id' => $author_id, ':datePubli' => $newDate));
        $result = $sth->fetch();

        return $result[0];

    } catch (\PDOException $e) {
        print $e->getMessage();
        return NULL;
    }
}

/**
 * Get the list of used hashtags in message
 * @param text the message
 * @return an array of hashtags
 */
function extract_hashtags($text) {

    return array_map(
        function($el) { return substr($el, 1); },
        array_filter(
            explode(" ", $text),
            function($c) {
                return $c !== "" && $c[0] == "#";
            }
        )
    );
}

/**
 * Get the list of mentioned users in message
 * @param text the message
 * @return an array of usernames
 */
function extract_mentions($text) {

    return array_map(
        function($el) { return substr($el, 1); },
        array_filter(
            explode(" ", $text),
            function($c) {
                return $c !== "" && $c[0] == "@";
            }
        )
    );
}

/**
 * Mention a user in a post
 * @param pid the post id
 * @param uid the user id to mention
 * @return true if everything went ok, false else
 */
function mention_user($pid, $uid) {

    try {
        $db = \Db::dbc();

        $sql = "INSERT INTO `MENTIONNER` (`IDTWEET`, `IDUSER`, `NOTIF`) VALUES ('$pid', '$uid', '1')";
        $db->query($sql);

    } catch (\PDOException $e) {
        print $e->getMessage();
        return false;
    }

    return true;
}

/**
 * Get mentioned user in post
 * @param pid the post id
 * @return the array of user objects mentioned
 */
function get_mentioned($pid) {

    try {
        $i = 0;
        $db = \Db::dbc();
        $sth = $db->prepare("SELECT `IDUSER` FROM `MENTIONNER` WHERE `IDTWEET` = :pid");
        $sth->execute(array(':pid' => $pid));

        $arrayObj[] = (object) array();

        while($result = $sth->fetch()) {
            $arrayObj[$i] = get($result[0]);
            $i++;
        }

    } catch (\PDOException $e) {
        print $e->getMessage();
        return NULL;
    }

    return $arrayObj;
}

/**
 * Delete a post in db
 * @param id the id of the post to delete
 * @return true if the post has been correctly deleted, false else
 */
function destroy($id) {

    try {
        $db = \Db::dbc();
        $sql = "UPDATE `TWEET` SET `ID_TWEET_REPONSE`= NULL WHERE `ID_TWEET_REPONSE` = :id";
        $sth = $db->prepare($sql);
        $sth->execute(array(':id' => $id));

        $sql = "DELETE FROM `TWEET` WHERE `ID_TWEET` = :id";
        $sth = $db->prepare($sql);
        $sth->execute(array(':id' => $id));
        
    } catch (\PDOException $e) {
        print $e->getMessage();
        return false;
    }
        return true;
}

/**
 * Search for posts
 * @param string the string to search in the text
 * @return an array of find objects
 */
function search($string) {

    try {
        $db = \Db::dbc();
        $sql = "SELECT ID_TWEET FROM TWEET WHERE CONTENT = :string";
        $sth = $db->prepare($sql);
        $sth->execute(array(':string' => $string));
        $result = $sth->fetch();

    } catch (\PDOException $e) {
        print $e->getMessage();
        return NULL;
      }

        return get($result[0]);
}

/**
 * List posts
 * @param date_sorted the type of sorting on date (false if no sorting asked), "DESC" or "ASC" otherwise
 * @return an array of the objects of each post
 * @warning this function does not return the passwords
 */
function list_all($date_sorted=false) {

  try {
    $i = 0;
    $db = \Db::dbc();

    if($date_sorted == 'ASC' || $date_sorted == 'DESC'){
        $sql = "SELECT `ID_TWEET` FROM `TWEET` ORDER BY DATE_PUBLI :date_sorted";
        $sth = $db->prepare($sql);
        $sth->execute(array(':date_sorted' => $date_sorted));
      }

    elseif($date_sorted == 'false')
    {
        $sql = "SELECT `ID_TWEET` FROM `TWEET`";
        $sth = $db->query($sql);
    }

    $arrayObj[] = (object) array();

    while($result = $sth->fetch()) {
        $arrayObj[$i] = get($result[0]);
        $i++;
    }

    return $arrayObj;

  } catch (\PDOException $e) {
      print $e->getMessage();
      return NULL;
    }
}

/**
 * Get a user's posts
 * @param id the user's id
 * @param date_sorted the type of sorting on date (false if no sorting asked), "DESC" or "ASC" otherwise
 * @return the list of posts objects
 */
function list_user_posts($id, $date_sorted="DESC") {

  try {
    $i = 0;
    $db = \Db::dbc();
    $sql = "SELECT `ID_TWEET` FROM `TWEET` WHERE `ID_USER` = :id ORDER BY DATE_PUBLI :date_sorted";
    $sth = $db->prepare($sql);
    $sth->execute(array(':id' => $id, ':date_sorted' => $date_sorted));

  } catch (\PDOException $e) {
  print $e->getMessage();
  return NULL;
  }

  $arrayObj[] = (object) array();

    while($result = $sth->fetch()) {
      $arrayObj[$i] = get($result[0]);
      $i++;
    }

    return $arrayObj;

/* Revoir car je n'utilise pas get()
    return [get(1)];
*/

}

/**
 * Get a post's likes
 * @param pid the post's id
 * @return the users objects who liked the post
 */
function get_likes($pid) {

  try {
      $i = 0;
      $db = \Db::dbc();
      $sth = $db->prepare("SELECT `ID_USER` FROM `AIMER` WHERE `ID_TWEET` = :pid");
      $sth->execute(array(':pid' => $pid));

      $arrayObj[] = (object) array();

      while($result = $sth->fetch()) {
          $arrayObj[$i] = get($result[0]);
          $i++;
      }

  } catch (\PDOException $e) {
      print $e->getMessage();
      return NULL;
  }

  return $arrayObj;
}

/**
 * Get a post's responses
 * @param pid the post's id
 * @return the posts objects which are a response to the actual post
 */
function get_responses($pid) {

  try {
      $i = 0;
      $db = \Db::dbc();
      $sth = $db->prepare("SELECT `ID_TWEET` FROM `TWEET` WHERE `ID_TWEET_REPONSE` = :pid");
      $sth->execute(array(':pid' => $pid));

      $arrayObj[] = (object) array();

      while($result = $sth->fetch()) {
          $arrayObj[$i] = get($result[0]);
          $i++;
      }

  } catch (\PDOException $e) {
      print $e->getMessage();
      return NULL;
  }

  return $arrayObj;
}

/**
 * Get stats from a post (number of responses and number of likes
 */
function get_stats($pid) {

    try {
      $db = \Db::dbc();

      $nb_likes = get_likes($pid);
      $nb_Resp = get_responses($pid);

      $obj = (object) array();

      $obj->nb_likes = $nb_likes->count();
      $obj->nb_Resp = $nb_Resp->count();

    } catch (\PDOException $e) {
      print $e->getMessage();
      return NULL;
    }

    return $obj;
}

/**
 * Like a post
 * @param uid the user's id to like the post
 * @param pid the post's id to be liked
 * @return true if the post has been liked, false else
 */
function like($uid, $pid) {

  try {
    $db = \Db::dbc();

    $sql = "SELECT * FROM `AIMER` WHERE `ID_TWEET` = :pid AND `ID_USER` = :uid";
    $sth = $db->prepare($sql);
    $sth->execute(array(':pid' => $pid, ':uid' => $uid));

    if(mysql_num_rows($sth) >= 1)
    {
        return true;
    }

    $sql = "INSERT INTO `AIMER` (`ID_TWEET`, `ID_USER`, `NOTIF`) VALUES (:pid, :uid, '1');";
    $sth = $db->prepare($sql);
    $sth->execute(array(':pid' => $pid, ':uid' => $uid));

  } catch (\PDOException $e) {
  print $e->getMessage();
  return false;
  }

    return true;
}

/**
 * Unlike a post
 * @param uid the user's id to unlike the post
 * @param pid the post's id to be unliked
 * @return true if the post has been unliked, false else
 */
function unlike($uid, $pid) {

  try {
    $db = \Db::dbc();

    $sql = "SELECT * FROM `AIMER` WHERE `ID_TWEET` = :pid AND `ID_USER` = :uid";
    $sth = $db->prepare($sql);
    $sth->execute(array(':pid' => $pid, ':uid' => $uid));

    if(mysql_num_rows($sth) == 0)
    {
        return true;
    }

    $sql = "DELETE FROM `AIMER` WHERE `AIMER`.`ID_TWEET` = :pid AND `AIMER`.`ID_USER` = :uid";
    $sth = $db->prepare($sql);
    $sth->execute(array(':pid' => $pid, ':uid' => $uid));

  } catch (\PDOException $e) {
  print $e->getMessage();
  return false;
  }

    return true;
}
