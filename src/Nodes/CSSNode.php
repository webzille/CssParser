<?php

namespace Webzille\CssParser\Nodes;

class CSSNode
{
    
    public readonly string $type;
    protected ?CSSNode $parent = null;
    protected array $children = [];
    public readonly int $lineNo;

    public function __construct(string $type, int $lineNo)
    {
        $this->type = $type;
        $this->lineNo = $lineNo;
    }

    public function addChild(CSSNode $child): void
    {
        $child->parent = $this;
        $this->children[] = $child;
    }

    public function getParent(): CSSNode
    {
        return $this->parent;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function countChildren(): int
    {
        return count($this->children);
    }

    public function hasChildren(): bool
    {
        return $this->countChildren() > 0;
    }
}