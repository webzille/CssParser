<?php

namespace Webzille\CssParser\Util;

use Webzille\CssParser\Nodes\AtRule;
use Webzille\CssParser\Nodes\CSSNode;
use Webzille\CssParser\Nodes\Property;
use Webzille\CssParser\Nodes\PropertyValue;
use Webzille\CssParser\Nodes\Selector;

class Search
{
    private $results = [];
    private CSSNode $root;
    private static ?Search $instance = null;

    public function __construct(CSSNode $node)
    {
        $this->root = $node;
    }

    public static function search(CSSNode $node): Search
    {
        if (self::$instance === null || !(self::$instance instanceof Search)) {
            self::$instance = new Search($node);
        } else {
            self::$instance->root = $node;
        }

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

    public function find(array $criteria, CSSNode $node = null): self
    {
        $node = $node === null ? $this->root : $node;

        foreach ($criteria as $criterion) {
            switch ($criterion['type']) {
                case 'type':
                    $this->searchByType($criterion['value'], $node);
                    break;
                case 'property':
                    $this->searchByProperty($criterion['property'], $criterion['value'] ?? null, $node);
                    break;
                case 'selector':
                    $this->searchBySelector($criterion['value'], $node);
                    break;
                case 'class':
                    $this->searchByClass($criterion['value'], $node);
                    break;
                case 'id':
                    $this->searchById($criterion['value'], $node);
                    break;
                case 'attribute':
                    $this->searchByAttribute($criterion['value'], $node);
                    break;
                case 'media':
                    $this->searchByMediaQuery($criterion['value'], $node);
                    break;
                case 'pseudo':
                    $this->searchByPseudo($criterion['value'], $node);
                    break;
                default:
                    throw new \InvalidArgumentException("Unknown search criterion type: " . $criterion['type']);
            }
        }

        return $this;
    }

    public function searchResults(array $criteria): self
    {
        $newResults = [];

        foreach ($this->results as $resultNode) {
            $this->searchRecursively($resultNode, $criteria, $newResults);
        }

        $this->results = $newResults;

        return $this;
    }

    private function searchRecursively(CSSNode $node, array $criteria, array &$newResults): void
    {
        foreach ($criteria as $criterion) {
            switch ($criterion['type']) {
                case 'type':
                    $match = $this->nodeMatchesType($criterion['value'], $node);
                    break;
                case 'property':
                    $match = $this->nodeMatchesProperty($criterion['property'], $criterion['value'] ?? null, $node);
                    break;
                case 'selector':
                    $match = $this->nodeMatchesSelector($criterion['value'], $node);
                    break;
                case 'class':
                    $match = $this->nodeMatchesClass($criterion['value'], $node);
                    break;
                case 'id':
                    $match = $this->nodeMatchesId($criterion['value'], $node);
                    break;
                case 'attribute':
                    $match = $this->nodeMatchesAttribute($criterion['value'], $node);
                    break;
                case 'media':
                    $match = $this->nodeMatchesMediaQuery($criterion['value'], $node);
                    break;
                case 'pseudo':
                    $match = $this->nodeMatchesPseudo($criterion['value'], $node);
                    break;
                default:
                    throw new \InvalidArgumentException("Unknown search criterion type: " . $criterion['type']);
            }

            if (!$match) {
                break;
            }
        }

        if ($match) {
            $newResults[] = $node;
        }

        if ($node->hasChildren()) {
            foreach ($node->getChildren() as $child) {
                $this->searchRecursively($child, $criteria, $newResults);
            }
        }
    }

    public function searchByType(string $type, CSSNode $node = null): self
    {
        $node = $node === null ? $this->root : $node;
        $newResults = [];

        if ($this->nodeMatchesType($type, $node)) {
            $newResults[] = $node;
        }

        if (!empty($node->getChildren())) {
            foreach ($node->getChildren() as $child) {
                $this->searchByType($type, $child);
            }
        }

        $this->results = array_merge($this->results, $newResults);

        return $this;
    }

    public function searchBySelector(string $selector, CSSNode $node = null): self
    {
        $node = $node === null ? $this->root : $node;

        if ($this->nodeMatchesSelector($selector, $node)) {
            $this->results[] = $node;
        }

        if ($node->hasChildren()) {
            foreach ($node->getChildren() as $child) {
                $this->searchBySelector($selector, $child);
            }
        }

        return $this;
    }

    public function searchByProperty(string $property, string $value = null, CSSNode $node = null): self
    {
        $node = $node === null ? $this->root : $node;

        if ($this->nodeMatchesProperty($property, $value, $node)) {
            $this->results[] = $node;
        }

        if ($node->hasChildren()) {
            foreach ($node->getChildren() as $child) {
                $this->searchByProperty($property, $value, $child);
            }
        }

        return $this;
    }

    public function searchByClass(string $class, CSSNode $node = null): self
    {
        $node = $node === null ? $this->root : $node;

        if ($this->nodeMatchesClass($class, $node)) {
            $this->results[] = $node;
        }

        if ($node->hasChildren()) {
            foreach ($node->getChildren() as $child) {
                $this->searchByClass($class, $child);
            }
        }

        return $this;
    }

    public function searchById(string $id, CSSNode $node = null): self
    {
        $node = $node === null ? $this->root : $node;

        if ($this->nodeMatchesId($id, $node)) {
            $this->results[] = $node;
        }

        if ($node->hasChildren()) {
            foreach ($node->getChildren() as $child) {
                $this->searchById($id, $child);
            }
        }

        return $this;
    }

    public function searchByAttribute(string $attribute, CSSNode $node = null): self
    {
        $node = $node === null ? $this->root : $node;

        if ($this->nodeMatchesAttribute($attribute, $node)) {
            $this->results[] = $node;
        }

        if ($node->hasChildren()) {
            foreach ($node->getChildren() as $child) {
                $this->searchByAttribute($attribute, $child);
            }
        }

        return $this;
    }

    public function searchByPseudo(string $pseudo, CSSNode $node = null): self
    {
        $node = $node === null ? $this->root : $node;

        if ($this->nodeMatchesPseudo($pseudo, $node)) {
            $this->results[] = $node;
        }

        if ($node->hasChildren()) {
            foreach ($node->getChildren() as $child) {
                $this->searchByPseudo($pseudo, $child);
            }
        }

        return $this;
    }

    public function searchByMediaQuery(string $mediaQuery, CSSNode $node = null): self
    {
        $node = $node === null ? $this->root : $node;

        if ($this->nodeMatchesMediaQuery($mediaQuery, $node)) {
            $this->results[] = $node;
        }

        if ($node->hasChildren()) {
            foreach ($node->getChildren() as $child) {
                $this->searchByMediaQuery($mediaQuery, $child);
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

    private function nodeMatchesType(string $type, CSSNode $node): bool
    {
        return $node instanceof $type;
    }

    private function nodeMatchesProperty(string $property, string $value = null, CSSNode $node): bool
    {
        if ($node instanceof Property && $node->property === $property) {
            return $value === null || $this->propertyHasValue($node, $value);
        }
        return false;
    }

    private function nodeMatchesSelector(string $selector, CSSNode $node): bool
    {
        return $node instanceof Selector && str_contains($node->selector, $selector);
    }

    private function nodeMatchesClass(string $class, CSSNode $node): bool
    {
        return $node instanceof Selector && strpos($node->selector, '.' . $class) !== false;
    }

    private function nodeMatchesId(string $id, CSSNode $node): bool
    {
        return $node instanceof Selector && strpos($node->selector, '#' . $id) !== false;
    }

    private function nodeMatchesAttribute(string $attribute, CSSNode $node): bool
    {
        return $node instanceof Selector && preg_match('/\[' . preg_quote($attribute, '/') . '[^]]*\]/', $node->selector);
    }

    private function nodeMatchesMediaQuery(string $mediaQuery, CSSNode $node): bool
    {
        return $node instanceof AtRule && str_starts_with($node->rule, '@media') && str_contains(trim("{$node->rule} {$node->params}"), $mediaQuery);
    }

    private function nodeMatchesPseudo(string $pseudo, CSSNode $node): bool
    {
        return $node instanceof Selector && strpos($node->selector, ':' . $pseudo) !== false;
    }
}
