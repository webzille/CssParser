<?php

namespace Webzille\CssParser\Util;

use Webzille\CssParser\Nodes\CSSNode;
use Webzille\CssParser\Nodes\Property;
use Webzille\CssParser\Nodes\PropertyValue;
use Webzille\CssParser\Nodes\Selector;

class Search
{

    private $results = [];

    private static ?Search $instance = null;

    public static function search(CSSNode $node): Search
    {
        self::$instance = self::$instance instanceof Search ? self::$instance : new Search();

        return self::$instance;
    }

    public function resetResults(): self
    {
        $this->results = [];

        return $this;
    }

    public function results(): array
    {
        return $this->results;
    }

    public function find(CSSNode $node, array $criteria): self
    {
        foreach ($criteria as $criterion) {
            switch ($criterion['type']) {
                case 'type':
                    $this->searchByType($node, $criterion['value']);
                    break;
                case 'property':
                    $this->searchByProperty($node, $criterion['property'], $criterion['value'] ?? null);
                    break;
                case 'selector':
                    $this->searchBySelector($node, $criterion['value']);
                    break;
                default:
                    throw new \InvalidArgumentException("Unknown search criterion type: " . $criterion['type']);
            }
        }

        return $this;
    }

    public function searchByType(CSSNode $node, string $type): self
    {
        if ($node instanceof $type) {
            $this->results[] = $node;
        }

        if (!empty($node->getChildren())) {
            foreach ($node->getChildren() as $child) {
                $this->searchByType($child, $type);
            }
        }

        return $this;
    }

    public function searchBySelector(CSSNode $node, string $selector): self
    {
        if ($node instanceof Selector && str_contains($node->selector, $selector)) {
            $this->results[] = $node;
        }

        if ($node->hasChildren()) {
            foreach ($node->getChildren() as $child) {
                $this->searchBySelector($child, $selector);
            }
        }

        return $this;
    }

    public function searchByProperty(CSSNode $node, string $property, string $value = null): self
    {
        if ($node instanceof Property && $node->property === $property) {
            if ($value === null || $this->propertyHasValue($node, $value)) {
                $this->results[] = $node;
            }
        }

        if ($node->hasChildren()) {
            foreach ($node->getChildren() as $child) {
                $this->searchByProperty($child, $property, $value);
            }
        }

        return $this;
    }

    private function propertyHasValue(CSSNode $node, string $value): bool
    {
        foreach ($node->getChildren() as $child) {
            if ($child instanceof PropertyValue && str_contains($child->value, $value)) {
                return true;
            }
        }

        return false;
    }
}