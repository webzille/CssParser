<?php

namespace Webzille\CssParser\Nodes;

class Comment extends CSSNode
{

    public readonly string $comment;

    public function __construct(string $comment, int $lineNo)
    {
        parent::__construct('comment', $lineNo);

        $this->comment = $comment;
    }
}