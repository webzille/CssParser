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
If you use the static factory method, you could easily chain other methods to it without having to dirty up your code with either additional lines or using parentheses.

The following search methods are included at the moment:

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
- **find:** This is a method that would append multiple searches to one result set. It expects an array of search criteria within an array.
```php
$criteria = [
    [
        'type'  => 'type',
        'value' => \Webzille\CssParser\Nodes\AtRule::class
    ],
    [
        'type'      => 'property',
        'property'  => 'font-family',
        'value'     => 'MyFont'
    ],
    [
        'type'  => 'selector',
        'value' => '.container'
    ]
];

$search = Search::search($nodes)->find($criteria);

$results = $search->results();
```
- **searchResults:** This method uses the results of your previous search query as the search subject (the haystack) for your search criteria in the same expected format as the previous **find** method.

You get your results through the method `results()` and if you don't want to mingle results between various search queries, you would need to clear the results between searches VIA the `clearResults()` method

For a more comprehensive example of searching, you may check out the **searchDemo.php** provided.

## Optimization

This package also provides an optimization utility class you may use to optimize the parsed CSS data structure which you could later render as minified or pretty CSS. Just like the search utility class, you could initiate the class directly or by using the static factory method for the same reasons as the search utility class.

```php
$optimizer = Optimize::optimize($nodes);

// Or the following if you prefer or not going to chain any methods to it.
$optimizer = new Optimize($nodes);
```

The following optimization options it offers at the moment.

- **removeWhitespace:** This method does two things, it removes any repeating whitespace (if you have more than one space within a `content` property for instance) AND it removes all the comments.
- **removeDuplicates:** This method removes any duplicate properties as long as their values are not vendor prefixed (it completely ignores any property that has vendor prefixed values at the moment so you could have multiple properties with the same vendor prefixed value).
- **toShorthand:** This method converts any properties to shorthand variant whenever possible (based on provided properties and their values, doesn't fill in any arbitrary data that would make it possible and not effect the end result).
- **optimizeColors:** All this method does is convert HEX colors to their shorthand variant whenever possible.
- **vendorPrefix:** This method adds vendor prefix to properties (and values whenever needed) to maximize browser compatibility.

Every method logs every change they make which you could retrieve VIA the `getModified()` method. You could also clear the log VIA the `clearModified()` method between optimizing methods if you want to see what each method does without the logs from other methods cluttering up the log.

The line numbers of every logged modification are relative to the original parsed CSS file and not the rendered CSS.

For a more comprehensive optimization example, you may check out the **optimizeDemo.php** provided.

## Contributing
Contributions to the Webzille CSS Parser are welcome! Please ensure that you submit pull requests to the development branch.

## License
This project is licensed under the MIT License - see the LICENSE file for details.
