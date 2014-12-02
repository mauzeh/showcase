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

namespace Bb\Bundle\Workflow\CoreBundle\Tests\Security\Voter\TaskWorkflow;

use Bb\Bundle\Workflow\CoreBundle\Entity\Task;
use Bb\Bundle\Workflow\CoreBundle\Entity\User;
use Bb\Bundle\Workflow\CoreBundle\Security\Voter\TaskWorkflow\StatusVoter;

/**
 * Tests the StatusVoter.
 *
 * @package Bb\Bundle\Workflow\CoreBundle\Tests
 */
class StatusVoterTest extends AbstractVoterTest
{
    /**
     * Creates the StatusVoter.
     *
     * @return StatusVoter
     */
    protected function _createVoter()
    {
        $roleHierarchy = $this->getMock('Symfony\Component\Security\Core\Role\RoleHierarchyInterface');

        return new StatusVoter($roleHierarchy);
    }

    /**
     * Data provider for testVote().
     *
     * These transitions should never be allowed because the Task status is not
     * compliant.
     *
     * @return array
     */
    public function providerTestVote()
    {
        return array(
            // No user may ever start any Task if the Task has no resource.
            array(Task::STATUS_ASSIGNED, Task::STATUS_STARTED, StatusVoter::ACCESS_DENIED),
            // No user may ever re-assign any Task if it is finished.
            array(Task::STATUS_FINISHED, Task::STATUS_ASSIGNED, StatusVoter::ACCESS_DENIED),
            // No user may ever re-assign any Task if it is sent to the client.
            array(Task::STATUS_SENT, Task::STATUS_ASSIGNED, StatusVoter::ACCESS_DENIED),
            // No user may ever re-assign any Task if it is archived.
            array(Task::STATUS_ARCHIVED, Task::STATUS_ASSIGNED, StatusVoter::ACCESS_DENIED),
        );
    }

    /**
     * Tests the vote() method.
     *
     * @param string $currentStatus        The current status of the Task.
     * @param string $newStatus            The requested new status of the Task.
     * @param int    $expectedAccessResult The expected result from the StatusVoter.
     *
     * @dataProvider providerTestVote
     */
    public function testVote($currentStatus, $newStatus, $expectedAccessResult)
    {
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $voter = $this->_createVoter();
        $task = new Task();
        $task->setStatus($currentStatus);
        $this->assertEquals($expectedAccessResult, $voter->vote($token, $task, array($newStatus)));
    }

    /**
     * Data provider for testVoteAsResource().
     *
     * These test workflow transitions that apply to users being logged in and
     * attempting the transition on a Task to which they are the assigned
     * resource.
     *
     * @return array
     */
    public function providerTestVoteAsResource()
    {
        return array(
            // Resources are allowed to accept a Task that is assigned to them.
            array(Task::STATUS_ASSIGNED, Task::STATUS_STARTED, StatusVoter::ACCESS_GRANTED),
            // Resources are not allowed to directly start on a new Task.
            // It needs to explicitly have the "assigned" status.
            array(Task::STATUS_NEW, Task::STATUS_STARTED, StatusVoter::ACCESS_DENIED),
            // Resources are allowed to send in their completed work.
            array(Task::STATUS_STARTED, Task::STATUS_FINISHED, StatusVoter::ACCESS_GRANTED),
        );
    }

    /**
     * Tests the vote() method for the logged in user as the Task resource.
     *
     * @param string $currentStatus        The current status of the Task.
     * @param string $newStatus            The requested new status of the Task.
     * @param int    $expectedAccessResult The expected result from the StatusVoter.
     *
     * @dataProvider providerTestVoteAsResource
     */
    public function testVoteAsResource($currentStatus, $newStatus, $expectedAccessResult)
    {
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $user = new User();

        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $roleHierarchy = $this->getMock('Symfony\Component\Security\Core\Role\RoleHierarchyInterface');
        $voter = new StatusVoter($roleHierarchy);

        $task = new Task();
        $task->setStatus($currentStatus);
        $task->setResource($user);

        $this->assertEquals($expectedAccessResult, $voter->vote($token, $task, array($newStatus)));
    }
}