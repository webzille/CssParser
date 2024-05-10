<?php

namespace Webzille\CssParser;

class CssFormat
{
    private string $indent = "    ";
    private string $newLine = "\n";
    private bool $minified = false;

    public function indent()
    {
        return $this->minified ? '' : $this->indent;
    }

    public function newLine()
    {
        return $this->minified ? '' : $this->newLine;
    }

    public function minified()
    {
        return $this->minified;
    }

    public function setIndent($indent = "    ")
    {
        $this->indent = $indent;

        return $this;
    }

    public function setNewLine($newLine = "\n")
    {
        $this->newLine = $newLine;

        return $this;
    }

    public function minify($minify = true)
    {
        $this->minified = $minify;

        return $this;
    }
}