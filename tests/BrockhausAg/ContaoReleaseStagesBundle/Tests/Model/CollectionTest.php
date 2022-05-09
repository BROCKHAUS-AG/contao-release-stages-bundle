<?php

/*
 * This file is part of contao-release-stages-bundle.
 *
 * (c) BROCKHAUS AG 2022 <info@brockhaus-ag.de>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/brockhaus-ag/contao-release-stages-bundle
 */
declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Tests\Model;

use BrockhausAg\ContaoReleaseStagesBundle\Model\Collection;
use Contao\TestCase\ContaoTestCase;
use Error;

/**
 * Class CollectionTest
 *
 * @package BrockhausAg\ContaoReleaseStagesBundle\Tests\Model
 */
class CollectionTest extends ContaoTestCase
{
    public function testInstantiation(): void
    {
        $arrayOfFile = self::createMock(Collection::class);
        self::assertInstanceOf(Collection::class, $arrayOfFile);
    }

    public function testAddItemsToCollection(): void
    {
        $testCollection = new TestCollection();
        $testCollection->add(new Test(0));
        $testCollection->add(new Test(1));

        $testCollection->getByIndex(0)->getA();

        for ($x = 0; $x != 2; $x++)
        {
            self::assertSame($x, $testCollection->getByIndex($x)->getA());
        }
    }

    public function testRemoveItemFromItems(): void
    {
        $testCollection = new TestCollection();
        $testCollection->add(new Test(0));
        $testCollection->add(new Test(1));

        $testCollection->remove(0);

        self::assertSame(1, $testCollection->getLength());
        self::assertSame(1, $testCollection->getByIndex(0)->getA());
    }

    public function testGetItems(): void
    {
        $testCollection = new TestCollection();
        $testCollection->add(new Test(0));
        $testCollection->add(new Test(1));

        for ($x = 0; $x != count($testCollection->get()); $x++)
        {
            self::assertSame($x, $testCollection->getByIndex($x)->getA());
        }
    }

    public function testGetItemAtIndex(): void
    {
        $testCollection = new TestCollection();
        $testCollection->add(new Test(0));
        $testCollection->add(new Test(1));

        self::assertSame(1, $testCollection->getByIndex(1)->getA());
    }

    public function testGetLengthOfCollection(): void
    {
        $testCollection = new TestCollection();
        $testCollection->add(new Test(0));
        $testCollection->add(new Test(1));

        self::assertSame(2, $testCollection->getLength());
    }

    public function testRemoveFromEmptyList(): void
    {
        $testCollection = new TestCollection();

        try {
            $testCollection->remove(0);
        }catch (Error $e) {
            self::assertNotNull($e);
            return;
        }
        self::fail("Exception test should break");
    }

    public function testGetItemAtIndexWhichNotExistsShouldThrowException(): void
    {
        $testCollection = new TestCollection();

        try {
            $testCollection->getByIndex(0);
        }catch (Error $e) {
            self::assertNotNull($e);
            return;
        }
        self::fail("Exception test should break");
    }
}

class Test
{
    private int $a;

    public function __construct(int $a)
    {
        $this->a = $a;
    }

    public function getA(): int
    {
        return $this->a;
    }

    public function setA(int $a): void
    {
        $this->a = $a;
    }
}

/**
 * @extends Collection<Test>
 */
class TestCollection extends Collection
{
}
