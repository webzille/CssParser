<?php

namespace Webzille\CssParser;

use Webzille\CssParser\Nodes\AtRule;
use Webzille\CssParser\Nodes\Comment;
use Webzille\CssParser\Nodes\CSSNode;
use Webzille\CssParser\Nodes\Property;
use Webzille\CssParser\Nodes\PropertyValue;
use Webzille\CssParser\Nodes\Selector;

class Tokenize
{

    private string $file;
    private bool $inProperty = false;
    private bool $isInString = false;
    private bool $inSelector = false;
    private int $inParentheses = 0;
    private bool $inComment = false;
    private bool $isComplex = true;
    private int $lineNo = 1;
    private int $col = 0;
    private string $charset = "UTF-8";
    private CSSNode $root;
    private CSSNode $currentNode;
    private CSSNode $currentProperty;
    private string $indent = "    ";
    private string $newLine = "\n";
    private bool $minified = false;
    private string $buffer = '';


    public function __construct(string $file)
    {
        $this->file = $file;
        $this->root = new CSSNode('root');
        $this->currentNode = $this->root;
    }

    public function parse(): self
    {
        $handle = fopen($this->file, 'r');

        if (!$handle) {
            throw new \Exception("Invalid File: {$this->file}");
        }

        while (!feof($handle)) {
            $line = trim(fgets($handle));

            if (!empty($line)) {
                $this->tokenize($line, strlen($line));
            }

            $this->lineNo++;
        }

        fclose($handle);

        return $this;
    }
    
    private function setCharset(string $charset): void
    {
        $this->charset = str_replace(["'", '"'], '', strtoupper($charset));
    }

    public function minified(bool $minified = false): self
    {
        $this->minified = $minified;

        return $this;
    }

    private function tokenize(string $line, int $length): void
    {
        $this->col = 0;

        while ($this->col < $length) {
            $char = $line[$this->col];
            $this->buffer .= $char;

            $this->parseDocument($char, $line, $length);

            $this->col++;
        }
    }

    private function parseDocument($char, $line, $length)
    {
        $this->parseBlock($char, $line);

        $this->parseComment($char, $line, $length);

        $this->parseLiterals($char);
    }

    private function parseLiterals($char)
    {
        if ($char === '"' || $char === "'" && !$this->inComment) {
            $this->isInString = !$this->isInString;
        }

        if ($char === '(' && !$this->inComment) {
            $this->inParentheses++;
        }

        if ($char === ')' && !$this->inComment) {
            $this->inParentheses--;
        }
    }

    private function parseBlock($char, $line)
    {
        if ($char === '@' && !$this->inComment) {
            $this->parseAtRule($line);
        }

        if ($char === '{' || ($char === ',' && !$this->inProperty && $this->inParentheses === 0) && !$this->inComment) {
            $this->parseSelector($line);
        }

        if ($this->inParentheses === 0 && !$this->isInString && !$this->inComment) {
            $this->parsePropertyBlock($char);
        }

        if ($char === '}' && !$this->inComment) {
            $this->parseBlockEnd();
        }
    }

    private function parseAtRule($line)
    {
        $atRuleEnd = strcspn($line, '{};', $this->col);
        $fullAtRule = trim(substr($line, $this->col, $atRuleEnd));
        $this->isComplex = $line[$atRuleEnd + $this->col] === ';' ? false : true;
        $this->col += $atRuleEnd;

        $spacePos = strpos($fullAtRule, ' ');
        $atRule = ($spacePos !== false) ? substr($fullAtRule, 0, $spacePos) : $fullAtRule;
        $params = ($spacePos !== false) ? trim(substr($fullAtRule, $spacePos)) : '';

        if ($atRule === '@charset') {
            $this->setCharset($params);
        }

        $node = new AtRule($atRule, $params, $this->isComplex);
        $this->currentNode->addChild($node);

        if ($this->isComplex) {
            $this->currentNode = $node;
        }

        $this->buffer = '';
        $this->inSelector = $this->isComplex;
    }

    private function parseSelector($line)
    {
        $this->buffer = rtrim($this->buffer, ',');
        $selectorValue = trim(str_replace(['}', '{'], '', $this->buffer));
        $selector = new Selector($selectorValue);
        $this->currentNode->addChild($selector);
        $this->buffer = '';

        if ($line[$this->col] !== ',') {
            $this->inSelector = true;
            $this->currentNode = $selector;
        }
    }

