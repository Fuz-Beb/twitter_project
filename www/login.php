<?php
require '../lib/main.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    \Controller\User\login($_POST["username"], $_POST["password"]);    
}
else {
    \Controller\User\login_page();
}
require dirname(__DIR__).'/lib/closure.php';
