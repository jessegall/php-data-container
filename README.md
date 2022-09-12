# php-contains-data

A trait that provides a convenient solution for objects to use an array as their data source. 
As well as offering the possibility to share the same data between instances.

## Installation

```
composer require jessegall/contains-data
```

## Usage

### Basic

```php
class Example {
    use ContainsData;

    public function __construct(array $data)
    {
        $this->set($data);
    }
    
    ...
}

$example = new Example([
    'one' => [
        'two' => [
            'three' => 'value'
        ]
    ],
    'list' => [1, 2, 3],
]);

// Get data
$example->get('one.two.three') // Returns 'value'; 
$example->get('one.two.three.missing', 'default value') // Returns 'default value';

// Set data
$example->set('one.two.three', 'new value'); // Replaces only one item
$example->set(['some' => ['new' => 'array']]); // Replaces the whole data container

// Check if item exists
$example->has('one.two.three') // true;
$example->has('one.two.three.missing') // false;

// Map data
$example->map('list', fn(int $value) => $value * 2); // Returns [2, 4, 6]
```

### Shared container reference

```php
class DataContainer {
    use ContainsData;
    
    public function getProperty(): string 
    {
        return $this->get('property')
    }
    
    public function setProperty(string $value): void
    {
        $this->set('property', $value);
    }
    
    ...
}

$instanceOne = new DataContainer();
$instanceTwo = new DataContainer();

$array = [ 'property' => 'value' ];

// Use $array as the container
$instanceOne->container($array);  
$instanceTwo->container($array); 

# Changing any value in the array will also change the content in the DataContainer instances

$array['property'] = 'new value'; 
$instanceOne->getProperty(); // 'new value'
$instanceTwo->getProperty(); // 'new value'

# works the other way around too!

$instanceOne->setProperty('another new value'); 
$instanceTwo->getProperty(); // 'another new value'
$array['property'] // 'another new value'
````
