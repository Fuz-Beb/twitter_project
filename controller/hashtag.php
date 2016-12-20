<?php
namespace Controller\Hashtag;

function hashtag_page($name) {
    $posts = \Model\Hashtag\get_posts($name);
    if(!$posts) {
        header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
        return;
    }
    $related_h = \Model\Hashtag\get_related_hashtags($name, 10);
    require "../view/hashtag.php";
}
