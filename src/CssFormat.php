<?php

namespace Webzille\CssParser;

class CssFormat
{
    private string $indent = "    ";
    private string $newLine = "\n";

    public function indent()
    {
        return $this->indent;
    }

    public function newLine()
    {
        return $this->newLine;
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

    public function minify($minified = true)
    {
        if ($minified) {
            $this->setIndent('')->setNewLine('');
        }
        
        return $this;
    }
}
