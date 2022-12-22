# php-contains-data
[![Build](https://github.com/jessegall/php-contains-data/actions/workflows/php.yml/badge.svg)](https://github.com/jessegall/php-contains-data/actions/workflows/php.yml)
[![codecov](https://codecov.io/github/jessegall/php-contains-data/branch/main/graph/badge.svg?token=06271AGB2I)](https://codecov.io/github/jessegall/php-contains-data)

The ContainsData trait provides a convenient way to manage data within an array in PHP, making it easy to access, modify, and manipulate the data using dot notation. 
The trait also allows sharing a container instance between objects, allowing multiple objects to access and modify the same data.

## Table of contents

- [Installation](#installation)
- [What can it do?](#what-can-it-do)
- [Usage](#usage)
- [Sharing a container between instances](#sharing-a-container-between-instances)

## Installation

```
composer require jessegall/contains-data
```

## What can it do?

The ContainsData trait provides a set of methods for managing data within an array in PHP.

The trait provides the following methods:

The `container()` method returns a reference to the container of the instance, which holds the data. If an argument is provided, the reference of the container property is updated to point to the provided array.

The `get()` method retrieves a value from the container using dot notation to traverse the array. If the provided key does not exist in the container, the method returns the value provided as the default argument.

The `getAsReference()` method works similarly to the `get()` method, but instead of returning the value directly, it returns a reference to the value. This allows the caller to modify the value directly in the container. If the provided key does not exist in the container, the method throws a `ReferenceMissingException` exception.

The `set()` method sets a value in the container using dot notation to traverse the array. If any intermediate keys in the provided key do not exist, they are created as empty arrays. The method returns the entire data container after the value has been set.

The `setAsReference()` method works similarly to the `set()` method, but instead of setting the value directly, it sets a reference to the value. This allows the caller to modify the value directly in the container.

The `has()` method checks if a key exists in the container using dot notation to traverse the array. If any intermediate keys in the provided key do not exist, the method returns `false`. If the key exists, the method returns `true`.

The `map()` method applies a callback function to a value within the container and returns the result. If the provided key points to an array, the callback is applied to each item in the array and an array of results is returned. If the `$replace` argument is `true`, the original value in the container is replaced with the result of the callback.

The `merge()` method can be used to merge additional data into the container. It allows the data in the container to be easily extended or updated with new data.

The `clear()` removes all items from the container array, except the items specified in the $except argument.

## Usage

```php
use JesseGall\ContainsData\ContainsData;

$data = new class {
    use ContainsData;
};

// Set a value in the container using dot notation
$data->set('foo.bar', 'baz');

// Get a value from the container using dot notation
$value = $data->get('foo.bar'); // "baz"

// Check if a key exists in the container
if ($data->has('foo.bar')) {
    // ...
}

// Map the values in an array within the container
$mappedValues = $data->map('foo.bar', function ($value) {
    return strtoupper($value);
}); // ["BAZ"]

// Replace the original value with the result of the callback
$data->map('foo.bar', function ($value) {
    return strtoupper($value);
}, true);

// Merge additional data into the container
$data->merge(['foo' => ['qux' => 'quux'], 'corge' => 'grault']);

// Clear all items from the container
$data->clear();
```

## Sharing a container between instances

Sharing a container instance between objects allows multiple objects to access and modify the same data. This can be useful in situations where multiple objects need to share information and maintain a consistent state.

To share a container instance, the reference of the container array must be passed to the `container()` method of each object that should have access to the shared data.

Once the container instance has been shared, any modifications to the data made through one of the objects will be reflected in the other objects as well, since they all reference the same array instance. This allows the objects to maintain a consistent state and share information.

Here is an example that demonstrates how to share a container instance between objects
```php
use JesseGall\ContainsData\ContainsData;

class DataContainer
{
    use ContainsData;
}

// Create a data container
$data = ['foo' => 'bar'];

// Create an instance of the DataContainer class
$container1 = new DataContainer();

// Set the container for the object
$container1->container($data);

// Create another instance of the DataContainer class
$container2 = new DataContainer();

// Set the container for the second object to the same container instance
$container2->container($data);

// Modify the value in the container using one of the objects
$container1->set('foo', 'baz');

// Access the modified value using the other object
echo $container2->get('foo'); // "baz"
````
In this example, the `$data` array is shared between the two objects that use the `ContainsData` trait by passing the reference of the `$data` array to the `container()` method of each object. Any modifications to the `$data` array made through one of the objects will be reflected in the other object as well, since they both reference the same array instance.
