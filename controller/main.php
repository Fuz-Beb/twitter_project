<?php
namespace Controller\Main;
function main() {
    $user = \Session\get_user();
    if($user) {
        $posts = array();
        $f = array_map(function($u){
            return $u->id;
        }, \Model\User\get_followings($user->id));
        $p = \Model\Post\list_all("DESC");
        foreach($p as $post) {
            if(in_array($p->author->id, $f)) {
                $posts[] = $p;
            }
        }
    }
    else {
        $posts = \Model\Post\list_all("DESC");
    }
    $popular_h = \Model\Hashtag\list_popular_hashtags(10);
    require "../view/timeline.php";
}
