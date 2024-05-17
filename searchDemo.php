<pre><?php

use Webzille\CssParser\Nodes\AtRule;
use Webzille\CssParser\Parser;
use Webzille\CssParser\Util\Search;

require "vendor/autoload.php";

$cssFile = "stylesheet.css";
$parser = new Parser($cssFile);
$nodes = $parser->parse()->getNodes();

$search = new Search($nodes);

// Test cases
$search->clearResults()->find([
    ['type' => 'property', 'property' => 'width', 'value' => '80%']
]);
echo "Results for property 'width' with value '80%':\n\n";
foreach ($search->results() as $results) {
    echo "<strong>$results->property: {$results->getChildren()[0]->value};</strong> on Line: $results->lineNo" . PHP_EOL;
}

$search->clearResults()->find([
    ['type' => 'selector', 'value' => 'h1']
]);
echo "\n\n\nResults for selector 'h1':\n\n";
foreach ($search->results() as $results) {
    echo "<strong>$results->selector</strong> on Line: $results->lineNo" . PHP_EOL;
}

$search->clearResults()->find([
    ['type' => 'id', 'value' => 'my-id']
]);
echo "\n\n\nResults for ID 'my-id':\n\n";
foreach ($search->results() as $results) {
    echo "<strong>$results->selector</strong> on Line: $results->lineNo" . PHP_EOL;
}

$search->clearResults()->find([
    ['type' => 'attribute', 'value' => 'href']
]);
echo "\n\n\nResults for attribute 'href':\n\n";
foreach ($search->results() as $results) {
    echo "<strong>$results->selector</strong> on Line: $results->lineNo" . PHP_EOL;
}

$search->clearResults()->find([
    ['type' => 'media', 'value' => 'screen and (min-width: 768px)']
]);
echo "\n\n\nResults for media query 'screen and (min-width: 768px)':\n\n";
foreach ($search->results() as $results) {
    echo "<strong>" . trim("$results->rule $results->params") . "</strong> on Line: $results->lineNo" . PHP_EOL;
}

$search->clearResults()->find([
    ['type' => 'media', 'value' => '768px']
]);
echo "\n\n\nResults for media query '768px':\n\n";
foreach ($search->results() as $results) {
    echo "<strong>" . trim("$results->rule $results->params") . "</strong> on Line: $results->lineNo" . PHP_EOL;
}

$search->clearResults()->find([
    ['type' => 'pseudo', 'value' => 'hover']
]);
echo "\n\n\nResults for pseudo-class 'hover':\n\n";
foreach ($search->results() as $results) {
    echo "<strong>$results->selector</strong> on Line: $results->lineNo" . PHP_EOL;
}

$search->clearResults()->find([
    ['type' => 'type', 'value' => AtRule::class]
]);
echo "\n\n\nResults for type 'AtRule':\n\n";
foreach ($search->results() as $results) {
    echo "<strong>" . trim("$results->rule $results->params") . "</strong> on Line: $results->lineNo" . PHP_EOL;
}

$search->clearResults()->find([
    ['type' => 'class', 'value' => 'container']
]);
echo "\n\n\nResults for class 'container':\n\n";
foreach ($search->results() as $results) {
    echo "<strong>$results->selector</strong> on Line: $results->lineNo" . PHP_EOL;
}

// Search within results
$search->searchResults([
    ['type' => 'property', 'property' => 'width']
]);

echo "\n\n\nResults within previous results for property 'width':\n\n";
foreach ($search->results() as $results) {
    echo "<strong>$results->property: {$results->getChildren()[0]->value};</strong> on Line: $results->lineNo" . PHP_EOL;
}
