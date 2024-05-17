<pre><?php

use Webzille\CssParser\CSS;

require "vendor/autoload.php";

$cssFile = "stylesheet.css";
$nodes = CSS::parser($cssFile)->parse()->getNodes();

$optimizer = CSS::optimize($nodes);

// 1. Remove Whitespace and comments
$optimizer->removeWhitespace();
echo "After removing whitespace / Comments:\n";
print_r($optimizer->getModified());

// 2. Remove Duplicates
$optimizer->clearModified()->removeDuplicates();
echo "\n\nAfter removing duplicates:\n";
print_r($optimizer->getModified());

// 3. Convert Longhand to Shorthand
$optimizer->clearModified()->toShorthand();
echo "\n\nAfter converting to shorthand:\n";
print_r($optimizer->getModified());

// 4. Optimize Colors
$optimizer->clearModified()->optimizeColors();
echo "\n\nAfter optimizing colors:\n";
print_r($optimizer->getModified());

// 5. Add Vendor Prefixes
$optimizer->clearModified()->vendorPrefix();
echo "\n\nAfter adding vendor prefixes:\n";
print_r($optimizer->getModified());
