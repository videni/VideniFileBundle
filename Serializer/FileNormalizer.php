<?php

namespace Videni\Bundle\FileBundle\Serializer;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\GraphNavigator;

class FileNormalizer implements EventSubscriberInterface
{
    private $fileHelper;

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

        if ($this->fileHelper->isUploadableEntity($object)) {
            $this->fileHelper->prependPrefix($object);
        }

        if ($this->fileHelper->isFileAnnotatedEntity($object)) {
            $this->fileHelper->prependHost($object);
        }
    }
}
