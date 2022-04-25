<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Model;

/**
 * @template T
 */
abstract class Collection
{
    /**
     * @var array
     */
    private array $items;

    /**
     * @param T $item
     * @return void
     */
    public function add($item): void
    {
        $this->items[] = $item;
    }

    /**
     * @param int $index
     * @return void
     */
    public function remove(int $index): void
    {
        array_splice($this->items, $index, 1);
        //unset($this->items[$index]);
    }

    /**
     * @return array
     */
    public function get() : array
    {
        return $this->items;
    }

    /**
     * @param int $index
     * @return T
     */
    public function getByIndex(int $index)
    {
        return $this->items[$index];
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return count($this->items);
    }
}
