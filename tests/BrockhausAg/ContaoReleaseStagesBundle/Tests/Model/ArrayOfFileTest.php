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


use BrockhausAg\ContaoReleaseStagesBundle\Model\ArrayOfFile;
use BrockhausAg\ContaoReleaseStagesBundle\Model\File;
use Contao\TestCase\ContaoTestCase;
use ReflectionClass;

/**
 * Class ArrayOfFileTest
 *
 * @package BrockhausAg\ContaoReleaseStagesBundle\Tests\Model
 */
class ArrayOfFileTest extends ContaoTestCase
{
    public function testInstantiation(): void
    {
        $arrayOfFile = self::createMock(ArrayOfFile::class);
        self::assertInstanceOf(ArrayOfFile::class, $arrayOfFile);
    }

    public function testGet(): void
    {
        $expected = array(
            new File(0, "a", "b"),
            new File(1, "c", "d")
        );

        $arrayOfFile = new ArrayOfFile();
        $reflection = new ReflectionClass($arrayOfFile);
        $reflection_property = $reflection->getProperty("files");
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($arrayOfFile, $expected);

        $actual = $arrayOfFile->get();

        for ($x = 0; $x != count($expected); $x++) {
            self::assertSame($expected[$x]->getLastModifiedTime(), $actual[$x]->getLastModifiedTime());
            self::assertSame($expected[$x]->getPath(), $actual[$x]->getPath());
            self::assertSame($expected[$x]->getProdPath(), $actual[$x]->getProdPath());
        }
    }

    public function testGetByIndex(): void
    {
        $expected = array(
            new File(0, "a", "b"),
            new File(1, "c", "d")
        );

        $arrayOfFile = new ArrayOfFile();
        $reflection = new ReflectionClass($arrayOfFile);
        $reflection_property = $reflection->getProperty("files");
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($arrayOfFile, $expected);

        $actual = $arrayOfFile->getByIndex(1);

        self::assertSame($expected[1]->getLastModifiedTime(), $actual->getLastModifiedTime());
        self::assertSame($expected[1]->getPath(), $actual->getPath());
        self::assertSame($expected[1]->getProdPath(), $actual->getProdPath());
    }

    public function testAdd(): void
    {
        $expected = array(
            new File(0, "a", "b"),
            new File(1, "c", "d")
        );

        $arrayOfFile = new ArrayOfFile();
        $arrayOfFile->add($expected[0]);
        $arrayOfFile->add($expected[1]);

        $reflection = new ReflectionClass($arrayOfFile);
        $reflection_property = $reflection->getProperty("files");
        $reflection_property->setAccessible(true);

        $actual = $reflection_property->getValue($arrayOfFile);

        for ($x = 0; $x != count($expected); $x++) {
            self::assertSame($expected[$x]->getLastModifiedTime(), $actual[$x]->getLastModifiedTime());
            self::assertSame($expected[$x]->getPath(), $actual[$x]->getPath());
            self::assertSame($expected[$x]->getProdPath(), $actual[$x]->getProdPath());
        }
    }
}
