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

        $sth = $db->prepare("SELECT `CONTENU`, `DATEPUBLI` FROM `TWEET` WHERE `id` = :id");
        $sth->execute(array(':id' => $id));
        $array = $sth->fetch(PDO::FETCH_NUM);

        $obj = (object) array();
        $obj->id = $id;
        $obj->text = $array[0];
        $obj->text = new \DateTime($array[1]);
        $obj->author = \Model\User\get($id);

        return $obj;

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
        $sth = $db->prepare("SELECT `IDUSER` FROM `AIMER` WHERE `IDTWEET` = :id");
        $sth->execute(array(':id' => $id));

        /* Récupération des objects des personnes qui ont like le post */
        $likes[] = (object) array();
        while($result = $sth->fetch(PDO::FETCH_NUM)) {
            $likes[$i] = \Model\User\get($result[0]);
            $i++;
        }

        $obj->likes = $likes;
        $obj->hashtags = extract_hashtags($obj->text);

        /* Récupération de l'object du tweet si c'est une réponse */
        $sth = $db->prepare("SELECT `IDTWEET_REPONSE` FROM `TWEET` WHERE `IDTWEET` = :id");
        $sth->execute(array(':id' => $id));

        $respond = $sth->fetch(PDO::FETCH_NUM);

        if ($respond == false)
            $obj->responds_to = NULL;
        else
            $obj->responds_to = get($respond);

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
        $date->format('Y-m-dTH:i:sP');

        $db = \Db::dbc();

        $sql = "INSERT INTO `TWEET` (`IDTWEET`, `IDUSER`, `IDTWEET_REPONSE`, `CONTENU`, `DATEPUBLI`) VALUES (NULL, '$author_id', '$response_to', '$text', '$date')";
        $db->query($sql);

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

    function filter($c) {
        return $c !== "" || $c[0] == "#";
    }

    $i = 0;
    $newArray = array_filter($text, "filter" );
    $hashtags[] = (object) array();

    while($array = $newArray->fetch(PDO::FETCH_NUM)) {
        $hashtags[$i]->id = $array[0];
        $hashtags[$i]->name = $array[1];
        $i++;
    }

    return $hashtags;
}

/**
 * Get the list of mentioned users in message
 * @param text the message
 * @return an array of usernames
 */
function extract_mentions($text) {

    function filter($c) {
        return $c !== "" || $c[0] == "@";
    }

    $i = 0;
    $newArray = array_filter($text, "filter" );
    $mentioned[] = (object) array();

    while($array = $newArray->fetch(PDO::FETCH_NUM)) {
        $mentioned[$i]->id = $array[0];
        $mentioned[$i]->name = $array[1];
        $i++;
    }

    return $mentioned;
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

        while($result = $sth->fetch(PDO::FETCH_NUM)) {
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
        $sql = "DELETE FROM `MENTIONNER` WHERE `IDTWEET` = :id";
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
        $result = $sth->fetch(PDO::FETCH_NUM);

    } catch (\PDOException $e) {
        print $e->getMessage();
        return NULL;
      }
        return [get($result)];
}

/**
 * List posts
 * @param date_sorted the type of sorting on date (false if no sorting asked), "DESC" or "ASC" otherwise
 * @return an array of the objects of each post
 * @warning this function does not return the passwords
 */
function list_all($date_sorted=false) {
    return [get(1),get(1),get(1),get(1),get(1),get(1)];
}

/**
 * Get a user's posts
 * @param id the user's id
 * @param date_sorted the type of sorting on date (false if no sorting asked), "DESC" or "ASC" otherwise
 * @return the list of posts objects
 */
function list_user_posts($id, $date_sorted="DESC") {
    return [get(1)];
}

/**
 * Get a post's likes
 * @param pid the post's id
 * @return the users objects who liked the post
 */
function get_likes($pid) {
    return [\Model\User\get(2)];
}

/**
 * Get a post's responses
 * @param pid the post's id
 * @return the posts objects which are a response to the actual post
 */
function get_responses($pid) {
    return [get(2)];
}

/**
 * Get stats from a post (number of responses and number of likes
 */
function get_stats($pid) {
    return (object) array(
        "nb_likes" => 10,
        "nb_responses" => 40
    );
}

/**
 * Like a post
 * @param uid the user's id to like the post
 * @param pid the post's id to be liked
 * @return true if the post has been liked, false else
 */
function like($uid, $pid) {
    return false;
}

/**
 * Unlike a post
 * @param uid the user's id to unlike the post
 * @param pid the post's id to be unliked
 * @return true if the post has been unliked, false else
 */
function unlike($uid, $pid) {
    return false;
}
