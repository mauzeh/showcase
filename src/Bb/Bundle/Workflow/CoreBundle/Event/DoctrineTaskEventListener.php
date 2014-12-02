<?php

/*
 * This file is a demonstration file modeled after a live Symfony2 application.
 *
 * Implementation details relating to the original application have been adapted
 * for the purpose of this demonstration.
 *
 * (c) Maurits Dekkers <bluedackers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bb\Bundle\Workflow\CoreBundle\Event;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use \JMS\DiExtraBundle\Annotation\DoctrineListener as DoctrineListener;
use \JMS\DiExtraBundle\Annotation\Service as Service;
use \JMS\DiExtraBundle\Annotation\Tag as Tag;
use \JMS\DiExtraBundle\Annotation\InjectParams as InjectParams;
use \JMS\DiExtraBundle\Annotation\Inject as Inject;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Class DoctrineTaskEventListener
 *
 * @Service("bb.workflow.core.event.task.doctrine_listener")
 * @Tag(
 *  "doctrine.event_listener", attributes = {
 *    "event" = "onFlush",
 *    "method" = "onFlush"
 * })
 * @package Bb\Bundle\Workflow\CoreBundle\Event
 */
class DoctrineTaskEventListener
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Constructor.
     *
     * @param EventDispatcherInterface $dispatcher
     *
     * @InjectParams({
     *   "dispatcher" = @Inject("event_dispatcher"),
     * })
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $eventManager = $em->getEventManager();

        // Remove the current listener to allow invocations of $em->flush().
        $eventManager->removeEventListener('onFlush', $this);

        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $key => $entity) {

            // Get the plain class name without namespace
            $className = join('', array_slice(explode('\\', get_class($entity)), -1));

            // Ignore changes to non-Task entities
            if ($className !== 'Task') {
                continue;
            }

            $meta = $em->getClassMetadata(get_class($entity));

            $changeSet = $uow->getEntityChangeSet($entity);

            // If the status was changed
            if (array_key_exists('status', $changeSet)) {

                $newStatus = $changeSet['status'][1];

                // Dispatch the event
                $event = new TaskEvent($entity, $entity->getResource());
                $this->dispatcher->dispatch($newStatus, $event);
            }

            $em->persist($entity);
            $em->flush();

            $uow->computeChangeSet($meta, $entity);
        }

        // Re-attach the current listener for any invocations of $em->flush().
        $eventManager->addEventListener('onFlush', $this);
    }
}
