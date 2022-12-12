# php-contains-data

The ContainsData trait provides a convenient way to manage data within an array in PHP, making it easy to access, modify, and manipulate the data using dot notation.

## Installation

```
composer require jessegall/contains-data
```

## What can it do?

The ContainsData trait provides a set of methods for managing data within an array in PHP.

The trait provides the following methods:

- container(): This method returns a reference to the data container. It can be used to access or modify the container property of the class.
- get(): This method retrieves the value of an item in the data container using dot notation to specify the key. For example, if the data container contains an array with a key user, you could use get('user') to retrieve the value of that key.
- getAsReference(): This method is similar to get(), but it returns a reference to the item in the data container instead of a copy of the value. This can be useful if you want to modify the value of an item in the container and have those changes reflected in the container itself.
- set(): This method sets the value of an item in the data container using dot notation to specify the key. For example, if you want to set the value of a key user in the data container, you could use set('user', $value) to set the value of that key.
- setAsReference(): This method is similar to set(), but it sets the value of an item in the data container as a reference to the value passed to the method. This can be useful if you want to modify the value of the item in the container and have those changes reflected in the value itself.
- has(): This method checks if an item exists in the data container using dot notation to specify the key. For example, if you want to check if the key user exists in the data container, you could use has('user') to check for its existence.
- map(): This method maps the value of an item in the data container to the result of a callback function. For example, if you want to map the values of an array in the data container to the result of a callback function, you could use map('array_key', $callback) to apply the callback function to each item in the array.



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

### Sharing a container between instances

This can be useful in situations where multiple instances of a class need to access and modify the same data, such as when implementing a cache or when working with a shared database connection. 


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
