<?php

require_once "vendor/autoload.php";

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$loader = new FilesystemLoader(["templates"]);
$twig = new Environment($loader);

$files = array_slice(scandir("articles"), 2);

echo $twig->render("index.html.twig", [
    "articles" => array_map(function($path) {
        $Parsedown = new Parsedown();
        return $Parsedown->text(file_get_contents("articles/" . $path));
    }, $files),
]);
