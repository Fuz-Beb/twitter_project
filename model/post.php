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
        $i = 0;
        $db = \Db::dbc();

        // Test si l'id fourni existe dans la base
        $sql = "SELECT * FROM `TWEET` WHERE `ID_TWEET` = :id";
        $sth = $db->prepare($sql);
        $sth->execute(array(':id' => $id));

        if($sth->rowCount() < 1)
            return $arrayObj = (object) array();

        /* Récupération des 4 premiers attribut */
        $obj = get($id);
        $sth = $db->prepare("SELECT `ID_USER` FROM `AIMER` WHERE `ID_TWEET` = :id");
        $sth->execute(array(':id' => $id));

        if ($sth->rowCount() < 1)
            $likes = [];
        else
            $likes[] = (object) array();

        /* Récupération des objects des personnes qui ont like le post */
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
        $newDate = $date->format('Y-m-d H:i:s');
        $db = \Db::dbc();

        if ($response_to == NULL)
            $sql = "INSERT INTO `TWEET` (`ID_TWEET`, `ID_USER`, `ID_TWEET_REPONSE`, `CONTENT`, `DATE_PUBLI`) VALUES (NULL, '$author_id', NULL, '$text', '$newDate')";
        else
            $sql = "INSERT INTO `TWEET` (`ID_TWEET`, `ID_USER`, `ID_TWEET_REPONSE`, `CONTENT`, `DATE_PUBLI`) VALUES (NULL, '$author_id', '$response_to', '$text', '$newDate')";

        $db->query($sql);

        // Récupération de l'id du dernier tweet créé
        $sql = "SELECT `ID_TWEET` FROM `TWEET` ORDER BY `ID_TWEET` DESC LIMIT 1";
        $sth = $db->query($sql);
        $result = $sth->fetch();

        //Recherche de mention utilisateur dans un texte
        $arrayMention = extract_mentions($text);
        foreach($arrayMention as $value1) {
            $sql = "SELECT `ID_USER` FROM `UTILISATEUR` WHERE `USERNAME` LIKE :username";
            $sth = $db->prepare($sql);
            $sth->execute(array(':username' => $value1));
            $value1 = $sth->fetch();
            mention_user($result[0], $value1[0]);
        }

        // Recherche des hashtags dans un texte
        $arrayHashtags = extract_hashtags($text);

        foreach($arrayHashtags as $value2)
            \Model\Hashtag\attach($result[0], $value2);

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
        $sql = "INSERT INTO `MENTIONNER` (`ID_TWEET`, `ID_USER`, `NOTIF`) VALUES ('$pid', '$uid', '1')";
        $db->query($sql);
        return true;

    } catch (\PDOException $e) {
        print $e->getMessage();
        return false;
    }
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
        $sth = $db->prepare("SELECT `ID_USER` FROM `MENTIONNER` WHERE `ID_TWEET` = :pid");
        $sth->execute(array(':pid' => $pid));

        if($sth->rowCount() < 1)
            return $arrayObj = [];

        while($result = $sth->fetch()) {
            $arrayObj[$i] = (object) array();
            $arrayObj[$i] = \Model\User\get($result[0]);
            $i++;
        }

        return $arrayObj;

    } catch (\PDOException $e) {
        print $e->getMessage();
        return NULL;
    }
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
        return true;

    } catch (\PDOException $e) {
        print $e->getMessage();
        return false;
    }
}
/**
 * Search for posts
 * @param string the string to search in the text
 * @return an array of find objects
 */
