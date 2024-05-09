<?php

namespace Webzille\CssParser\Nodes;

class AtRule extends CSSNode {
    public $rule;
    public $params;
    public $isComplex;

    public function __construct($rule, $params = '', $isComplex = false) {
        parent::__construct('at-rule');
        $this->rule = $rule;
        $this->params = $params;
        $this->isComplex = $isComplex;
    }
}