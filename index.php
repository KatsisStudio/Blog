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

        $folder = basename($path);

        $text = file_get_contents($path . "/article.md");
        $expr = "/!\[([^\]]+)\]\(([^\)]+)\)/";
        $repl = "![$1](articles/$folder/$2)";
        $text = preg_replace($expr, $repl, $text);

        return $Parsedown->text($text);
    }, $files),
]);
