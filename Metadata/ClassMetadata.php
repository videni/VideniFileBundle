<?php

namespace App\Bundle\FileBundle\Metadata;

use Metadata\ClassMetadata as BaseClassMetadata;

class ClassMetadata extends BaseClassMetadata
{
    public $fields = [];

    public function serialize(): string
    {
        return serialize([
            $this->fields,
            parent::serialize(),
        ]);
    }

    public function unserialize($str): void
    {
        [
            $this->fields,
            $parentStr
            ] = unserialize($str);

        parent::unserialize($parentStr);
    }
}