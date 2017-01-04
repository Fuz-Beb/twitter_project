<?php
namespace Model\User;
use \Db;
use \PDO;
use \PDOException;
/**
 * User model
 *
 * This file contains every db action regarding the users
 */

/**
 * Get a user in db
 * @param id the id of the user in db
 * @return an object containing the attributes of the user or null if error or the user doesn't exist
 */
function get($id) {
    try {
        $db = \Db::dbc();

        $sth = $db->prepare("SELECT `ID_USER`, `USERNAME`, `NAME`, `PASSWORD`, `EMAIL`, `AVATAR`, `SIGN_UP` FROM `UTILISATEUR` WHERE `ID_USER` = :id");
        $sth->execute(array(':id' => $id));
        if ($array = $sth->fetch(PDO::FETCH_NUM))
        {
            $obj = (object) array();
            $obj->id = $array[0];
            $obj->username = $array[1];
            $obj->name = $array[2];
            $obj->password = $array[3];
            $obj->email = $array[4];
            $obj->avatar = $array[5];
            $obj->sign_up = $array[6];

            return $obj;
        }
        else
        {
            return NULL;
        }

    } catch (\PDOException $e) {
        print $e->getMessage();
        return NULL;
    }
}

/**
 * Create a user in db
 * @param username the user's username
 * @param name the user's name
 * @param password the user's password
 * @param email the user's email
 * @param avatar_path the temporary path to the user's avatar
 * @return the id which was assigned to the created user, null if an error occured
 * @warning this function doesn't check whether a user with a similar username exists
 * @warning this function hashes the password
 */
function create($username, $name, $password, $email, $avatar_path) {

    try {
        $db = \Db::dbc();

        /* Hashage du mot de passe */
        $hash_pass = hash_password($password);

        $sql = "INSERT INTO `UTILISATEUR` (`ID_USER`, `USERNAME`, `NAME`, `PASSWORD`, `EMAIL`, `AVATAR`, `SIGN_UP`) VALUES (NULL, '$username', '$name', '$hash_pass', '$email', '$avatar_path', NOW())";
        $db->query($sql);


        $sql = "SELECT `ID_USER` FROM `UTILISATEUR` WHERE `USERNAME` = :username";
        $sth = $db->prepare($sql);
        $sth->execute(array(':username' => $username));
        $result = $sth->fetch(PDO::FETCH_NUM);

    } catch (\PDOException $e) {
      print $e->getMessage();
      return NULL;
    }
    return $result[0];
}

/**
 * Modify a user in db
 * @param uid the user's id to modify
 * @param username the user's username
 * @param name the user's name
 * @param email the user's email
 * @return true if everything went fine, false else
 * @warning this function doesn't check whether a user with a similar username exists
 */
function modify($uid, $username, $name, $email) {

  try {
    $db = \Db::dbc();

    $sql = "UPDATE `UTILISATEUR` SET `USERNAME` = '$username', `NAME` = '$name', `EMAIL` = '$email' WHERE `ID_USER` = :uid";
    $sth = $db->prepare($sql);
    $sth->execute(array(':uid' => $uid));

  } catch (\PDOException $e) {
    print $e->getMessage();
    return false;
  }
    return true;
}

/**
 * Modify a user in db
 * @param uid the user's id to modify
 * @param new_password the new password
 * @return true if everything went fine, false else
 * @warning this function hashes the password
 */
function change_password($uid, $new_password) {

  try {
    $db = \Db::dbc();

    /* Hashage du mot de passe */
    $hash_pass = hash_password($new_password);

    $sql = "UPDATE `UTILISATEUR` SET `PASSWORD` = '$hash_pass' WHERE `ID_USER` = :uid";
    $sth = $db->prepare($sql);
    $sth->execute(array(':uid' => $uid));

  } catch (\PDOException $e) {
    print $e->getMessage();
    return false;
  }
    return true;
}

/**
 * Modify a user in db
 * @param uid the user's id to modify
 * @param avatar_path the temporary path to the user's avatar
 * @return true if everything went fine, false else
 */
