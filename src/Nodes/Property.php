<?php

namespace Webzille\CssParser\Nodes;

class Property extends CSSNode
{

    public readonly string $property;

    public function __construct(string $property, int $lineNo)
    {
        parent::__construct('property', $lineNo);
        
        $this->property = $property;
    }
}