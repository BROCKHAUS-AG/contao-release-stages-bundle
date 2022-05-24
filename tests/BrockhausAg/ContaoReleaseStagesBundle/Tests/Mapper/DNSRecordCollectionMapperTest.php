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

namespace BrockhausAg\ContaoReleaseStagesBundle\Tests\Mapper;

use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config\DNSRecordCollectionMapper;
use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config\DatabaseMapper;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\DNSRecordCollection;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\DNSRecord;
use Contao\TestCase\ContaoTestCase;

/**
 * Class DNSRecordCollectionMapperTest
 *
 * @package BrockhausAg\ContaoReleaseStagesBundle\Tests\Mapper
 */
class DNSRecordCollectionMapperTest extends ContaoTestCase
{
    public function testInstantiation(): void
    {
        self::assertInstanceOf(DatabaseMapper::class, new DatabaseMapper());
    }

    public function testMap(): void
    {
        $input = '[
            {
              "alias": "example-site",
              "dns": "www.example-site.de"
            },
            {
              "alias": "example-site-better",
              "dns": "www.example-site-better.de"
            }
        ]';
        $expected = new DNSRecordCollection();
        $expected->add(new DNSRecord("example-site", "www.example-site.de"));
        $expected->add(new DNSRecord("example-site-better", "www.example-site-better.de"));
        $mapper = new DNSRecordCollectionMapper();

        $actual = $mapper->mapArray(json_decode($input));

        for ($x = 0; $x != $expected->getLength(); $x++) {
            self::assertSame($expected->getByIndex($x)->getAlias(), $actual->getByIndex($x)->getAlias());
            self::assertSame($expected->getByIndex($x)->getDns(), $actual->getByIndex($x)->getDns());
        }
    }
}
