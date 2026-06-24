<?php

declare(strict_types=1);

namespace GrftTestSimpleCarRepository;

use GrftTestSimpleCar\SimpleCar;

/**
 * Abstract contract for a repository that manages SimpleCar instances.
 *
 * The contract is split into three groups: CRUD operations on a single car,
 * queries that return cars or primitive arrays, and bulk operations driven by
 * primitive-array input.
 */
interface SimpleCarRepositoryInterface
{
    /**
     * Adds a new car to the repository.
     *
     * @param SimpleCar $car Car instance to persist; must not be null.
     * @throws \InvalidArgumentException If car is null or the id already exists.
     */
    public function add(SimpleCar $car): void;

    /**
     * Add a batch of cars to the repository in iteration order.
     *
     * @param SimpleCar[] $cars Sequence of cars to insert; null is rejected.
     * @throws \InvalidArgumentException If cars is null or any element collides with an existing entry.
     */
    public function addRange(array $cars): void;

    /**
     * Returns the car identified by id if it exists.
     *
     * @param string $id Stable car identifier; must not be empty or whitespace.
     * @return SimpleCar|null The matching car, or null when the id is unknown.
     * @throws \InvalidArgumentException If id is null or whitespace-only.
     */
    public function get(string $id): ?SimpleCar;

    /**
     * Returns every car currently stored in the repository.
     *
     * @return SimpleCar[] Snapshot of all cars.
     */
    public function getAll(): array;

    /**
     * Replaces an existing car with the supplied instance.
     *
     * @param SimpleCar $car Car carrying the identifier of the entry to replace.
     * @return bool True when an existing car was replaced, false when not found.
     * @throws \InvalidArgumentException If car is null.
     * @see add()
     */
    public function update(SimpleCar $car): bool;

    /**
     * Removes the car identified by id.
     *
     * @param string $id Stable car identifier; must not be empty or whitespace.
     * @return bool True when a car was removed, false when the id was not found.
     * @throws \InvalidArgumentException If id is null or whitespace-only.
     */
    public function delete(string $id): bool;

    /**
     * Finds every car whose manufacturer matches make (case-insensitive).
     *
     * @param string $make Manufacturer name to match; must not be empty.
     * @return SimpleCar[] Matching cars.
     * @throws \InvalidArgumentException If make is null or whitespace-only.
     */
    public function findByMake(string $make): array;

    /**
     * Finds every car whose production year falls in [minYear, maxYear].
     *
     * @param int $minYear Inclusive lower bound on the production year.
     * @param int $maxYear Inclusive upper bound on the production year.
     * @return SimpleCar[] Cars within the requested range.
     */
    public function findByYearRange(int $minYear, int $maxYear): array;

    /**
     * Returns every distinct production year present in the repository.
     *
     * @return int[] Sorted list of unique production years.
     * @see findByYearRange()
     */
    public function getYears(): array;

    /**
     * Returns every distinct manufacturer present in the repository.
     *
     * @return string[] Sorted list of unique manufacturer names.
     */
    public function getMakes(): array;

    /**
     * Removes every car whose id is listed in ids.
     *
     * Missing ids are silently skipped; the return value reports how many entries were actually removed.
     *
     * @param string[] $ids Identifiers to remove; null is rejected.
     * @return int Number of cars actually removed from the repository.
     * @throws \InvalidArgumentException If ids is null.
     * @author graftcode
     * @since 0.2.1
     */
    public function deleteByIds(array $ids): int;
}
