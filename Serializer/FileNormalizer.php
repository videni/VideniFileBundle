<?php

namespace Videni\Bundle\FileBundle\Serializer;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\GraphNavigator;

class FileNormalizer implements EventSubscriberInterface
{
    private $fileHelper;

    private $called = [];

    public function __construct(FileHelper $fileHelper)
    {
        $this->fileHelper = $fileHelper;
    }

    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => Events::PRE_SERIALIZE,
                'method' => 'onPostSerialize',
                'format' => 'json',
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION
            ]
        ];
    }

    public function onPostSerialize(ObjectEvent $event)
    {
        $object = $event->getObject();
        if (array_key_exists(spl_object_hash($object), $this->called)) {
            return;
        }

        $this->called[spl_object_hash($object)] = true;

        if ($this->fileHelper->isUploadableEntity($object)) {
            $this->fileHelper->prependPrefix($object);
        }

        if ($this->fileHelper->isFileAnnotatedEntity($object)) {
            $this->fileHelper->prependHost($object);
        }
    }
}
