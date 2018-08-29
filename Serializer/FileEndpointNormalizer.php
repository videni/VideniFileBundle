<?php

namespace App\Bundle\FileBundle\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;
use App\Bundle\FileBundle\Metadata\MetadataReader;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

/**
 * 给文件链接添加域名
 */
class FileEndpointNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    private const FILE_ATTRIBUTE_DENORMALIZER_ALREADY_CALLED = 'FILE_ATTRIBUTE_DENORMALIZER_ALREADY_CALLED';

    private $metadataReader;
    private $serializer;
    private $nameConverter;
    private $assetEndpoint;
    private $called = [];


    /**
     * @var PropertyAccessor
     */
    private static $propertyAccessor;

    public function __construct(
        MetadataReader $metadataReader,
        NameConverterInterface $nameConverter,
        $assetEndpoint
    ) {
        $this->metadataReader = $metadataReader;
        $this->nameConverter = $nameConverter;
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
            $property = $this->nameConverter->normalize($name);
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

    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $fields = $this->metadataReader->getLinks($class);
        foreach ($fields as $name => $field) {
            $property = $this->nameConverter->normalize($name);
            if (isset($data[$property])) {
                $position =   strpos($data[$property], $this->assetEndpoint);
                $data[$property] = $position !== false? substr($data[$property], $position+strlen($this->assetEndpoint)) : $data[$property];
            }
        }

        $context[self::FILE_ATTRIBUTE_DENORMALIZER_ALREADY_CALLED] = true;

        return $this->serializer->denormalize($data, $class, $format, $context);
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = array())
    {
        if (isset($context[self::FILE_ATTRIBUTE_DENORMALIZER_ALREADY_CALLED])) {
            return false;
        }

        return class_exists($type) && $this->metadataReader->hasFile($type);
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
