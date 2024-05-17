<?php

namespace Webzille\CssParser\Util;

use Webzille\CssParser\Nodes\Comment;
use Webzille\CssParser\Nodes\CSSNode;
use Webzille\CssParser\Nodes\Property;
use Webzille\CssParser\Nodes\PropertyValue;

class Optimize
{

    private CSSNode $root;

    private static ?Optimize $instance = null;

    private array $modified = [];

    private array $shorthandProperties = [
        'margin' => ['margin-top', 'margin-right', 'margin-bottom', 'margin-left'],
        'padding' => ['padding-top', 'padding-right', 'padding-bottom', 'padding-left'],
        'border' => ['border-width', 'border-style', 'border-color'],
        'border-width' => ['border-top-width', 'border-right-width', 'border-bottom-width', 'border-left-width'],
        'border-style' => ['border-top-style', 'border-right-style', 'border-bottom-style', 'border-left-style'],
        'border-color' => ['border-top-color', 'border-right-color', 'border-bottom-color', 'border-left-color'],
        'border-radius' => ['border-top-left-radius', 'border-top-right-radius', 'border-bottom-right-radius', 'border-bottom-left-radius'],
        'background' => [
            'background-color', 'background-image', 'background-repeat',
            'background-attachment', 'background-position', 'background-size', 'background-origin', 'background-clip'
        ],
        'font' => [
            'font-style', 'font-variant', 'font-weight', 'font-stretch', 'font-size',
            'line-height', 'font-family'
        ],
        'list-style' => ['list-style-type', 'list-style-position', 'list-style-image'],
        'outline' => ['outline-color', 'outline-style', 'outline-width'],
        'overflow' => ['overflow-x', 'overflow-y'],
        'flex' => ['flex-grow', 'flex-shrink', 'flex-basis'],
        'grid' => [
            'grid-template-rows', 'grid-template-columns', 'grid-template-areas',
            'grid-auto-rows', 'grid-auto-columns', 'grid-auto-flow',
            'grid-column-gap', 'grid-row-gap'
        ],
        'grid-area' => ['grid-row-start', 'grid-column-start', 'grid-row-end', 'grid-column-end'],
        'grid-template' => ['grid-template-rows', 'grid-template-columns', 'grid-template-areas'],
        'grid-gap' => ['grid-row-gap', 'grid-column-gap'],
        'transition' => ['transition-property', 'transition-duration', 'transition-timing-function', 'transition-delay'],
        'animation' => [
            'animation-name', 'animation-duration', 'animation-timing-function',
            'animation-delay', 'animation-iteration-count', 'animation-direction',
            'animation-fill-mode', 'animation-play-state'
        ],
        'place-content' => ['align-content', 'justify-content'],
        'place-items' => ['align-items', 'justify-items'],
        'place-self' => ['align-self', 'justify-self']
    ];

