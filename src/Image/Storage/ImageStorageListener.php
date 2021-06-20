<?php

declare(strict_types=1);

namespace App\Image\Storage;

use App\Image\Model\Image;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class ImageStorageListener implements EventSubscriber
{
    public function __construct(private ImageStorage $imageStorage)
    {
    }

    public function prePersist(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getEntity();

        if ($entity instanceof Image) {
            $this->imageStorage->upload($entity);
        }
    }

    public function postRemove(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getEntity();

        if ($entity instanceof Image) {
            $this->imageStorage->delete($entity);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::postRemove,
        ];
    }
}
