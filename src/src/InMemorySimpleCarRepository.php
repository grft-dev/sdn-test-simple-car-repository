<?php

declare(strict_types=1);

namespace GrftTestSimpleCarRepository;

use GrftTestSimpleCar\SimpleCar;
use InvalidArgumentException;

/**
 * An in-memory repository that stores SimpleCar instances using a string identifier.
 */
final class InMemorySimpleCarRepository implements SimpleCarRepositoryInterface
{
    // ── static members ──────────────────────────────────────────

    public const DEFAULT_CAPACITY = 100;

    public static int $totalInstancesCreated = 0;

    public static function createWithSampleData(): self
    {
        return new self();
    }

    // ── instance state ──────────────────────────────────────────

    /** @var array<string, SimpleCar> */
    private array $store = [];

    public function __construct()
    {
        self::$totalInstancesCreated++;

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

    // ── CRUD ────────────────────────────────────────────────────

    public function add(SimpleCar $car): void
    {
        if (isset($this->store[$car->getId()])) {
            throw new InvalidArgumentException("SimpleCar with Id {$car->getId()} already exists.");
        }
        $this->store[$car->getId()] = $car;
    }

    /** @param SimpleCar[] $cars */
    public function addRange(array $cars): void
    {
        foreach ($cars as $car) {
            $this->add($car);
        }
    }

    public function get(string $id): ?SimpleCar
    {
        if (trim($id) === '') {
            throw new InvalidArgumentException('id cannot be null or whitespace');
        }

        return $this->store[$id] ?? null;
    }

    /** @return SimpleCar[] */
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

    // ── queries returning complex types ─────────────────────────

    /** @return SimpleCar[] */
    public function findByMake(string $make): array
    {
        if (trim($make) === '') {
            throw new InvalidArgumentException('make cannot be null or whitespace');
        }
        $lower = strtolower($make);

        return array_values(array_filter(
            $this->store,
            static fn (SimpleCar $c) => strtolower($c->getMake()) === $lower
        ));
    }

    /** @return SimpleCar[] */
    public function findByYearRange(int $minYear, int $maxYear): array
    {
        return array_values(array_filter(
            $this->store,
            static fn (SimpleCar $c) => $c->getYear() >= $minYear && $c->getYear() <= $maxYear
        ));
    }

    // ── queries returning primitive arrays ──────────────────────

    /** @return int[] */
    public function getYears(): array
    {
        $years = array_unique(array_map(
            static fn (SimpleCar $c) => $c->getYear(),
            array_values($this->store)
        ));
        sort($years);

        return array_values($years);
    }

    /** @return string[] */
    public function getMakes(): array
    {
        $makes = array_unique(array_map(
            static fn (SimpleCar $c) => $c->getMake(),
            array_values($this->store)
        ));
        sort($makes);

        return array_values($makes);
    }

    // ── bulk operations with primitive-array input ──────────────

    /** @param string[] $ids */
    public function deleteByIds(array $ids): int
    {
        $count = 0;
        foreach ($ids as $id) {
            if (isset($this->store[$id])) {
                unset($this->store[$id]);
                $count++;
            }
        }

        return $count;
    }
}
