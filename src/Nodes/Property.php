<?php

namespace Webzille\CssParser\Nodes;

class Property extends CSSNode {
    public $property;

    public function __construct($property) {
        parent::__construct('property');
        $this->property = $property;
    }

    public function countChildren()
    {
        return count($this->children);
    }
}