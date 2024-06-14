<?php

require_once "vendor/autoload.php";

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class ParsedownExtension extends Parsedown
{
    // From https://github.com/adjmpwgt/parsedown-extra-plus/blob/main/ParsedownExtraPlus.php
    public $blockCodeClassFormat = 'language-%s';

    public $blockPreClassHighlight = 'prettyprint';

    protected function blockFencedCode($Line)
    {
        $Block = parent::blockFencedCode($Line);

        if (isset($Block['element']['name']) && $Block['element']['name'] == 'pre' && isset($Block['element']['text']['name']) && $Block['element']['text']['name'] == 'code') {
            if (isset($Block['element']['text']['attributes']['class'])) {
                if (strpos($Block['element']['text']['attributes']['class'], 'mermaid') === false) {
                    $Block['element']['attributes']['class'] = $this->blockPreClassHighlight;
                } else {
                    $Block['element']['text']['attributes']['class'] = str_replace(sprintf($this->blockCodeClassFormat, 'mermaid'), 'mermaid', $Block['element']['text']['attributes']['class']);
                }
            }
        }
        return $Block;
    }
}

function cleanName($path) {
    return trimZeros(basename($path));
}

function trimZeros($name) {
    return ltrim($name, '0');
}

function parseMetadata($path) {
    $id = basename($path);
    $metadata = json_decode(file_get_contents("$path/info.json"), true);
    $metadata["preview"] = "articles/" . $id . "/" . $metadata["preview"];

    return [
        "id" => trimZeros($id),
        "metadata" => $metadata
    ];
}

function parseArticle($path) {
    $Parsedown = new ParsedownExtension();
    $Parsedown->setBreaksEnabled(true);

    $folder = basename($path);

    $text = file_get_contents($path . "/article.md");
    $expr = "/!\[([^\]]+)\]\(([^\)]+)\)/";
    $repl = "![$1](articles/$folder/$2)";
    $text = preg_replace($expr, $repl, $text);

    return $Parsedown->text($text);
}

$loader = new FilesystemLoader(["templates"]);
$twig = new Environment($loader);

$urlData = array_filter(explode("/", substr(explode("?", $_SERVER["REQUEST_URI"])[0], 1)));
$article = count($urlData) > 0 ? $urlData[0] : null;

$files = glob("articles/*", GLOB_ONLYDIR);
array_pop($files);

foreach ($files as $f) {
    if ($article == cleanName($f)) {
        $result = $value;
        echo $twig->render("article.html.twig", [
            "article" => parseArticle($f),
            "metadata" => parseMetadata($f)
        ]);
        return;
    }
}

echo $twig->render("index.html.twig", [
    "articles" => array_map("parseMetadata", $files),
]);