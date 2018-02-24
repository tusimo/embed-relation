embed-relation
[![Latest Stable Version](http://img.shields.io/github/release/tusimo/embed-relation.svg)](https://packagist.org/packages/tusimo/embed-relation) [![Total Downloads](http://img.shields.io/packagist/dm/tusimo/embed-relation.svg)](https://packagist.org/packages/tusimo/embed-relation) 
==================
add a new relation is missing from [Laravel](https://laravel.com/)'s ORM. embedsMany extends [Eloquent](https://laravel.com/docs/master/eloquent) ORM .


## Installation

Either [PHP](https://php.net) 5.6+ is required.

To get the latest version of embedsMany, simply require the project using [Composer](https://getcomposer.org):

```bash
$ composer require tusimo/embed-relation
```

Instead, you may of course manually update your require block and run `composer update` if you so choose:

```json
{
    "require": {
        "tusimo/embed-relation": "^0.1"
    }
}
```

## Usage

Within your eloquent model class add following line

```php
class User extends Model {
    use \Tusimo\Eloquent\Traits\EmbedsRelation;
    ...
}
```

## Example:
Consider User has several favorite books and the book_ids just store in the user table as book_ids column.
We want this column can to load use relations.
So we can do it like this.
We user table just like these.

| id     | username  | book_ids |
|:----- :|:---------:|:--------:|
| 1      | tusimo    |   1,2    |
| 2      | alan      |   4,3,2  |
| 3      | lily      |   6,4,8  |


```php
class User extends Model {
    use \Tusimo\Eloquent\Traits\EmbedsRelation;

    public function books () {
        return $this->embedsMany(Book::class);
    }
}
```

If we want to get the books so we can use`$user->books`.
For now I just finished get relation data.
Next I will do the save thing And the reverse relation.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

[link-contributors]: ../../contributors
