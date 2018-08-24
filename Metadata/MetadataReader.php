<?php

namespace App\Bundle\FileBundle\Metadata;

use Metadata\AdvancedMetadataFactoryInterface;
use App\Bundle\FileBundle\Exception\MappingNotFoundException;

/**
 * MetadataReader.
 */
class MetadataReader
{
    /**
     * @var AdvancedMetadataFactoryInterface
     */
    protected $reader;

    /**
     * Constructs a new instance of the MetadataReader.
     *
     * @param AdvancedMetadataFactoryInterface $reader The "low-level" metadata reader
     */
    public function __construct(AdvancedMetadataFactoryInterface $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Tells if the given class has file.
     *
     * @param string $class   The class name to test (FQCN)
     *
     * @return bool
     *
     * @throws MappingNotFoundException
     */
    public function hasFile(string $class): bool
    {
        $metadata = $this->reader->getMetadataForClass($class);

        return !!$metadata;
    }

    /**
     * Search for all classes that have files.
     *
     * @return array|null A list of classes that have files
     *
     * @throws \RuntimeException
     */
    public function getFileClasses(): ?array
    {
        return $this->reader->getAllClassNames();
    }

    /**
     * Attempts to read the file fields.
     *
     * @param string $class   The class name to test (FQCN)
     *
     * @return array A list of file link fields
     *
     * @throws MappingNotFoundException
     */
    public function getLinks(string $class): array
    {
        if (null === $metadata = $this->reader->getMetadataForClass($class)) {
            throw MappingNotFoundException::createNotFoundForClass($mapping ?? '', $class);
        }
        $linkFields = [];

        foreach ($metadata->classMetadata as $classMetadata) {
            $linkFields = array_merge($linkFields, $classMetadata->fields);
        }


        return $linkFields;
    }

    /**
     * Attempts to read the mapping of a specified property.
     *
     * @param string $class The class name to test (FQCN)
     * @param string $field The field
     *
     * @return mixed The field mapping
     *
     * @throws MappingNotFoundException
     */
    public function getLink(string $class, string $field)
    {
        $fieldsMetadata = $this->getLinks($class);

        return $fieldsMetadata[$field] ?? null;
    }
}
