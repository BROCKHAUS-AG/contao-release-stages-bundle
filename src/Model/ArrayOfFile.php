<?php
namespace BrockhausAg\ContaoReleaseStagesBundle\Model;

class ArrayOfFile
{
    private array $files;

    public function __construct()
    {
        $this->files = array();
    }

    public function add(File $file) : void
    {
        $this->files[] = $file;
    }

    public function get() : array
    {
        return $this->files;
    }

    public function getByIndex(int $index) : File
    {
        return $this->files[$index];
    }
}
