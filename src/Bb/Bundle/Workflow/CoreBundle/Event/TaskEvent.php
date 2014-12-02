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

use Bb\Bundle\Workflow\CoreBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;
use Bb\Bundle\Workflow\CoreBundle\Entity\Task;

/**
 * Class TaskEvent
 */
class TaskEvent extends Event
{
    /**
     * @var Task
     */
    protected $task;

    /**
     * @var User
     */
    private $operator = null;

    /**
     * @param Task $task
     * @param User $operator
     */
    public function __construct(Task $task, User $operator = null)
    {
        $this->task = $task;
        $this->operator = $operator;
    }

    /**
     * @return Task
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @return User
     */
    public function getOperator()
    {
        return $this->operator;
    }
}
