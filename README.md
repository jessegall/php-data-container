# php-has-array-data

A package that provides a trait to get and set items in an array using dot notation

```
composer require jessegall/has-array-data
```

## Usage

```php
$subject = new class {
    use HasArrayData;

    public function __construct()
    {
        $this->data = [
            'one' => [
                'two' => [
                    'three' => 'value'
                ]
            ],
        ];
    }
    
    ...
}

// Get data
$subject->get('one.two.three');
$subject->get('one.two.three.missing', 'default value');

// Set data
$subject->set('one.two.three', 'new value');
$subject->set($overwrite = ['some' => ['new' => 'array']]);

// Check if item exists
$subject->has('one.two.three') // true;
$subject->has('one.two.three.missing') // false;
```