function search($string) {

    try {
        $db = \Db::dbc();
        $i = 0;
        $sql = "SELECT `ID_TWEET` FROM `TWEET` WHERE (CONVERT(`CONTENT` USING utf8) LIKE '%$string%')";
        $sth = $db->query($sql);

        // Si l'argument est vide alors ne rien faire
        if($string == '')
            return $arrayObj = [];

        if($sth->rowCount() < 1)
            return NULL;

        if ($result = $sth->fetch()) {
            $arrayObj[0] = get($result[0]);
            $i++;
            while($result = $sth->fetch()) {
                    $arrayObj[$i] = get($result[0]);
                    $i++;
            }
        }

        return $arrayObj;

    } catch (\PDOException $e) {
        print $e->getMessage();
        return NULL;
    }
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

        if($date_sorted == "ASC")
            $sql = "SELECT `ID_TWEET` FROM `TWEET` ORDER BY `DATE_PUBLI` ASC";
        elseif ($date_sorted == "DESC")
            $sql = "SELECT `ID_TWEET` FROM `TWEET` ORDER BY `DATE_PUBLI` DESC";
        elseif($date_sorted == false || $date_sorted == FALSE)
            $sql = "SELECT `ID_TWEET` FROM `TWEET`";

        $sth = $db->query($sql);

        if($sth->rowCount() < 1)
            return $arrayObj = [];

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

        if($date_sorted == "ASC")
            $sql = "SELECT `ID_TWEET` FROM `TWEET` WHERE `ID_USER` = :id ORDER BY DATE_PUBLI ASC";
        elseif ($date_sorted == "DESC")
            $sql = "SELECT `ID_TWEET` FROM `TWEET` WHERE `ID_USER` = :id ORDER BY DATE_PUBLI DESC";
        elseif($date_sorted == false || $date_sorted == FALSE)
            $sql = "SELECT `ID_TWEET` FROM `TWEET` WHERE `ID_USER` = :id";

        $sth = $db->prepare($sql);
        $sth->execute(array(':id' => $id));

        if($sth->rowCount() < 1)
            return $arrayObj = [];

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

        if($sth->rowCount() < 1)
            return $arrayObj = [];

        while($result = $sth->fetch()) {
            $arrayObj = \Model\User\get($result[0]);
            $i++;
        }

        return $arrayObj;

    } catch (\PDOException $e) {
        print $e->getMessage();
        return NULL;
    }

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

    if($sth->rowCount() < 1)
        return $arrayObj = [];

    while($result = $sth->fetch()) {
        $arrayObj[$i] = (object) array();
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
 * Get stats from a post (number of responses and number of likes
 */
function get_stats($pid) {

    try {
        $db = \Db::dbc();
        $sth = $db->prepare("SELECT COUNT(*) FROM `AIMER` WHERE `ID_TWEET` = :pid");
        $sth->execute(array(':pid' => $pid));
        $response = $sth->fetch();
        $nb_likes = $response[0];

        $sth = $db->prepare("SELECT COUNT(*) FROM `TWEET` WHERE`ID_TWEET_REPONSE` = :pid");
        $sth->execute(array(':pid' => $pid));
        $response = $sth->fetch();
        $nb_resp = $response[0];

        $obj = (object) array();
        $obj->nb_likes = $nb_likes;
        $obj->nb_Resp = $nb_resp;

        return $obj;

    } catch (\PDOException $e) {
        print $e->getMessage();
        return NULL;
    }
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

    if($sth->rowCount() > 0)
        return true;

    $sql = "INSERT INTO `AIMER` (`ID_TWEET`, `ID_USER`, `NOTIF`) VALUES (:pid, :uid, '1');";
    $sth = $db->prepare($sql);
    $sth->execute(array(':pid' => $pid, ':uid' => $uid));
    return true;

    } catch (\PDOException $e) {
        print $e->getMessage();
        return false;
    }
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

        if($sth->rowCount() == 0)
            return true;

        $sql = "DELETE FROM `AIMER` WHERE `ID_TWEET` = :pid AND `ID_USER` = :uid";
        $sth = $db->prepare($sql);
        $sth->execute(array(':pid' => $pid, ':uid' => $uid));
        return true;

    } catch (\PDOException $e) {
        print $e->getMessage();
        return false;
    }
}
