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

    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    public function setParent(?CSSNode $parent): void
    {
        $this->parent = $parent;
    }

    public function removeChild(CSSNode $child): void
    {
        foreach ($this->children as $key => $existingChild) {
            if ($existingChild === $child) {
                unset($this->children[$key]);

                $this->children = array_values($this->children);
                break;
            }
        }
    }

    public function clearChildren()
    {
        $this->children = [];
    }
}