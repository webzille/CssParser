<?php

namespace Webzille\CssParser\Nodes;

class Selector extends CSSNode {
    public $selector;

    public function __construct($selector) {
        parent::__construct('selector');
        $this->selector = $selector;
    }

    public function hasChildren()
    {
        return count($this->children) > 0;
    }
}