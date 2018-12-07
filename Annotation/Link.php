<?php

namespace Videni\Bundle\FileBundle\Annotation;

/**
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Link
{
    protected $absolute = true;

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
