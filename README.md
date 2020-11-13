# Data transfer object for arrays
Zero-dependency library to convert associative arrays to an object model

## Installation

You can install the package via composer:

```bash
composer require amorphine/data-transfer-object
```

## Purpose

The goal of this package is to give a convenient declared way to manipulate data extracted from associative arrays.
Advantages of object structure:

- Type safety (as long as PHP makes it possible)
- Statically analyze and auto completion.
- Scalar type casting

Let's look at the example of a JSON API call:

```php
$post = $api->get('posts', 1); 

[
    'title' => '…',
    'body' => '…',
    'author' => '["id" => 1, ...]',
]
```

Working with this array is difficult, as we'll always have to refer to the documentation to know what's exactly in it. 
This package allows you to create data transfer object definitions, classes, which will represent the data in a structured way.

We did our best to keep the syntax and overhead as little as possible:

```php
class PostData extends DataTransferObject
{
    /** @var string */
    public $title;
    
    /** @var string */
    public $body;
    
    /** @var AnotherDataTransferObjectImplementation */
    public $author;
}
```

An object of `PostData` can from now on be constructed like so:

```php
$postData = new PostData([
    'title' => '…',
    'body' => '…',
    'author' => '…',
]);
```

Now you can use this data in a structured way:

```php
$postData->title;
$postData->body;
$postData->author;
```

By adding doc blocks to our properties, their values will be validated against the given type; 
and a `TypeError` will be thrown if the value doesn't comply with the given type.

Here are the possible ways of declaring types:

```php
use SomeNameSpace\Author;

class PostData extends DataTransferObject
{
    /**
     * Built in types: 
     *
     * @var string 
     */
    public $property;
    
    /**
     * Classes with their FQCN: 
     *
     * @var Author
     */
    public $property;
    
    /**
     * Lists of types: 
     *
     * @var Author[]
     */
    public $property;
    
    /**
     * Iterator of types: 
     *
     * @var iterator<Author>
     */
    public $property;
    
    /**
     * Union types: 
     *
     * @var string|int
     */
    public $property;
    
    /**
     * Nullable types: 
     *
     * @var string|null
     */
    public $property;
    
    /**
     * Mixed types: 
     *
     * @var mixed|null
     */
    public $property;
    
    /**
     * Any iterator : 
     *
     * @var iterator
     */
    public $property;
    
    /**
     * No type, which allows everything
     */
    public $property;
    
    /** 
     * PHP types declaration supported
     */
    public ?int $property;

}
```

PHP 7.4 typed properties are the source of types like PHP doc is. The compatibility of declared types is upon to the developer. Feel free to use one of them, both or none at all.

### Automatic casting of nested DTOs

If you've got nested DTO fields, data passed to the parent DTO will automatically be cast.

```php
class PostData extends DataTransferObject
{
    /** @var AuthorData */
    public $author;
}
```

`PostData` can now be constructed like so:

```php
$postData = new PostData([
    'author' => [
        'name' => 'Foo',
    ],
]);
```

### Automatic casting of nested array DTOs

Similarly to above, nested array DTOs will automatically be cast.

```php
class TagData extends DataTransferObject
{
    /** @var string */
   public $name;
}

class PostData extends DataTransferObject
{
    /** @var TagData[] */
   public $tags;
}
```

`PostData` will automatically construct tags like such:

```php
$postData = new PostData([
    'tags' => [
        ['name' => 'foo'],
        ['name' => 'bar']
    ]
]);
```
### Exception handling

Beside property type validation, you can also be certain that the data transfer object in its whole is always valid.
On constructing a data transfer object, we'll validate whether all required (non-nullable) properties are set. 
If not, a `Amorphine\DataTransferObject\Exceptions\DataTransferObjectError` will be thrown.

Likewise, if you're trying to set non-defined properties, you'll get a `DataTransferObjectError`.

### Testing

``` bash
composer test
```

### Limitations
- Opcache: types and data sources are resolved through PHP comments so ensure `opcache.save_comments` equals `1`

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Powered by
- [Data Transfer Object by Spatie](https://spatie.be/open-source) - special thanks to them for some patterns, pieces of code and Readme structure
- [David Grudl (nette/di)](https://davidgrudl.com/) - thanks for class import solutions based on tokens

## See also
- [Data Transfer Object by Spatie](https://github.com/spatie/data-transfer-object) - mature and popular solution with rich functionality
