<?php

namespace Webzille\CssParser\Nodes;

class CSSNode {
    public $type;
    public $parent = null;
    public $children = [];

    public function __construct($type) {
        $this->type = $type;
    }

    public function addChild(CSSNode $child) {
        $child->parent = $this;
        $this->children[] = $child;
    }
}