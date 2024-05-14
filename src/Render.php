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
    private string $indent;
    private string $newLine;

    public function __construct(CSSNode $node, CssFormat $format = null)
    {
        $this->root = $node;
        $this->format = $format ?? new CssFormat();
        $this->indent = $this->format->indent();
        $this->newLine = $this->format->newLine();
    }

    public function css(CSSNode $node = null, int $indentation = 0, string $css = ''): string
    {
        $node = $node === null ? $this->root : $node;
        $indent = str_repeat($this->indent, $indentation);
        foreach ($node->getChildren() as $child) {
            if ($child instanceOf Selector) {
                if ($child->hasChildren()) {
                    $css .= $indent . $child->selector . " {{$this->newLine}";
                    $css .= $this->css($child, $indentation + 1);
                    $css = rtrim($css) . "{$this->newLine}$indent}{$this->newLine}{$this->newLine}";
                } else {
                    $css .= $indent . $child->selector . ", {$this->newLine}";
                }
            } elseif ($child instanceof Property) {
                $css .= "$indent" . $child->property . ":";
                $css .= $this->outputPropertyValue($child, $indentation);
                $css .= ";{$this->newLine}";
            } elseif ($child instanceof AtRule) {
                $css .= $this->outputAtRule($child, $indentation);
            } elseif ($child instanceof Comment) {
                $css .= "$indent{$child->comment}{$this->newLine}";
            }
        }

        return $css;
    }

    private function outputPropertyValue(CSSNode $property, int $indentation): string
    {
        $css = '';
        $indent = str_repeat($this->indent, $indentation);
        $runningCount = 1;
        foreach ($property->getChildren() as $value) {
            if ($value instanceof PropertyValue) {
                $important = ($value->important) ? ' !important' : '';
                $css .= " {$value->value}" . $important;
                if ($value->getParent()->countChildren() !== $runningCount) {
                    $css .= ",{$this->newLine}{$this->indent}$indent";
                }
            }
            $runningCount++;
        }

        return $css;
    }

    private function outputAtRule(AtRule $atRule, int $indentation = 0): string
    {
        $indent = str_repeat($this->indent, $indentation);
        $css = $indent . $atRule->rule;

        if (!empty($atRule->params) && !is_numeric($atRule->params) && !is_bool($atRule->params)) {
            $css .= " " . $atRule->params;
        }

        if ($atRule->isComplex) {
            $css .= " {{$this->newLine}";
            $css .= $this->css($atRule, $indentation + 1);
            $css = rtrim($css) . "{$this->newLine}$indent}{$this->newLine}{$this->newLine}";
        } else {
            $css .= ";{$this->newLine}{$this->newLine}";
        }

        return $css;
    }
}