<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config;

use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Mapper;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Ssh;
use stdClass;

class SshMapper extends Mapper
{
    public function map(stdClass $data): Ssh
    {
        return new Ssh(
            $data->port,
            $data->username,
            $data->password
        );
    }
}