    private array $vendorPrefixes = [
        'properties' => [
            'animation' => ['-webkit-', '-moz-', '-o-'],
            'animation-delay' => ['-webkit-', '-moz-', '-o-'],
            'animation-direction' => ['-webkit-', '-moz-', '-o-'],
            'animation-duration' => ['-webkit-', '-moz-', '-o-'],
            'animation-fill-mode' => ['-webkit-', '-moz-', '-o-'],
            'animation-iteration-count' => ['-webkit-', '-moz-', '-o-'],
            'animation-name' => ['-webkit-', '-moz-', '-o-'],
            'animation-play-state' => ['-webkit-', '-moz-', '-o-'],
            'animation-timing-function' => ['-webkit-', '-moz-', '-o-'],

            'transition' => ['-webkit-', '-moz-', '-o-'],
            'transition-delay' => ['-webkit-', '-moz-', '-o-'],
            'transition-duration' => ['-webkit-', '-moz-', '-o-'],
            'transition-property' => ['-webkit-', '-moz-', '-o-'],
            'transition-timing-function' => ['-webkit-', '-moz-', '-o-'],

            'transform' => ['-webkit-', '-ms-', '-moz-'],
            'transform-origin' => ['-webkit-', '-ms-', '-moz-'],
            'transform-style' => ['-webkit-', '-ms-', '-moz-'],

            'flex' => ['-webkit-'],
            'flex-basis' => ['-webkit-'],
            'flex-direction' => ['-webkit-'],
            'flex-flow' => ['-webkit-'],
            'flex-grow' => ['-webkit-'],
            'flex-shrink' => ['-webkit-'],
            'flex-wrap' => ['-webkit-'],
            'align-content' => ['-webkit-'],
            'align-items' => ['-webkit-'],
            'align-self' => ['-webkit-'],
            'justify-content' => ['-webkit-'],
            'order' => ['-webkit-'],

            'appearance' => ['-webkit-', '-moz-'],
            'backface-visibility' => ['-webkit-', '-moz-'],
            'background-clip' => ['-webkit-', '-moz-'],
            'box-shadow' => ['-webkit-', '-moz-'],
            'column-count' => ['-webkit-', '-moz-'],
            'column-gap' => ['-webkit-', '-moz-'],
            'column-rule' => ['-webkit-', '-moz-'],
            'column-rule-color' => ['-webkit-', '-moz-'],
            'column-rule-style' => ['-webkit-', '-moz-'],
            'column-rule-width' => ['-webkit-', '-moz-'],
            'column-width' => ['-webkit-', '-moz-'],
            'filter' => ['-webkit-', '-ms-'],
            'grid-column' => ['-ms-'],
            'grid-row' => ['-ms-'],
            'hyphens' => ['-webkit-', '-ms-', '-moz-'],
            'mask' => ['-webkit-', '-ms-'],
            'opacity' => ['-webkit-', '-moz-'],
            'perspective' => ['-webkit-', '-moz-'],
            'reflect' => ['-webkit-'],
            'tab-size' => ['-moz-', '-o-'],
            'text-decoration' => ['-webkit-', '-moz-'],
            'text-size-adjust' => ['-webkit-', '-ms-'],
            'user-select' => ['-webkit-', '-moz-', '-ms-'],
            'writing-mode' => ['-webkit-', '-moz-', '-ms-']
        ],
        'values' => [
            'display' => [
                'flex' => ['-webkit-box', '-moz-box', '-ms-flexbox', '-webkit-flex'],
            ],
            'background' => [
                'linear-gradient' => ['-webkit-linear-gradient', '-moz-linear-gradient', '-o-linear-gradient'],
                'radial-gradient' => ['-webkit-radial-gradient', '-moz-radial-gradient', '-o-radial-gradient'],
            ],
            'background-image' => [
                'linear-gradient' => ['-webkit-linear-gradient', '-moz-linear-gradient', '-o-linear-gradient'],
                'radial-gradient' => ['-webkit-radial-gradient', '-moz-radial-gradient', '-o-radial-gradient'],
            ],
            'user-select' => [
                'none' => ['-webkit-none', '-moz-none', '-ms-none'],
            ],
            'position'  => [
                'sticky' => ['-webkit-sticky'],
            ]
        ]
    ];

    public function __construct(CSSNode $node)
    {
        $this->root = $node;
    }

    public static function optimize(CSSNode $node): Optimize
    {
        if (self::$instance === null || !(self::$instance instanceof Optimize)) {
            self::$instance = new Optimize($node);
        } else {
            self::$instance->root = $node;
        }

        return self::$instance;
    }

    public function getNodes(): CSSNode
    {
        return $this->root;
    }

    public function clearModified(): self
    {
        $this->modified = [];

        return $this;
    }

    public function removeWhitespace(CSSNode $node = null): self
    {
        $node = $node === null ? $this->root : $node;
        foreach ($node->getChildren() as $child) {
            if ($child instanceof PropertyValue) {
                $oldValue = $child->value;
                $child->value = preg_replace('/\s+/', ' ', trim($child->value));
                if ($oldValue != $child->value) {
                    $this->logModified("Cleared Whitespace: From `$oldValue` to `{$child->value}`; (Line: {$child->lineNo})");
                }
            } elseif ($child instanceof Comment) {
                $this->logModified("Removed Comment: {$child->comment}; (Line: {$child->lineNo})");
                $node->removeChild($child);
            } else {
                $this->removeWhitespace($child);
            }
        }

        return $this;
    }

    public function removeDuplicates(CSSNode $node = null): self
    {
        $node = $node === null ? $this->root : $node;
        $uniqueRules = [];

        foreach ($node->getChildren() as $child) {
            if ($child instanceof Property) {
                $property = $child->property;
                if (!isset($uniqueRules[$property])) {
                    if ($this->isVendor($child->getChildren()[0]->value)) {
                        continue;
                    }
                    $uniqueRules[$property] = $child;
                } else {
                    $this->logModified("Removed duplicate: `{$uniqueRules[$property]->property}: {$uniqueRules[$property]->getChildren()[0]->value};` (Line: {$uniqueRules[$property]->lineNo})");
                    $node->removeChild($child);
                }
            } else {
                $this->removeDuplicates($child);
            }
        }

        return $this;
    }

    private function isVendor(string $vendorable): bool
    {
        $vendorPrefixes = [
            '-webkit-',
            '-moz-',
            '-ms-',
            '-o-',
        ];

        foreach ($vendorPrefixes as $prefix) {
            if (str_contains($vendorable, $prefix) === true) {
                return true;
            }
        }

        return false;
    }

    private function logModified(string $log): void
    {
        $this->modified[] = $log;
    }

    private function collapseValues(array $values): string
    {
        $values = array_values($values);

        if (count($values) === 4 && $values[0] === $values[2] && $values[1] === $values[3]) {
            return ($values[0] === $values[1]) ? $values[0] : "{$values[0]} {$values[1]}";
        } else if (count($values) === 4 && $values[1] === $values[3]) {
            return "{$values[0]} {$values[1]} {$values[2]}";
        }

        return implode(' ', $values);
    }

