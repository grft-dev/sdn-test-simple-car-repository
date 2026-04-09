<?php

declare(strict_types=1);

namespace GrftTestSimpleCarRepository;

use GrftTestSimpleCar\SimpleCar;

/**
 * Contract for a repository that manages SimpleCar instances.
 */
interface SimpleCarRepositoryInterface
{
    public function add(SimpleCar $car): void;

    /** @param SimpleCar[] $cars */
    public function addRange(array $cars): void;

    public function get(string $id): ?SimpleCar;

    /** @return SimpleCar[] */
    public function getAll(): array;

    public function update(SimpleCar $car): bool;
    public function delete(string $id): bool;

    /** @return SimpleCar[] */
    public function findByMake(string $make): array;

    /** @return SimpleCar[] */
    public function findByYearRange(int $minYear, int $maxYear): array;

    /** @return int[] */
    public function getYears(): array;

    /** @return string[] */
    public function getMakes(): array;

    /** @param string[] $ids */
    public function deleteByIds(array $ids): int;
}
