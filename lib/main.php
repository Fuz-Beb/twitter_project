<?php

function require_wildcard($w) {
    foreach (glob($w) as $filename)
    {
        require_once $filename;
    }
}

require "session.php";
require "../vendor/autoload.php";
require "db.php";

Db::connect();

require_wildcard("../model/*.php");
require_wildcard("../controller/*.php");
require_wildcard("../view/partials/*.php");
