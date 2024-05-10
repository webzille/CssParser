<pre><?php

use Webzille\CssParser\CssFormat;
use Webzille\CssParser\Parser;
use Webzille\CssParser\Render;

require "vendor/autoload.php";

$minified = false;
$parser = new Parser("stylesheet.css");
$nodes = $parser->parse()->getNodes();
$format = (new CssFormat)->setIndent("    ")->setNewLine("\n");
$render = new Render($nodes, $format);
$css = trim($render->css());
//print_r($nodes);
echo "<textarea style='width: 100%; height: 100%;'>$css</textarea>";