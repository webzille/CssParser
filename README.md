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

Using the parser is quite simple.

```php
$parser = new Tokenize("path/to/css.css");
$nodes = $parser->parse()->getNodes();
$css = $parser->render($nodes);
```

By default, the class renders pretty CSS, if you prefer minified CSS (or need a toggle to make it dynamic based on user input) you may chain the `minified(true)` method before the `render()` method.

```php
// Default for minified is false, setting it to true rendered minified CSS
$minified = true;
$css = $parser->minified($minified)->render($nodes);
```

## Contributing

Contributions to the Webzille CSS Parser are welcome! Please ensure that you submit pull requests to the development branch.

## License

This project is licensed under the MIT License - see the LICENSE file for details.
