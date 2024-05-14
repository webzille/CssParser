<?php

namespace Webzille\CssParser\Nodes;

class PropertyValue extends CSSNode
{

    public string $value;
    public readonly bool $important;

    public function __construct(string $value, int $lineNo, bool $important)
    {
        parent::__construct('property-value', $lineNo);
        
        $this->value = $value;
        $this->important = $important;
    }
}