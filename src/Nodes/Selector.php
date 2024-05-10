<?php

namespace Webzille\CssParser\Nodes;

class Selector extends CSSNode
{

    public readonly string $selector;

    public function __construct(string $selector, int $lineNo)
    {
        parent::__construct('selector', $lineNo);

        $this->selector = $selector;
    }
}