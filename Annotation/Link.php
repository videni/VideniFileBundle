<?php

namespace App\Bundle\FileBundle\Annotation;

/**
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Link
{
    protected $absolute = false;

    public function __construct(array $options)
    {
        if (isset($options['absolute'])) {
            $this->absolute = (bool)$options['absolute'];
        }
    }

    public function isAbsolute()
    {
        return $this->absolute;
    }
}