    private function parsePropertyBlock($char)
    {
        if ($char === ':' && $this->inSelector && strpos(trim($this->buffer), ':') && strpos($this->buffer, '.') === false && strpos($this->buffer, '#') === false) {
            $this->parseProperty();
        }

        if ($this->inProperty && ($char === ';' || $char === '}' || ($char === ',' && strrpos($this->buffer, ')')))) {
            $this->parsePropertyValue($char);
        }
    }

    private function parseProperty()
    {
        $property = trim(str_replace(':', '', $this->buffer));
        $this->currentProperty = new Property($property);
        $this->currentNode->addChild($this->currentProperty);
        $this->buffer = '';
        $this->inProperty = true;
    }

    private function parsePropertyValue($char)
    {
        $this->buffer = rtrim($this->buffer, ',;');

        $value = trim(str_replace('}', '', $this->buffer));
        $this->currentProperty->addChild(new PropertyValue($value));
        $this->buffer = '';

        if ($char !== ',' && !strrpos($this->buffer, ')')) {
            $this->inProperty = false;
        }
    }

    private function parseComment($char, $line, $length)
    {
        if ($char === '/' && $this->col + 1 < $length && $line[$this->col + 1] === '*') {
            $this->parseCommentStart($line);
        }

        if ($this->inComment && $this->col + 1 === $length) {
            $this->parseCommentLine();
        }

        if ($char === '*' && $this->col + 1 < $length && $line[$this->col + 1] == '/') {
            $this->parseCommentEnd();
        }
    }

    private function parseCommentStart($line)
    {
        $this->inComment = true;
        $commentEnd = strpos($line, '*/', $this->col + 2);
        if ($commentEnd !== false) {
            $comment = substr($line, $this->col, $commentEnd - $this->col + 2);
            $this->col = $commentEnd + 1;
            $comment = new Comment($comment);
            $this->currentNode->addChild($comment);
            $this->buffer = '';
            $this->inComment = false;
        }
    }

    private function parseCommentLine()
    {
        $comment = new Comment(trim($this->buffer));
        $this->currentNode->addChild($comment);
        $this->buffer = '';
    }

    private function parseCommentEnd()
    {
        $this->inComment = false;
        $comment = new Comment(trim($this->buffer) . '/');
        $this->currentNode->addChild($comment);
        $this->buffer = '';
    }

    private function parseBlockEnd()
    {
        if (!empty($this->currentNode->parent))
        {
            $this->currentNode = $this->currentNode->parent;
        }
        $this->buffer = '';
        $this->inSelector = false;
    }

    public function getNodes(): CSSNode
    {
        return $this->root;
    }

    public function render(CSSNode $node, int $indentation = 0, string $css = ''): string
    {
        $indent = $this->minified ? '' : str_repeat($this->indent, $indentation);
        $newLine = $this->minified ? '' : $this->newLine;
        foreach ($node->children as $child) {
            if ($child instanceOf Selector) {
                if ($child->hasChildren()) {
                    $css .= $indent . $child->selector . " {{$newLine}";
                    $css .= $this->render($child, $indentation + 1);
                    $css .= "$indent}$newLine";
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
        $indent = $this->minified ? '' : str_repeat($this->indent, $indentation);
        $singleIndent = $this->minified ? '' : $this->indent;
        $newLine = $this->minified ? '' : $this->newLine;
        $runningCount = 1;
        foreach ($property->children as $value) {
            if ($value instanceof PropertyValue) {
                $css .= " {$value->getValue()}";
                if ($value->parent->countChildren() !== $runningCount) {
                    $css .= ",{$newLine}{$singleIndent}$indent";
                }
            }
            $runningCount++;
        }

        return $css;
    }

    private function outputAtRule(AtRule $atRule, int $indentation = 0): string
    {
        $indent = $this->minified ? '' : str_repeat($this->indent, $indentation);
        $css = $indent . $atRule->rule;
        $newLine = $this->minified ? '' : $this->newLine;

        if (!empty($atRule->params) && !is_numeric($atRule->params) && !is_bool($atRule->params)) {
            $css .= " " . $atRule->params;
        }

        if ($atRule->isComplex) {
            $css .= " {{$newLine}";
            $css .= $this->render($atRule, $indentation + 1);
            $css .= "$indent}$newLine$newLine";
        } else {
            $css .= ";$newLine";
        }

        return $css;
    }
}