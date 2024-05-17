<pre><?php

use Webzille\CssParser\CssFormat;
use Webzille\CssParser\Parser;
use Webzille\CssParser\Render;
use Webzille\CssParser\Util\Optimize;

require "vendor/autoload.php";

$minified = false;
$parser = new Parser("stylesheet.css");
$nodes = $parser->parse()->getNodes();
$format = (new CssFormat)->setIndent("    ")->setNewLine("\n");

$optimized = Optimize::optimize($nodes)
                        //->removeWhitespace()
                        ->removeDuplicates()
                        ->toShorthand()
                        ->optimizeColors()
                        ->vendorPrefix();

$render = new Render($optimized->getNodes(), $format);

$css = trim($render->css());
//print_r($nodes);
print_r($optimized->getModified());
echo "<textarea style='width: 100%; height: 100%;'>$css</textarea>";
