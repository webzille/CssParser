<?php

namespace Webzille\CssParser;

class CssFormat
{
    private string $indent = "    ";
    private string $newLine = "\n";

    public function indent(): string
    {
        return $this->indent;
    }

    public function newLine(): string
    {
        return $this->newLine;
    }

    public function setIndent(string $indent = "    "): self
    {
        $this->indent = $indent;

        return $this;
    }

    public function setNewLine(string $newLine = "\n"): self
    {
        $this->newLine = $newLine;

        return $this;
    }

    public function minify(bool $minified = true): self
    {
        if ($minified) {
            $this->setIndent('')->setNewLine('');
        }
        
        return $this;
    }
}
