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

use Bb\Bundle\Workflow\CoreBundle\Entity\Task;
use Bb\Bundle\Workflow\CoreBundle\BbWorkflowCoreEvents;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Monolog\Logger;

use \JMS\DiExtraBundle\Annotation\Service as Service;
use \JMS\DiExtraBundle\Annotation\Tag as Tag;
use \JMS\DiExtraBundle\Annotation\InjectParams as InjectParams;
use \JMS\DiExtraBundle\Annotation\Inject as Inject;

/**
 * Registers event listeners for TaskEvents.
 *
 * @Service("bb.workflow.core.task.event.subscriber")
 *
 * @Tag("kernel.event_subscriber")
 */
class TaskSubscriber implements EventSubscriberInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param Logger $logger
     *
     * @InjectParams({
     *   "logger" = @Inject("logger"),
     * })
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            BbWorkflowCoreEvents::TASK_NEW => array(
                array('onTaskAny', 0),
                array('onTaskNew', 0),
            ),
            BbWorkflowCoreEvents::TASK_ASSIGN => array(
                array('onTaskAny', 0),
                array('onTaskAssign', 0),
            ),
            BbWorkflowCoreEvents::TASK_REJECT => array(
                array('onTaskAny', 0),
                array('onTaskReject', 0),
            ),
            BbWorkflowCoreEvents::TASK_START => array(
                array('onTaskAny', 0),
                array('onTaskStart', 0),
            ),
            BbWorkflowCoreEvents::TASK_FINISH => array(
                array('onTaskAny', 0),
                array('onTaskFinish', 0),
            ),
            BbWorkflowCoreEvents::TASK_SEND => array(
                array('onTaskAny', 0),
                array('onTaskSend', 0),
            ),
            BbWorkflowCoreEvents::TASK_ARCHIVE => array(
                array('onTaskAny', 0),
                array('onTaskArchive', 0),
            ),
        );
    }

    /**
     * Event listener.
     *
     * @param TaskEvent       $event
     * @param string          $eventName
     * @param EventDispatcher $dispatcher
     */
    public function onTaskAny(TaskEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        $message = 'Task status was changed to ' . $event->getTask()->getStatus();
        $context = array(
            'task' => $event->getTask(),
            'operator' => $event->getOperator(),
        );
        $this->logger->log(Logger::DEBUG, $message, array(), $context);
    }

    /**
     * Event listener.
     *
     * @param TaskEvent       $event
     * @param string          $eventName
     * @param EventDispatcher $dispatcher
     */
    public function onTaskNew(TaskEvent $event, $eventName, EventDispatcher $dispatcher)
    {
    }

    /**
     * Event listener.
     *
     * @param TaskEvent       $event
     * @param string          $eventName
     * @param EventDispatcher $dispatcher
     */
    public function onTaskAssign(TaskEvent $event, $eventName, EventDispatcher $dispatcher)
    {
    }

    /**
     * Event listener.
     *
     * @param TaskEvent       $event
     * @param string          $eventName
     * @param EventDispatcher $dispatcher
     */
    public function onTaskReject(TaskEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        $event->getTask()->setResource(null);
        $event->getTask()->setStatus(Task::STATUS_NEW);
    }

    /**
     * Event listener.
     *
     * @param TaskEvent       $event
     * @param string          $eventName
     * @param EventDispatcher $dispatcher
     */
    public function onTaskStart(TaskEvent $event, $eventName, EventDispatcher $dispatcher)
    {
    }

    /**
     * Event listener.
     *
     * @param TaskEvent       $event
     * @param string          $eventName
     * @param EventDispatcher $dispatcher
     */
    public function onTaskFinish(TaskEvent $event, $eventName, EventDispatcher $dispatcher)
    {
    }

    /**
     * Event listener.
     *
     * @param TaskEvent       $event
     * @param string          $eventName
     * @param EventDispatcher $dispatcher
     */
    public function onTaskSend(TaskEvent $event, $eventName, EventDispatcher $dispatcher)
    {
    }

    /**
     * Event listener.
     *
     * @param TaskEvent       $event
     * @param string          $eventName
     * @param EventDispatcher $dispatcher
     */
    public function onTaskArchive(TaskEvent $event, $eventName, EventDispatcher $dispatcher)
    {
    }
}
