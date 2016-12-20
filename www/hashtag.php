<?php
require "../lib/main.php";
if(isset($_GET['name'])) {
    Controller\Hashtag\hashtag_page($_GET['name']);
}
else {
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
}
require "../lib/closure.php";
