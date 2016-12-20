<?php
namespace Controller\Search;

function search_user($query_txt) {
    $users = \Model\User\search($query_txt);
    require "../view/search_user.php";
}

function search_post($query_txt) {
    $posts = \Model\Post\search($query_txt);
    require "../view/search_post.php";
}
