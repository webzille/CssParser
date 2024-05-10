<?php

namespace Webzille\CssParser\Nodes;

class PropertyValue extends CSSNode
{

    public string $value;

    public function __construct(string $value, int $lineNo)
    {
        parent::__construct('property-value', $lineNo);
        
        $this->value = $value;
    }
}