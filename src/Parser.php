<?php

namespace Webzille\CssParser;

use Webzille\CssParser\Enum\State;
use Webzille\CssParser\Nodes\AtRule;
use Webzille\CssParser\Nodes\Comment;
use Webzille\CssParser\Nodes\CSSNode;
use Webzille\CssParser\Nodes\Property;
use Webzille\CssParser\Nodes\PropertyValue;
use Webzille\CssParser\Nodes\Selector;

class Parser
{

    private string $file;
    private string $buffer = '';
    private string $charset = "UTF-8";
    private bool $isComplex = true;
    private bool $isInSingleQuotes = false;
    private bool $isInDoubleQuotes = false;
    private int $col = 0;
    private int $lineNo = 1;
    private int $inParentheses = 0;
    private CSSNode $root;
    private CSSNode $currentNode;
    private CSSNode $currentProperty;
    private State $state = State::Root;
    private ?State $previousState = null;


    public function __construct(string $file)
    {
        $this->file = $file;
        $this->root = $this->currentNode = new CSSNode($file, $this->lineNo);
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

    public function getCharset(): string
    {
        return $this->charset;
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

    private function parseDocument(string $char, string $line, int $length): void
    {
        if ($this->state !== State::Comment) {
            $this->parseBlock($char, $line);

            $this->parseLiterals($char);
        }

        $this->parseComment($char, $line, $length);
    }

    private function parseLiterals(string $char): void
    {
        switch ($char) {
            case '"':
                $this->isInSingleQuotes = !$this->isInSingleQuotes;
                break;
            case "'":
                $this->isInDoubleQuotes = !$this->isInDoubleQuotes;
                break;
            case '(':
                $this->inParentheses++;
                break;
            case ')':
                $this->inParentheses--;
                break;
        }
    }

    private function parseBlock(string $char, string $line): void
    {
        if ($char === '@') {
            $this->parseAtRule($line);
        }

        if ($char === '{' || ($char === ',' && $this->state !== State::Property && !$this->inLiteral())) {
            $this->parseSelector($line);
        }

        if (!$this->inLiteral()) {
            $this->parsePropertyBlock($char);
        }

        if ($char === '}') {
            $this->parseBlockEnd();
        }
    }

    private function inString(): bool
    {
        return $this->isInSingleQuotes || $this->isInDoubleQuotes;
    }

    private function inParentheses(): bool
    {
        return $this->inParentheses > 0;
    }

    private function inLiteral(): bool
    {
        return $this->inParentheses() || $this->inString();
    }

    private function parseAtRule(string $line): void
    {
        $atRuleEnd = strcspn($line, '{};', $this->col);
        $fullAtRule = trim(substr($line, $this->col, $atRuleEnd));
        $this->isComplex = $line[$atRuleEnd + $this->col] !== ';';
        $this->col += $atRuleEnd;

        $spacePos = strpos($fullAtRule, ' ');
        $atRule = ($spacePos !== false) ? substr($fullAtRule, 0, $spacePos) : $fullAtRule;
        $params = ($spacePos !== false) ? trim(substr($fullAtRule, $spacePos)) : '';

        if ($atRule === '@charset') {
            $this->setCharset($params);
        }

        $node = new AtRule($atRule, $this->lineNo, $params, $this->isComplex);
        $this->currentNode->addChild($node);

        if ($this->isComplex) {
            $this->currentNode = $node;
            $this->setState(State::Selector);
        }

        $this->buffer = '';
    }

    private function parseSelector(string $line): void
    {
        $this->buffer = rtrim($this->buffer, ',');
        $selectorValue = trim(str_replace(['}', '{'], '', $this->buffer));
        $selector = new Selector($selectorValue, $this->lineNo);
        $this->currentNode->addChild($selector);
        $this->buffer = '';

        if ($line[$this->col] !== ',') {
            $this->setState(State::Selector);
            $this->currentNode = $selector;
        }
    }

    private function parsePropertyBlock(string $char): void
    {
        if ($char === ':' && $this->state === State::Selector && strpos(trim($this->buffer), ':') && strpos($this->buffer, '.') === false && strpos($this->buffer, '#') === false) {
            $this->parseProperty();
        }

        if ($this->state === State::Property && ($char === ';' || $char === '}' || ($char === ',' && strrpos($this->buffer, ')')))) {
            $this->parsePropertyValue($char);
        }
    }

    private function parseProperty(): void
    {
        $property = trim(str_replace(':', '', $this->buffer));
        $this->currentProperty = new Property($property, $this->lineNo);
        $this->currentNode->addChild($this->currentProperty);
        $this->setState(State::Property);
        $this->buffer = '';
    }

    private function parsePropertyValue(string $char): void
    {
        $this->buffer = rtrim($this->buffer, ',;');

        $value = trim(str_replace('}', '', $this->buffer));
        $this->currentProperty->addChild(new PropertyValue($value, $this->lineNo));
        $this->buffer = '';

        if ($char !== ',' && !strrpos($this->buffer, ')')) {
            $this->setState(State::Selector);
        }
    }

    private function parseComment(string $char, string $line, int $length): void
    {
        if ($char === '/' && $this->col + 1 < $length && $line[$this->col + 1] === '*') {
            $this->parseCommentStart($line);
        }

        if ($this->state === State::Comment && $this->col + 1 === $length) {
            $this->parseCommentLine();
        }

        if ($char === '*' && $this->col + 1 < $length && $line[$this->col + 1] == '/') {
            $this->parseCommentEnd();
        }
    }

    private function parseCommentStart(string $line): void
    {
        $this->setState(State::Comment);
        $commentEnd = strpos($line, '*/', $this->col + 2);
        if ($commentEnd !== false) {
            $comment = substr($line, $this->col, $commentEnd - $this->col + 2);
            $this->col = $commentEnd + 1;
            $comment = new Comment($comment, $this->lineNo);
            $this->currentNode->addChild($comment);
            $this->setState($this->previousState);
            $this->buffer = '';
        }
    }

    private function parseCommentLine(): void
    {
        $comment = new Comment(trim($this->buffer), $this->lineNo);
        $this->currentNode->addChild($comment);
        $this->buffer = '';
    }

    private function parseCommentEnd(): void
    {
        $this->setState($this->previousState);
        $comment = new Comment(trim($this->buffer) . '/', $this->lineNo);
        $this->currentNode->addChild($comment);
        $this->buffer = '';
    }

    private function parseBlockEnd(): void
    {
        $this->currentNode = $this->currentNode->getParent() ?? $this->currentNode;
        $this->setState(State::Root);
        $this->buffer = '';
    }

    private function setState(State $state): void
    {
        $this->previousState = $this->state;
        $this->state = $state;
    }

    public function getNodes(): CSSNode
    {
        return $this->root;
    }
}