
# sdn/test-simple-car-repository

Simple car in-memory repository.

## Install
```bash
composer require sdn/test-simple-car-repository
```

## Usage
```php
use GrftTestSimpleCarRepository\InMemorySimpleCarRepository;
use GrftTestSimpleCar\SimpleCar;

$repository = new InMemorySimpleCarRepository();

// Get all cars (pre-seeded with 5 sample cars)
$cars = $repository->getAll();

// Add a new car
$car = new SimpleCar('BMW', 'M3', 2023);
$repository->add($car);

// Get by id
$found = $repository->get($car->getId());

// Update
$repository->update($car);

// Delete
$repository->delete($car->getId());
```

## License

MIT