function change_avatar($uid, $avatar_path) {

  try {
    $db = \Db::dbc();

    $sql = "UPDATE `UTILISATEUR` SET `PASSWORD` = '$avatar_path' WHERE `ID_USER` = :uid";
    $sth = $db->prepare($sql);
    $sth->execute(array(':uid' => $uid));

  } catch (\PDOException $e) {
    print $e->getMessage();
    return false;
  }
    return true;
}

/**
 * Delete a user in db
 * @param id the id of the user to delete
 * @return true if the user has been correctly deleted, false else
 */
function destroy($id) {

    try {
        $db = \Db::dbc();

        $sql = "DELETE FROM `UTILISATEUR` WHERE `ID_USER` = :id";
        $sth = $db->prepare($sql);
        $sth->execute(array(':id' => $id));

    } catch (\PDOException $e) {
        print $e->getMessage();
        return false;
    }
        return true;
}

/**
 * Hash a user password
 * @param password the clear password to hash
 * @return the hashed password
 */
function hash_password($password) {
    return md5($password);
}

/**
 * Search a user
 * @param string the string to search in the name or username
 * @return an array of find objects
 */
function search($string) {

    try {
        $db = \Db::dbc();
        $i = 0;
        $sql = "SELECT `ID_USER` FROM `UTILISATEUR` WHERE `USERNAME` LIKE :string OR `NAME` LIKE :string";
        $sth = $db->prepare($sql);
        $sth->execute(array(':string' => $string));

        if ($result = $sth->fetch()) {

            $arrayObj[] = (object) array();
            $arrayObj[0] = get($result[0]);
            $i++; 

            while($result = $sth->fetch()) {
                    $arrayObj[$i] = get($result[0]);
                    $i++;                
            }
        }
        else
        {
            $sql = "SELECT `ID_USER`, INSTR( `USERNAME`, '$string' ), INSTR( `NAME`, '$string') FROM `UTILISATEUR`";
            $sth = $db->query($sql);

            $arrayObj[] = (object) array();

            while($result = $sth->fetch()) {
                if ($result[1] !== 0 || $result[2] !== 0)
                {
                    $arrayObj[$i] = get($result[0]);
                    $i++;                
                }
            }
        }
        return $arrayObj;

    } catch (\PDOException $e) {
        print $e->getMessage();
        return NULL;
    }
}

/**
 * List users
 * @return an array of the objects of every users
 */
