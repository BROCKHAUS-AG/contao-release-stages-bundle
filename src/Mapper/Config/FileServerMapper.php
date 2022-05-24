<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config;

use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Mapper;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\FileServer;
use stdClass;

class FileServerMapper extends Mapper
{
    public function map(stdClass $data): FileServer
    {
        $ftpMapper = new FtpMapper();
        $ftp = $ftpMapper->map($data->ftp);

        $sshMapper = new SshMapper();
        $ssh = $sshMapper->map($data->ssh);

       return new FileServer($data->server, $data->path, $ftp, $ssh);
    }
}
