<?php

namespace App\Bundle\FileBundle\Serializer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;
use App\Bundle\FileBundle\Metadata\MetadataReader;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * 给链接添加域名
 */
class FileEndpointNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    private $metadataReader;
    private $serializer;
    private $assetEndpoint;

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
        $data = [];

        $fields = $this->metadataReader->getLinks(get_class($object));
        foreach ($fields as $name => $field) {
            $property = strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst($name)));

            if ($field['absolute']) {
                $data[$property] = $this->assetEndpoint. $object->{'get'.ucfirst($name)};
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
        return is_object($object) && $this->metadataReader->hasFile(get_class($object));
    }
}
