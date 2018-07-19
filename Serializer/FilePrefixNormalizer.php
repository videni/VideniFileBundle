<?php

namespace App\Bundle\FileBundle\Serializer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use Gaufrette\Exception\FileNotFound;
use Vich\UploaderBundle\Metadata\MetadataReader;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Exception\MappingNotFoundException;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

/**
 * 给文件添加域名
 */
class FilePrefixNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    private $uploaderHelper;
    private $metadataReader;
    private $url;
    private $decoratedNormalizer;

    public function __construct(
        AbstractNormalizer $normailizer,
        UploaderHelper $uploaderHelper,
        MetadataReader $metadataReader,
        $url
    ) {
        $this->decoratedNormalizer = $normailizer;
        $this->uploaderHelper = $uploaderHelper;
        $this->metadataReader = $metadataReader;
        $this->url = $url;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = [];
        $data = $this->addPrefix($object);

        return  array_merge($this->decoratedNormalizer->normalize($object, $format, $context), $data);
    }

    protected function addPrefix($object)
    {
        $data = [];

        $fields = $this->metadataReader->getUploadableFields(get_class($object));
        foreach ($fields as $name => $field) {
            $property = strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst($field['fileNameProperty'])));
            $path =  $this->uploaderHelper->asset($object, $name);
            $data[$property] = $this->url. $path;  //如：model/test.png
        }

        return $data;
    }

    public function setSerializer(SerializerInterface $serializer)
    {
        $this->decoratedNormalizer->setSerializer($serializer);
    }

    public function supportsNormalization($object, $format = null)
    {
        return is_object($object) && $this->metadataReader->isUploadable(get_class($object));
    }
}
