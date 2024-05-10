<?php

namespace Webzille\CssParser;

use Webzille\CssParser\Nodes\AtRule;
use Webzille\CssParser\Nodes\Comment;
use Webzille\CssParser\Nodes\CSSNode;
use Webzille\CssParser\Nodes\Property;
use Webzille\CssParser\Nodes\PropertyValue;
use Webzille\CssParser\Nodes\Selector;

class Render
{
    
    private CSSNode $root;
    private CssFormat $format;

    public function __construct(CSSNode $node, CssFormat $format = null)
    {
        $this->root = $node;
        $this->format = $format ?? new CssFormat();
    }

    public function css(CSSNode $node = null, int $indentation = 0, string $css = ''): string
    {
        $node = $node === null ? $this->root : $node;
        $indent = str_repeat($this->format->indent(), $indentation);
        $newLine = $this->format->newLine();
        foreach ($node->getChildren() as $child) {
            if ($child instanceOf Selector) {
                if ($child->hasChildren()) {
                    $css .= $indent . $child->selector . " {{$newLine}";
                    $css .= $this->css($child, $indentation + 1);
                    $css .= "$indent}$newLine$newLine";
                } else {
                    $css .= $indent . $child->selector . ", $newLine";
                }
            } elseif ($child instanceof Property) {
                $css .= "$indent" . $child->property . ":";
                $css .= $this->outputPropertyValue($child, $indentation);
                $css .= ";$newLine";
            } elseif ($child instanceof AtRule) {
                $css .= $this->outputAtRule($child, $indentation);
            } elseif ($child instanceof Comment) {
                $css .= "$indent{$child->comment}$newLine";
            }
        }

        return $css;
    }

    private function outputPropertyValue(CSSNode $property, int $indentation): string
    {
        $css = '';
        $indent = str_repeat($this->format->indent(), $indentation);
        $singleIndent = $this->format->indent();
        $newLine = $this->format->newLine();
        $runningCount = 1;
        foreach ($property->getChildren() as $value) {
            if ($value instanceof PropertyValue) {
                $css .= " {$value->value}";
                if ($value->getParent()->countChildren() !== $runningCount) {
                    $css .= ",{$newLine}{$singleIndent}$indent";
                }
            }
            $runningCount++;
        }

        return $css;
    }

    private function outputAtRule(AtRule $atRule, int $indentation = 0): string
    {
        $indent = str_repeat($this->format->indent(), $indentation);
        $css = $indent . $atRule->rule;
        $newLine = $this->format->newLine();

        if (!empty($atRule->params) && !is_numeric($atRule->params) && !is_bool($atRule->params)) {
            $css .= " " . $atRule->params;
        }

        if ($atRule->isComplex) {
            $css .= " {{$newLine}";
            $css .= $this->css($atRule, $indentation + 1);
            $css = rtrim($css) . "$newLine$indent}$newLine$newLine";
        } else {
            $css .= ";$newLine$newLine";
        }

        return $css;
    }
}