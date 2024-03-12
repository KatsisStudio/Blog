<?php

require_once "vendor/autoload.php";

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$loader = new FilesystemLoader(["templates"]);
$twig = new Environment($loader);

$files = glob("articles/*", GLOB_ONLYDIR);

echo $twig->render("index.html.twig", [
    "articles" => array_map(function($path) {
        $Parsedown = new Parsedown();
        return $Parsedown->text(file_get_contents($path . "/article.md"));
    }, $files),
]);
