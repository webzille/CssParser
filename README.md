# CssParser
Parse CSS file into a simple abstract syntax tree (AST) data structure.

## About Webzille / CssParser
This package parses a CSS file and puts each construct in it's own object in the order as they appear in the CSS file. The AST structure is fairly simple at the moment and their plenty room for improvements.

## Installation
To begin parsing CSS files with this packages you need to run the following command VIA composer.

```bash
composer require webzille/cssparser
```

## Usage
Using the parser is quite simple. To parse a CSS file and get the generated AST data structure back (AKA nodes).

```php
$parser = new Parser("stylesheet.css");
$nodes = $parser->parse()->getNodes();
```

Then to render the CSS you could render it with the help of the format class. If you don't use it, it would use the default format.

```php
$format = (new CssFormat)->setIndent("    ")->setNewLine("\n");
$render = new Render($nodes, $format);
$css = trim($render->css());
```

That example sets the same indentation and newline characters as are the defaults if you don't use the format object.

```php
$render = new Render($nodes);
$css = trim($render->css());
```

If you want minified CSS, instead of setting an empty string as indent and newline, you could simply use the `minify()` method which takes an optional boolean argument as it's value to make the minification dynamic (wether it's based on specific conditions or user input).

```php
$minified = true;
$format = (new CssFormat)->minify($minified);
$render = new Render($nodes, $format);
$css = trim($render->css());

// The following is the same as the previous
$format = (new CssFormat)->minify();
$render = new Render($nodes, $format);
$css = trim($render->css());
```

At the moment, minified CSS is simply the entire CSS in one line.

## Contributing
Contributions to the Webzille CSS Parser are welcome! Please ensure that you submit pull requests to the development branch.

## License
This project is licensed under the MIT License - see the LICENSE file for details.
