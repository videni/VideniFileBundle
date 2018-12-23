<?php

namespace Videni\Bundle\FileBundle\Serializer;

use Vich\UploaderBundle\Metadata\MetadataReader;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Videni\Bundle\FileBundle\Metadata\MetadataReader as FileMetaReader;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Prepend host and path for entity file properties
 */
class FileHelper
{
    private $uploaderHelper;
    private $metadataReader;
    private $fileMetadataReader;

    private $host;

     /**
     * @var PropertyAccessor
     */
    private static $propertyAccessor;

    public function __construct(
        UploaderHelper $uploaderHelper,
        MetadataReader $metadataReader,
        FileMetaReader $fileMetadataReader,
        $host
    ) {
        $this->uploaderHelper = $uploaderHelper;
        $this->metadataReader = $metadataReader;
        $this->fileMetadataReader = $fileMetadataReader;
        $this->host = $host;
    }

    public function prependHost($object)
    {
        $propertyAccessor = $this->getPropertyAccessor();

        $fields = $this->fileMetadataReader->getLinks(get_class($object));
        foreach ($fields as $name => $field) {
            if ($field['absolute']) {
                $oldValue = $propertyAccessor->getValue($object, $name);
                if(empty($oldValue)) {
                    continue;
                }
                $propertyAccessor->setValue($object, $name, $this->host.$oldValue);
            }
        }
    }

    public function isFileAnnotatedEntity($object)
    {
        return $this->fileMetadataReader->hasFile(get_class($object));
    }

    public function prependPrefix($object)
    {
        $data = [];

        $fields = $this->metadataReader->getUploadableFields(get_class($object));

        foreach ($fields as $name => $field) {
            $newValue = $this->uploaderHelper->asset($object, $name);  //etc, test.png => media/test.png

            $this->getPropertyAccessor()->setValue($object, $field['fileNameProperty'], $newValue);
        }
    }

    public function isUploadableEntity($object)
    {
        return $this->metadataReader->isUploadable(get_class($object));
    }

    /**
     * @return PropertyAccessor
     */
    private static function getPropertyAccessor()
    {
        if (!self::$propertyAccessor) {
            self::$propertyAccessor = PropertyAccess::createPropertyAccessor();
        }

        return self::$propertyAccessor;
    }
}
