<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config;

use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Mapper;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Ftp;
use stdClass;

class FtpMapper extends Mapper
{
    public function map(stdClass $data): Ftp
    {
        return new Ftp(
            $data->port,
            $data->username,
            $data->password,
            $data->ssl
        );
    }
}