    public function toShorthand(CSSNode $node = null): self
    {
        $node = $node === null ? $this->root : $node;
        $allLonghands = array_merge(...array_values($this->shorthandProperties));

        $properties = array_reduce($node->getChildren(), function ($carry, $property) use ($allLonghands) {
            if ($property instanceof Property && in_array($property->property, $allLonghands)) {
                $carry[$property->property] = $property;
            }
            return $carry;
        }, []);

        foreach ($node->getChildren() as $child) {
            if (!$child instanceof Property) {
                $this->toShorthand($child);
                continue;
            }

            foreach ($this->shorthandProperties as $shorthand => $longhands) {
                $values = array_intersect_key($properties, array_flip($longhands));
                if (count($values) < count($longhands)) {
                    continue;
                }

                $hasMultipleValues = $isImportant = false;
                $valueArray = [];

                foreach ($longhands as $longhand) {
                    if (!isset($values[$longhand])) {
                        continue;
                    }

                    $property = $values[$longhand];
                    $propertyValue = $property->getChildren()[0];

                    if (!$propertyValue instanceof PropertyValue || count($property->getChildren()) > 1) {
                        continue;
                    }

                    $valueArray[] = $propertyValue->value;
                    $isImportant = $isImportant || $propertyValue->important;
                    if (count(explode(' ', $propertyValue->value)) > 1) {
                        $hasMultipleValues = true;
                        break;
                    }
                }

                if ($hasMultipleValues) {
                    continue;
                }

                $valueString = $this->collapseValues($valueArray);
                $newProperty = new Property($shorthand, $child->lineNo);
                $newProperty->addChild(new PropertyValue($valueString, $child->lineNo, $isImportant));
                $node->addChild($newProperty);

                foreach ($longhands as $longhand) {
                    if (!isset($properties[$longhand])) {
                        continue;
                    }

                    $this->logModified("Removed: $longhand: {$properties[$longhand]->getChildren()[0]->value}; (Line: {$properties[$longhand]->lineNo})");
                    $node->removeChild($properties[$longhand]);
                    unset($properties[$longhand]);
                }

                $this->logModified("Created: $shorthand: $valueString; (Line: {$child->lineNo})");
            }
        }

        return $this;
    }

    public function getModified(): array
    {
        return $this->modified;
    }

    public function optimizeColors(CSSNode $node = null): self
    {
        $node = $node === null ? $this->root : $node;

        foreach ($node->getChildren() as $child) {
            if ($child instanceof PropertyValue) {
                $oldValue = $child->value;
                $child->value = preg_replace('/#([0-9a-f])\1([0-9a-f])\2([0-9a-f])\3/i', '#$1$2$3', $child->value);
                if ($oldValue != $child->value) {
                    $this->logModified("Changed: $oldValue to {$child->value}; (Line: {$child->lineNo})");
                }
            } else {
                $this->optimizeColors($child);
            }
        }

        return $this;
    }

    function vendorPrefix(CSSNode $node = null): self
    {
        $node = $node === null ? $this->root : $node;
        
        $children = $node->getChildren();
        
        foreach ($children as $index => $child) {
            if (!$child instanceof Property) {
                $this->vendorPrefix($child);
                continue;
            }
            
            $property = $child->property;
            $valueNodes = $child->getChildren();

            if (isset($this->vendorPrefixes['properties'][$property])) {
                foreach ($this->vendorPrefixes['properties'][$property] as $prefix) {
                    $prefixedProperty = new Property($prefix . $property, $child->lineNo);
                    $prefixedProperty->setChildren($valueNodes);

                    $this->logModified("Added Prefixed Property: $prefix$property; (Line: {$child->lineNo})");

                    array_splice($children, $index, 0, [$prefixedProperty]);
                    $index++;
                }
            }

            foreach ($valueNodes as $valueNode) {
                if (!$valueNode instanceof PropertyValue || !isset($this->vendorPrefixes['values'][$property])) {
                    continue;
                }

                $value = $valueNode->value;

                foreach ($this->vendorPrefixes['values'][$property] as $originalValue => $prefixes) {
                    if (strpos($value, $originalValue) === false) {
                        continue;
                    }

                    foreach ($prefixes as $prefix) {
                        $prefixedValue = str_replace($originalValue, $prefix, $value);
                        $prefixedProperty = new Property($property, $child->lineNo);
                        $prefixedValueNode = new PropertyValue($prefixedValue, $child->lineNo, $valueNode->important);
                        $prefixedProperty->addChild($prefixedValueNode);

                        $this->logModified("Added Prefixed Value: $prefixedValue; (Line: {$child->lineNo})");

                        array_splice($children, $index, 0, [$prefixedProperty]);
                        $index++;
                    }
                }
            }

            $this->vendorPrefix($child);
        }

        $node->setChildren($children);

        return $this;
    }
}
