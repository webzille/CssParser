<?php

namespace Webzille\CssParser;

use Webzille\CssParser\Nodes\CSSNode;
use Webzille\CssParser\Util\Optimize;
use Webzille\CssParser\Util\Search;

class CSS
{

    public static function parser(string $stylesheet): Parser
    {
        return new Parser($stylesheet);
    }

    public static function format(): Format
    {
        return new Format();
    }

    public static function optimize(CSSNode $nodes): Optimize
    {
        return new Optimize($nodes);
    }

    public static function search(CSSNode $nodes): Search
    {
        return new Search($nodes);
    }

    public static function render(CSSNode $nodes, ?Format $format = null): Render
    {
        return new Render($nodes, $format);
    }
}