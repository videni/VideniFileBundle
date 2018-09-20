<?php

namespace App\Bundle\FileBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccess;
use App\Bundle\FileBundle\Metadata\MetadataReader;

class FileEventListener
{
    private $nameConverter;
    private $metadataReader;
    private $assetEndpoint;

    static private $propertyAccessor;

    public function __construct(MetadataReader $metadataReader, $assetEndpoint)
    {
        $this->metadataReader = $metadataReader;
        $this->assetEndpoint = $assetEndpoint;
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function prePersist(LifecycleEventArgs $event): void
    {
        $this->strip($event);
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function preUpdate(LifecycleEventArgs $event): void
    {
        $this->strip($event);
    }

    protected function strip($event)
    {
        $entity = $event->getEntity();
        $class = get_class($entity);
        if (!$this->metadataReader->hasFile($class)) {
            return ;
        }

        $propertyAccessor = $this->getPropertyAccessor();

        $fields = $this->metadataReader->getLinks($class);
        foreach ($fields as $name => $field) {
            $value =  $propertyAccessor->getValue($entity, $name);

            $propertyAccessor->setValue($entity, $name, $this->stripEndPoint($value));
        }
    }

    private function stripEndPoint($propertyValue)
    {
        $position =  strpos($propertyValue, $this->assetEndpoint);

        return $position !== false? substr($propertyValue, $position+strlen($this->assetEndpoint)) : $propertyValue;
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
