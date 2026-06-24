<?php

declare(strict_types=1);

namespace GrftTestSimpleCarRepository;

use GrftTestSimpleCar\SimpleCar;
use InvalidArgumentException;

/**
 * An in-memory repository that stores SimpleCar instances using a string identifier.
 *
 * Implements {@see SimpleCarRepositoryInterface} and covers:
 *   primitive fields, static members, constructors, primitive-array I/O,
 *   and methods that accept / return complex types from the same library.
 */
final class InMemorySimpleCarRepository implements SimpleCarRepositoryInterface
{
    // ── static members ──────────────────────────────────────────

    /** Default capacity hint for the repository. */
    public const DEFAULT_CAPACITY = 100;

    /** Total number of repository instances created so far. */
    public static int $totalInstancesCreated = 0;

    /**
     * Returns a new repository pre-loaded with sample data.
     *
     * @return self Repository containing the default sample cars used across graft tests.
     * @example
     *   $repo = InMemorySimpleCarRepository::createWithSampleData();
     *   count($repo->getAll()); // 5
     */
    public static function createWithSampleData(): self
    {
        return new self();
    }

    // ── instance state ──────────────────────────────────────────

    /** @var array<string, SimpleCar> */
    private array $store = [];

    /**
     * Initializes the repository and seeds it with five sample cars.
     *
     * The samples cover a mix of makes and production years so that range and make-based queries
     * return non-trivial results out of the box.
     *
     * @author graftcode
     * @since 0.2.1
     */
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

    /**
     * Adds a new car to the repository.
     *
     * The check for duplicates and the insert happen atomically, so concurrent calls cannot create
     * two entries for the same id.
     *
     * @param SimpleCar $car Car to add; must not be null.
     * @throws InvalidArgumentException If car is null or a car with the same id already exists.
     */
    public function add(SimpleCar $car): void
    {
        if (isset($this->store[$car->getId()])) {
            throw new InvalidArgumentException("SimpleCar with Id {$car->getId()} already exists.");
        }
        $this->store[$car->getId()] = $car;
    }

    /**
     * Adds a range of simple cars to the repository.
     *
     * @param SimpleCar[] $cars Array of cars to add.
     * @throws InvalidArgumentException If any car has an id that already exists.
     */
    public function addRange(array $cars): void
    {
        foreach ($cars as $car) {
            $this->add($car);
        }
    }

    /**
     * Gets a simple car by its string identifier.
     *
     * @param string $id The simple car identifier.
     * @return SimpleCar|null The matching SimpleCar if found; otherwise null.
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
     * Returns all simple cars in the repository.
     *
     * @return SimpleCar[] A list of SimpleCar.
     */
    public function getAll(): array
    {
        return array_values($this->store);
    }

    /**
     * Updates an existing simple car in the repository.
     *
     * @param SimpleCar $car The simple car with updated data.
     * @return bool true if the car was updated; otherwise false (not found).
     */
    public function update(SimpleCar $car): bool
    {
        if (!isset($this->store[$car->getId()])) {
            return false;
        }
        $this->store[$car->getId()] = $car;

        return true;
    }

    /**
     * Deletes a simple car by its string identifier.
     *
     * @param string $id The simple car identifier.
     * @return bool true if the car was removed; otherwise false.
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

    // ── queries returning complex types ─────────────────────────

    /**
     * Finds every car whose manufacturer matches make (case-insensitive).
     *
     * @param string $make Manufacturer name to match; must not be empty.
     * @return SimpleCar[] Matching cars; empty array when none match.
     * @throws InvalidArgumentException If make is null or whitespace-only.
     */
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

    /**
     * Finds all simple cars whose production year falls within the inclusive range.
     *
     * @param int $minYear Minimum year (inclusive).
     * @param int $maxYear Maximum year (inclusive).
     * @return SimpleCar[] Cars within the given year range.
     */
    public function findByYearRange(int $minYear, int $maxYear): array
    {
        return array_values(array_filter(
            $this->store,
            static fn (SimpleCar $c) => $c->getYear() >= $minYear && $c->getYear() <= $maxYear
        ));
    }

    // ── queries returning primitive arrays ──────────────────────

    /**
     * Returns all distinct production years currently stored, ascending.
     *
     * @return int[] Sorted list of distinct years.
     */
    public function getYears(): array
    {
        $years = array_unique(array_map(
            static fn (SimpleCar $c) => $c->getYear(),
            array_values($this->store)
        ));
        sort($years);

        return array_values($years);
    }

    /**
     * Returns every distinct manufacturer present in the repository.
     *
     * @deprecated Prefer querying makes from a domain-specific service in new code.
     * @return string[] Sorted list of unique manufacturer names.
     */
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

    /**
     * Removes every car whose id is listed in ids.
     *
     * Missing ids are silently skipped; the return value reports how many entries were actually removed.
     *
     * @param string[] $ids Identifiers to remove; null is rejected.
     * @return int Number of cars actually removed.
     * @throws InvalidArgumentException If ids is null.
     * @author graftcode
     * @since 0.2.1
     */
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
