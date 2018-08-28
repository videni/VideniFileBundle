<?php

namespace App\Bundle\FileBundle\Serializer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;
use App\Bundle\FileBundle\Metadata\MetadataReader;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * 给文件链接添加域名
 */
class FileEndpointNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    private $metadataReader;
    private $serializer;
    private $assetEndpoint;
    private $called = [];

    /**
     * @var PropertyAccessor
     */
    private static $propertyAccessor;

    public function __construct(
        MetadataReader $metadataReader,
        $assetEndpoint
    ) {
        $this->metadataReader = $metadataReader;
        $this->assetEndpoint = $assetEndpoint;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = [];
        $data = $this->addEndpoint($object);

        return  array_merge($this->serializer->normalize($object, $format, $context), $data);
    }

    protected function addEndpoint($object)
    {
        $this->called[spl_object_hash($object)] = true;

        $data = [];

        $fields = $this->metadataReader->getLinks(get_class($object));

        foreach ($fields as $name => $field) {
            $property = strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst($name)));
            if ($field['absolute']) {
                $data[$property] = $this->assetEndpoint. $this->getPropertyAccessor()->getValue($object, $name);
            }
        }

        return $data;
    }

    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function supportsNormalization($object, $format = null)
    {
        if (!is_object($object)) {
            return false;
        }

        if (array_key_exists(spl_object_hash($object), $this->called)) {
            return false;
        }

        return $this->metadataReader->hasFile(get_class($object));
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
