<?php

require_once "vendor/autoload.php";

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$loader = new FilesystemLoader(["templates"]);
$twig = new Environment($loader);

$files = glob("articles/*", GLOB_ONLYDIR);

function parseMetadata($path) {
    return [
        "id" => basename($path),
        "metadata" => json_decode(file_get_contents("$path/info.json"), true)
    ];
}

function parseArticle($path) {
    $Parsedown = new Parsedown();
    $Parsedown->setBreaksEnabled(true);

    $folder = basename($path);

    $text = file_get_contents($path . "/article.md");
    $expr = "/!\[([^\]]+)\]\(([^\)]+)\)/";
    $repl = "![$1](articles/$folder/$2)";
    $text = preg_replace($expr, $repl, $text);

    return $Parsedown->text($text);
}

if (isset($_GET["article"]) && in_array("articles/" . $_GET["article"], $files)) {
    echo $twig->render("article.html.twig", [
        "article" => parseArticle("articles/" . $_GET["article"]),
    ]);
} else {
    echo $twig->render("index.html.twig", [
        "articles" => array_map("parseMetadata", $files),
    ]);
}
