<?php

namespace Webzille\CssParser\Nodes;

class PropertyValue extends CSSNode {
    private $value;

    public function __construct($value) {
        parent::__construct('property-value');
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
}