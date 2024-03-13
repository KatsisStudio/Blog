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

$loader = new FilesystemLoader(["templates"]);
$twig = new Environment($loader);

$files = glob("articles/*", GLOB_ONLYDIR);
array_pop($files);

function parseMetadata($path) {
    $id = basename($path);
    $metadata = json_decode(file_get_contents("$path/info.json"), true);
    $metadata["preview"] = "articles/" . $id . "/" . $metadata["preview"];

    return [
        "id" => basename($path),
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

if (isset($_GET["article"]) && in_array("articles/" . $_GET["article"], $files)) {
    echo $twig->render("article.html.twig", [
        "article" => parseArticle("articles/" . $_GET["article"]),
        "metadata" => parseMetadata("articles/" . $_GET["article"])
    ]);
} else {
    echo $twig->render("index.html.twig", [
        "articles" => array_map("parseMetadata", $files),
    ]);
}
