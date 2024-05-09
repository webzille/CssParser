<?php

namespace Webzille\CssParser\Nodes;

class Comment extends CSSNode {
    public $comment;

    public function __construct($comment) {
        parent::__construct('comment');
        $this->comment = $comment;
    }
}