function list_all() {

    try {
        $db = \Db::dbc();

        $i = 0;
        $sql = "SELECT * FROM `UTILISATEUR`";
        $sth = $db->query($sql);

        $arrayObj[] = (object) array();

        while($result = $sth->fetch(PDO::FETCH_NUM)) {
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
 * Get a user from its username
 * @param username the searched user's username
 * @return the user object or null if the user doesn't exist
 */
function get_by_username($username) {

  try {
    $db = \Db::dbc();

    $sql = "SELECT `ID_USER`  FROM `UTILISATEUR` WHERE `USERNAME` = :username";
    $sth = $db->prepare($sql);
    $sth->execute(array(':username' => $username));
    $result = $sth->fetch(PDO::FETCH_NUM);

    if ($result == NULL)
        return NULL;
    else
        return get($result[0]);

  } catch (\PDOException $e) {
    print $e->getMessage();
    return NULL;
    }
}

/**
 * Get a user's followers
 * @param uid the user's id
 * @return a list of users objects
 */
function get_followers($uid) {

    try {
    $db = \Db::dbc();

    $i = 0;
    $sql = "SELECT `ID_USER`  FROM `SUIVRE` WHERE `ID_USER_1` = :uid";
    $sth = $db->prepare($sql);
    $sth->execute(array(':uid' => $uid));

    $oneObject[] = (object) array();
    $oneObject = [];

    while($result = $sth->fetch(PDO::FETCH_NUM)) {
        $oneObject[$i] = get($result[0]);
        $i++;
    }

    return $oneObject;

  } catch (\PDOException $e) {
    print $e->getMessage();
    }
}

/**
 * Get the users our user is following
 * @param uid the user's id
 * @return a list of users objects
 */
function get_followings($uid) {

    try {
    $db = \Db::dbc();

    $i = 0;
    $sql = "SELECT `ID_USER_1`  FROM `SUIVRE` WHERE `ID_USER` = :uid";
    $sth = $db->prepare($sql);
    $sth->execute(array(':uid' => $uid));

    $oneObject[] = (object) array();
    $oneObject = [];

    while($result = $sth->fetch(PDO::FETCH_NUM)) {
        $oneObject[$i] = get($result[0]);
        $i++;
    }

    return $oneObject;

  } catch (\PDOException $e) {
    print $e->getMessage();
    }
}

/**
 * Get a user's stats
 * @param uid the user's id
 * @return an object which describes the stats
 */
function get_stats($uid) {

    try {
    $db = \Db::dbc();

    $sql = "SELECT COUNT(`ID_TWEET`) FROM `TWEET` WHERE `ID_USER` = :uid";
    $sth = $db->prepare($sql);
    $sth->execute(array(':uid' => $uid));

    $nb_posts = $sth->fetch(PDO::FETCH_NUM);


    $obj = (object) array();
    $obj->nb_posts = $nb_posts[0];

    $nb_followers = get_followers($uid);
    $nb_following = get_followings($uid);

    $obj = (object) array();
    $obj->nb_posts = $nb_posts[0];
    $obj->nb_followers = $nb_followers->count();
    $obj->nb_following = $nb_following->count();

    return $obj;

  } catch (\PDOException $e) {
    print $e->getMessage();
    return NULL;
    }
}

/**
 * Verify the user authentification
 * @param username the user's username
 * @param password the user's password
 * @return the user object or null if authentification failed
 * @warning this function must perform the password hashing
 */
function check_auth($username, $password) {

  try {
    $db = \Db::dbc();

    $sql = "SELECT `ID_USER`, `PASSWORD` FROM `UTILISATEUR` WHERE `USERNAME` = :username";
    $sth = $db->prepare($sql);
    $sth->execute(array(':username' => $username));
    $result = $sth->fetch(PDO::FETCH_NUM);

    /* Vérification du password */
    if(md5($password, $result[1]))
        return get($result[0]);
    else
        return NULL;

  } catch (\PDOException $e) {
    print $e->getMessage();
    return null;
  }

}

/**
 * Verify the user authentification based on id
 * @param id the user's id
 * @param password the user's password (already hashed)
 * @return the user object or null if authentification failed
 */
function check_auth_id($id, $password) {

  try {
    $db = \Db::dbc();

    $sql = "SELECT `PASSWORD` FROM `UTILISATEUR` WHERE `ID_USER` = :id";
    $sth = $db->prepare($sql);
    $sth->execute(array(':id' => $id));
    $result = $sth->fetch(PDO::FETCH_NUM);

    /* Vérification du password */
    if($password == $result[0])
        return get($id);
    else
        return NULL;

  } catch (\PDOException $e) {
    print $e->getMessage();
    return NULL;
  }

}

/**
 * Follow another user
 * @param id the current user's id
 * @param id_to_follow the user's id to follow
 * @return true if the user has been followed, false else
 */
function follow($id, $id_to_follow) {

    try {
    $db = \Db::dbc();

    $sql = "INSERT INTO `SUIVRE` (`ID_USER`, `ID_USER_1`, `NOTIF`) VALUES ('$id', '$id_to_follow', '1')";
    $db->query($sql);

  } catch (\PDOException $e) {
    print $e->getMessage();
    return false;
  }
  return true;
}

/**
 * Unfollow a user
 * @param id the current user's id
 * @param id_to_follow the user's id to unfollow
 * @return true if the user has been unfollowed, false else
 */
function unfollow($id, $id_to_unfollow) {
    try {
    $db = \Db::dbc();

    $sql = "DELETE FROM `SUIVRE` WHERE `ID_USER` = :id AND `ID_USER_1` = :id_to_unfollow";
    $sth = $db->prepare($sql);
    $sth->execute(array(':id' => $id, ':id_to_unfollow' => $id_to_unfollow));


  } catch (\PDOException $e) {
    print $e->getMessage();
    return false;
  }
  return true;
}
