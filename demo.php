<pre><?php

use Webzille\CssParser\CSS;

require "vendor/autoload.php";

$minified = false;
$nodes = CSS::parser("stylesheet.css")->parse()->getNodes();
$format = CSS::format()->minify($minified);

$optimized = CSS::Optimize($nodes)
                        //->removeWhitespace()
                        ->removeDuplicates()
                        ->toShorthand()
                        ->optimizeColors()
                        ->vendorPrefix();

$css = trim(CSS::render($optimized->getNodes(), $format)->css());
//print_r($nodes);
print_r($optimized->getModified());
echo "<textarea style='width: 100%; height: 100%;'>$css</textarea>";
