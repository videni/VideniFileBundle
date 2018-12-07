<?php

namespace Videni\Bundle\FileBundle\Metadata\Driver;

use Doctrine\Common\Annotations\Reader as AnnotationReader;
use Metadata\Driver\AdvancedDriverInterface;
use Videni\Bundle\FileBundle\Annotation\File;
use Videni\Bundle\FileBundle\Annotation\Link;
use Videni\Bundle\FileBundle\Metadata\ClassMetadata;

class AnnotationDriver implements AdvancedDriverInterface
{
    protected $reader;

    public function __construct(AnnotationReader $reader)
    {
        $this->reader = $reader;
    }

    public function loadMetadataForClass(\ReflectionClass $class)
    {
        if (!$this->hasFile($class)) {
            return;
        }

        $classMetadata = new ClassMetadata($class->name);
        $classMetadata->fileResources[] = $class->getFileName();

        foreach ($class->getProperties() as $property) {
            $linkField = $this->reader->getPropertyAnnotation($property, Link::class);
            if (null === $linkField) {
                continue;
            }
            $fieldMetadata = [
                'absolute' => $linkField->isAbsolute(),
            ];

            $classMetadata->fields[$property->getName()] = $fieldMetadata;
        }

        return $classMetadata;
    }

    public function getAllClassNames()
    {
        return [];
    }

    protected function hasFile(\ReflectionClass $class)
    {
        return null !== $this->reader->getClassAnnotation($class, File::class);
    }
}
