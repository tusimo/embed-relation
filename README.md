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
And when we have a json string column as data. We support virtual column and can use cast.
And support some new cast, 'integer_array', 'string_array', 'float_array', 'bool_array'
```php
class User extends Model {
    use \Tusimo\Eloquent\Traits\EmbedsRelation;
    use \Tusimo\Eloquent\Traits\CastAttributes;
    
    protected $virtualColumnMaps = [
        'data' => [
            'address' => 'home_address',//you can rename the column
            'follower_ids'
        ],
        //'more_json_data' => [],
    ];
    
    $casts = [
        'book_ids' => 'integer_array',
        'home_address' => 'string',
        'follower_ids' => 'iinteger_array',
    ];
    ...
}
```

## Example:
Consider User has several favorite books and the book_ids just store in the user table as book_ids column.
We want this column can to load use relations.
So we can do it like this.
We have user table just like this.

| id | user_name | book_ids | data |
|----|-----------|----------|------|
| 1  | tusimo    | 1,2,3    |{"address":"NY","follower_ids":"1,3"}|
| 2  | john      | 2,4,7    |{"address":"WD","follower_ids":"3"}|
| 3  | aly       | 5        |{"address":"LA","follower_ids":"1,2"}|

and book table like this,

| id | book       |
|----|------------|
| 1  | css        |
| 2  | php        |
| 3  | javascript |
| 4  | database   |
| 5  | sql        |
| 6  | python     |
| 7  | html       |

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

Now we can access data like this .

```php
    $user->home_address = 'HA';
    $user->follower_ids = [1,2,3,4];
    $user->save();
    foreach($user->follower_ids as $followerId) {//which now is array type
        echo $followerId;
    }
    if($user->isVirtualDirty('home_address')) {
        //detect virtual column is dirty or not 
        dd($user->getVirtualDirty());
    }
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

[link-contributors]: ../../contributors
