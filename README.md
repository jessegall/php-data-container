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

## Examples

This package is really useful for wrapping, for example:
API responses to enable autocompletion and let other developers know what data is available,
dividing big arrays in small understandable data containers, and much more...

### Api wrapper example

Wrapping api responses can greatly improve code quality and readability.
Imagine using an API with 100+ different resources.
By wrapping them you provide an easy-to-use interface for other developers to work with.

For this example we'll assume that the api returns the following data

```php
$response = [
    'order' => [
        'price' => 100,
        'currency' => 'EUR',
        'customer' => [
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'x_johnny_x@hotmail.com'
        ],
        'products' => [
            [ 'title' => 'Dark Temptation body spray', 'category' => 'hygiene', 'brand' => 'Axe' ],
            [ 'title' => 'PHP for dummies', 'category' => 'books', 'author' => 'Janet Valade' ],
            [ 'title' => 'The joy of solo sex', 'category' => 'books', 'author' => 'Harold Litten']
        ]
    ]
]
```

The classes used for wrapping the response are as follows:

```php
class Order {
    use ContainsData;
    
    public function __construct(array $data) 
    {
        $this->set($data);
    }
    
    public function getPrice(): float  { return $this->get('price'); }
    
    public function getCurrency(): string { return $this->get('currency'); }
    
    public function customer(): Customer
    {
        return new Customer($this->get('customer'));
    }
    
    /**
    * @return Product[] // Make the IDE understand the return type 
    */
    public function products(): array
    {
        return $this->map('products', fn(array $item) => new Product($item))
    }
}

class Customer {
    use ContainsData;
    
    public function __construct(array $data) 
    {
        $this->set($data);
    }
    
    public function getFirstName(): string { return $this->get('first_name'); }
    
    public function getLastName(): string { return $this->get('last_name'); }
}

class Product {
    use ContainsData;
    
    public function __construct(array $data) 
    {
        $this->set($data);
    }
    
    public function getTitle(): string {return $this->get('title'); }
    
    public function getCategory(): string { return $this->get('category'); }
    
    public function getAuthor(): ?string{ return $this->get('author'); }
    
    public function getBrand(): ?string { return $this->get('brand'); }
}
````

With the wrapper classes above we can do something like the example below.
With the added bonus that our IDE now autocompletes the data!

```php
$api = new ExampleApi();

$response = $api->getOrders($orderId);

$order = new Order($response['order']);

Mail::to($order->getCustomer(), new OrderSuccessfulMail());

foreach ($order->getProducts() as $product) {
    event(new ProductSold($product));
    
    log("Product sold: {$product->getTitle()}");
    
    ... 
}
```