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
use Bb\Bundle\Workflow\CoreBundle\Security\Voter\TaskWorkflow\ResourceVoter;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Tests the ResourceVoter.
 *
 * @package Bb\Bundle\Workflow\CoreBundle\Tests
 */
class ResourceVoterTest extends AbstractVoterTest
{
    /**
     * Creates the ResourceVoter.
     *
     * @return ResourceVoter
     */
    protected function _createVoter()
    {
        $roleHierarchy = $this->getMock('Symfony\Component\Security\Core\Role\RoleHierarchyInterface');

        return new ResourceVoter($roleHierarchy);
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
            array(Task::STATUS_ASSIGNED, Task::STATUS_STARTED, ResourceVoter::ACCESS_DENIED),
            // No user may ever re-assign any Task if it is finished.
            array(Task::STATUS_FINISHED, Task::STATUS_ASSIGNED, ResourceVoter::ACCESS_DENIED),
            // No user may ever re-assign any Task if it is sent to the client.
            array(Task::STATUS_SENT, Task::STATUS_ASSIGNED, ResourceVoter::ACCESS_DENIED),
            // No user may ever re-assign any Task if it is archived.
            array(Task::STATUS_ARCHIVED, Task::STATUS_ASSIGNED, ResourceVoter::ACCESS_DENIED),
        );
    }

    /**
     * Tests the vote() method.
     *
     * @param string $currentStatus        The current status of the Task.
     * @param string $newStatus            The requested new status of the Task.
     * @param int    $expectedAccessResult The expected result from the ResourceVoter.
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
            array(Task::STATUS_ASSIGNED, Task::STATUS_STARTED, ResourceVoter::ACCESS_GRANTED),
            // Resources are allowed to send in their completed work.
            array(Task::STATUS_STARTED, Task::STATUS_FINISHED, ResourceVoter::ACCESS_GRANTED),
        );
    }

    /**
     * Tests the vote() method for the logged in user as the Task resource.
     *
     * @param string $currentStatus        The current status of the Task.
     * @param string $newStatus            The requested new status of the Task.
     * @param int    $expectedAccessResult The expected result from the ResourceVoter.
     *
     * @dataProvider providerTestVoteAsResource
     */
    public function testVoteAsResource($currentStatus, $newStatus, $expectedAccessResult)
    {
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $user = new User();

        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $roleHierarchy = $this->getMock('Symfony\Component\Security\Core\Role\RoleHierarchyInterface');
        $voter = new ResourceVoter($roleHierarchy);

        $task = new Task();
        $task->setStatus($currentStatus);
        $task->setResource($user);

        $result = $voter->vote($token, $task, array($newStatus));

        $this->assertEquals($expectedAccessResult, $result);
    }

    /**
     * Data provider for testVoteFallback().
     *
     * These workflow transitions apply to Administrators performing actions
     * that pass any requirements on the Task status in order to perform the
     * actions. In other words, the status of the Task is not blocking to the
     * requested action. If the Task status is not blocking for the requested
     * action, then only the role of the user who requests the action is
     * blocking.
     *
     * @return array
     */
    public function providerTestVoteFallback()
    {
        return array(
            // Administrators may assign any new Task
            array(User::ROLE_ADMIN, null, Task::STATUS_NEW, Task::STATUS_ASSIGNED, ResourceVoter::ACCESS_GRANTED),
            // Administrators may re-assign any new Task
            array(User::ROLE_ADMIN, null, Task::STATUS_STARTED, Task::STATUS_ASSIGNED, ResourceVoter::ACCESS_GRANTED),
            // Administrators may accept and start any assigned Task on behalf of anyone
            array(User::ROLE_ADMIN, new User(), Task::STATUS_ASSIGNED, Task::STATUS_STARTED, ResourceVoter::ACCESS_GRANTED),
            // Administrators may finish any started Task on behalf of anyone
            array(User::ROLE_ADMIN, null, Task::STATUS_STARTED, Task::STATUS_FINISHED, ResourceVoter::ACCESS_GRANTED),
            // Administrators may send any finished Task to the client on behalf of anyone
            array(User::ROLE_ADMIN, null, Task::STATUS_FINISHED, Task::STATUS_SENT, ResourceVoter::ACCESS_GRANTED),
            // Administrators may archive any sent Task.
            array(User::ROLE_ADMIN, null, Task::STATUS_SENT, Task::STATUS_ARCHIVED, ResourceVoter::ACCESS_GRANTED),
            // Resources may not assign any new Task to anyone.
            array(User::ROLE_RESOURCE, null, Task::STATUS_NEW, Task::STATUS_ASSIGNED, ResourceVoter::ACCESS_DENIED),
            // Resources may not send any finished Task to the client.
            array(USER::ROLE_RESOURCE, null, Task::STATUS_FINISHED, Task::STATUS_SENT, ResourceVoter::ACCESS_DENIED),
            // Resources may not archive any Tasks.
            array(User::ROLE_RESOURCE, null, Task::STATUS_SENT, Task::STATUS_ARCHIVED, ResourceVoter::ACCESS_DENIED),
            // Clients may not assign any Tasks.
            array(User::ROLE_CLIENT, null, Task::STATUS_NEW, Task::STATUS_ASSIGNED, ResourceVoter::ACCESS_DENIED),
            // Clients may not start any Tasks.
            array(User::ROLE_CLIENT, new User(), Task::STATUS_ASSIGNED, Task::STATUS_STARTED, ResourceVoter::ACCESS_DENIED),
            // Clients may not re-assign any Tasks.
            array(User::ROLE_CLIENT, new User(), Task::STATUS_STARTED, Task::STATUS_ASSIGNED, ResourceVoter::ACCESS_DENIED),
            // Clients may not finish any Tasks.
            array(User::ROLE_CLIENT, null, Task::STATUS_STARTED, Task::STATUS_FINISHED, ResourceVoter::ACCESS_DENIED),
            // Clients may not send any Tasks to the client.
            array(User::ROLE_CLIENT, null, Task::STATUS_FINISHED, Task::STATUS_SENT, ResourceVoter::ACCESS_DENIED),
            // Clients may not archive any Tasks.
            array(User::ROLE_CLIENT, null, Task::STATUS_SENT, Task::STATUS_ARCHIVED, ResourceVoter::ACCESS_DENIED),
        );
    }

    /**
     * Tests the vote() method for Task statuses that meet the requirements.
     *
     * @param string $roleString           The role name of the logged in user.
     * @param User   $taskResource         The Task resource.
     * @param string $currentStatus        The current status of the Task.
     * @param string $newStatus            The requested new status of the Task.
     * @param int    $expectedAccessResult The expected result from the ResourceVoter.
     *
     * @dataProvider providerTestVoteFallback
     */
    public function testVoteFallback($roleString, $taskResource, $currentStatus, $newStatus, $expectedAccessResult)
    {
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');

        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $role = new Role($roleString);

        $token->expects($this->once())
            ->method('getRoles')
            ->willReturn(array($role));

        $roleHierarchy = $this->getMock('Symfony\Component\Security\Core\Role\RoleHierarchyInterface');
        $roleHierarchy->expects($this->once())
            ->method('getReachableRoles')
            ->with()
            ->willReturn(array($role));

        $voter = new ResourceVoter($roleHierarchy);

        $task = new Task();
        $task->setStatus($currentStatus);

        if ($taskResource) {
            $task->setResource($taskResource);
        }

        $result = $voter->vote($token, $task, array($newStatus));

        $this->assertEquals($expectedAccessResult, $result);
    }
}
