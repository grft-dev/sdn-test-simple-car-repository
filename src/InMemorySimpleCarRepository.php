<?php

declare(strict_types=1);

namespace GrftTestSimpleCarRepository;

use GrftTestSimpleCar\SimpleCar;
use InvalidArgumentException;

final class InMemorySimpleCarRepository
{
    /** @var array<string, SimpleCar> */
    private array $store = [];

    public function __construct()
    {
        $samples = [
            new SimpleCar('Toyota', 'Corolla', 2018, '0'),
            new SimpleCar('Tesla', 'Model 3', 2021, '1'),
            new SimpleCar('Ford', 'Mustang', 1967, '2'),
            new SimpleCar('Honda', 'Civic', 2015, '3'),
            new SimpleCar('Chevrolet', 'Camaro', 2020, '4'),
        ];

        foreach ($samples as $car) {
            $this->add($car);
        }
    }

    /**
     * @throws InvalidArgumentException If a car with the same id already exists.
     */
    public function add(SimpleCar $car): void
    {
        if (isset($this->store[$car->getId()])) {
            throw new InvalidArgumentException("SimpleCar with Id {$car->getId()} already exists.");
        }
        $this->store[$car->getId()] = $car;
    }

    /**
     * @param SimpleCar[] $cars Array of cars to add.
     */
    public function addRange(array $cars): void
    {
        foreach ($cars as $car) {
            $this->add($car);
        }
    }

    /**
     * @throws InvalidArgumentException If id is null or whitespace.
     */
    public function get(string $id): ?SimpleCar
    {
        if (trim($id) === '') {
            throw new InvalidArgumentException('id cannot be null or whitespace');
        }

        return $this->store[$id] ?? null;
    }

    /**
     * @return SimpleCar[] A list of SimpleCar.
     */
    public function getAll(): array
    {
        return array_values($this->store);
    }

    public function update(SimpleCar $car): bool
    {
        if (!isset($this->store[$car->getId()])) {
            return false;
        }
        $this->store[$car->getId()] = $car;

        return true;
    }

    /**
     * @throws InvalidArgumentException If id is null or whitespace.
     */
    public function delete(string $id): bool
    {
        if (trim($id) === '') {
            throw new InvalidArgumentException('id cannot be null or whitespace');
        }
        if (isset($this->store[$id])) {
            unset($this->store[$id]);

            return true;
        }

        return false;
    }
}
