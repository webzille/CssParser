<?php

namespace Webzille\CssParser\Nodes;

class AtRule extends CSSNode
{

    public readonly string $rule;
    public readonly string $params;
    public readonly int $isComplex;

    public function __construct(string $rule, int $lineNo, string $params = '', bool $isComplex = false)
    {
        parent::__construct('at-rule', $lineNo);
        
        $this->rule = $rule;
        $this->params = $params;
        $this->isComplex = $isComplex;
    }
}