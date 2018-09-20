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
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class FileEndpointDenormalizer implements DenormalizerInterface
{
    private $decoratedNormalizer;
    private $nameConverter;
    private $metadataReader;
    private $assetEndpoint;

    /**
     * @var PropertyAccessor
     */
    private static $propertyAccessor;

    public function __construct(
        AbstractObjectNormalizer $decoratedNormalizer,
        MetadataReader $metadataReader,
        NameConverterInterface $nameConverter,
        $assetEndpoint
    ) {
        $this->decoratedNormalizer = $decoratedNormalizer;
        $this->nameConverter = $nameConverter;
        $this->metadataReader = $metadataReader;
        $this->assetEndpoint = $assetEndpoint;
    }

    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $fields = $this->metadataReader->getLinks($class);

        foreach ($fields as $name => $field) {
            $property = $this->nameConverter->normalize($name);
            if (isset($data[$property])) {
                $data[$property] =  $this->stripEndPoint($data[$property]);
            }
        }

        return $this->decoratedNormalizer->denormalize($data, $class, $format, $context);
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = array())
    {
        return class_exists($type) && $this->metadataReader->hasFile($type);
    }

    private function stripEndPoint($propertyValue)
    {
        $position =  strpos($propertyValue, $this->assetEndpoint);
        return $position !== false? substr($propertyValue, $position+strlen($this->assetEndpoint)) : $propertyValue;
    }
}
