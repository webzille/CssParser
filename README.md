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

## Searching

The search utility class is provided to help searching through the CSS AST Data Structure in various ways. You could search for a specific selector, property, property / value pair or other various methods. To use the search utility you could either instantiate the class yourself, or use the static search factory method to get the instance and do your search in one line.

```php
$search = Search::search($nodes);

// Or the following if you prefer
$search = new Search($nodes);
```

The following search methods are included at the moment
- **searchByType:** Searches the AST data structure for every node that are the instance of a specific type. Besides the optional $node argument (the AST Data Structure), the $type argument expected should take form of `AtRule::class`. If no $node is provided, it will use the $node
```php
$search->searchByType(Comment::class);
```
- **searchBySelector:** Searches for the requested selector. The match could be partial or full, it's not strict.
- **searchByProperty:** This method either searches for the requested property or for property / value pair if you additionally provide it with a value that you want the matched property to be.
- **searchByClass:** This method searches for a selector that has the requested class in it. It is assumed you not to provide it with a period, the period is prepanded automatically.
- **searchById:** As the previous search method, this searches for the requested ID. You shouldn't add the hash symbol before the name of the ID in the arguement. It is prepanded automatically.
- **searchByAttribute:** Searches for any occurances of the requested attribute within the available selectors. (For example `href` in `a[href=^]`).
- **searchByPseudo:** Searches for any occrances of the requested pseudo classes. You shouldn't enter any symbols into the argument with the pseudo you want to find. (Like `:after` for instance).
- **searchByMedia:** Searches for any occurances of the requested media query within the at-rules. The query could be partial or in full entirety.

For a more comprehensive example of searching, you may check out the **searchDemo.php** provided.

If you use the static factory method, you could easily chain other methods to it without having to dirty up your code with either additional lines or using parentheses.

## Contributing
Contributions to the Webzille CSS Parser are welcome! Please ensure that you submit pull requests to the development branch.

## License
This project is licensed under the MIT License - see the LICENSE file for details.
