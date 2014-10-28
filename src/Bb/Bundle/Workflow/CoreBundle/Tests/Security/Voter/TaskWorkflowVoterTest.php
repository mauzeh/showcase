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

namespace Bb\Bundle\Workflow\CoreBundle\Tests\Security\Voter;

use Bb\Bundle\Workflow\CoreBundle\Entity\Task;
use Bb\Bundle\Workflow\CoreBundle\Entity\User;
use Bb\Bundle\Workflow\CoreBundle\Security\Voter\TaskWorkflowVoter;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Tests the TaskWorkflowVoter.
 *
 * @package Bb\Bundle\Workflow\CoreBundle\Tests
 */
class TaskWorkflowVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The class that the TaskWorkflowVoter should support.
     *
     * @var string
     */
    private $_class = 'Bb\Bundle\Workflow\CoreBundle\Entity\Task';

    /**
     * The attributes that the TaskWorkflowVoter should support.
     *
     * @var array
     */
    private $_supportedAttributes = array();

    /**
     * Initializes the attributest that the TaskWorkflowVoter should support.
     */
    protected function setUp()
    {
        $this->_supportedAttributes = array_keys(Task::getStatuses());
    }

    /**
     * Creates the TaskWorkflowVoter.
     *
     * @return TaskWorkflowVoter
     */
    private function _createVoter()
    {
        $roleHierarchy = $this->getMock('Symfony\Component\Security\Core\Role\RoleHierarchyInterface');

        return new TaskWorkflowVoter($roleHierarchy);
    }

    /**
     * Tests the getSupportedAttributes() method.
     */
    public function testGetSupportedAttributes()
    {
        $voter = $this->_createVoter();
        $this->assertSame(
            $this->_supportedAttributes,
            $voter->getSupportedAttributes()
        );
    }

    /**
     * Tests the supportsAttribute() method.
     */
    public function testSupportsAttribute()
    {
        $voter = $this->_createVoter();
        $this->assertFalse($voter->supportsAttribute('nonexistent'));
        foreach ($this->_supportedAttributes as $attribute) {
            $this->assertTrue($voter->supportsAttribute($attribute));
        }
    }

    /**
     * Tests the supportsClass() method.
     */
    public function testSupportsClass()
    {
        $voter = $this->_createVoter();
        $this->assertTrue($voter->supportsClass($this->_class));
    }

    /**
     * The Voter should abstain from voting because the class is unsupported.
     */
    public function testVoteUnsupportedClass()
    {
        $voter = $this->_createVoter();
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $this->assertEquals(TaskWorkflowVoter::ACCESS_ABSTAIN, $voter->vote($token, new \StdClass(), array()));
    }

    /**
     * The Voter should abstain from voting because the attribute is unsupported.
     */
    public function testVoteUnsupportedAttribute()
    {
        $voter = $this->_createVoter();
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $this->assertEquals(TaskWorkflowVoter::ACCESS_ABSTAIN, $voter->vote($token, new Task(), array('unsupported')));
    }

    /**
     * Access should be denied because there is no valid logged in user.
     */
    public function testVoteAsUnAuthenticatedUser()
    {
        $voter = $this->_createVoter();
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $user = new \StdClass();
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $this->assertEquals(TaskWorkflowVoter::ACCESS_DENIED, $voter->vote($token, new Task(), array($this->_supportedAttributes[0])));
    }

    /**
     * Tests that invalid arguments should raise an exception.
     */
    public function testVoteInvalidArgumentException()
    {
        $voter = $this->_createVoter();
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $this->setExpectedException('\InvalidArgumentException');
        $voter->vote($token, new Task(), array('one', 'two'));
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
            array(Task::STATUS_ASSIGNED, Task::STATUS_STARTED, TaskWorkflowVoter::ACCESS_DENIED),
            // No user may ever re-assign any Task if it is finished.
            array(Task::STATUS_FINISHED, Task::STATUS_ASSIGNED, TaskWorkflowVoter::ACCESS_DENIED),
            // No user may ever re-assign any Task if it is sent to the client.
            array(Task::STATUS_SENT, Task::STATUS_ASSIGNED, TaskWorkflowVoter::ACCESS_DENIED),
            // No user may ever re-assign any Task if it is archived.
            array(Task::STATUS_ARCHIVED, Task::STATUS_ASSIGNED, TaskWorkflowVoter::ACCESS_DENIED),
        );
    }

    /**
     * Tests the vote() method.
     *
     * @param string $currentStatus        The current status of the Task.
     * @param string $newStatus            The requested new status of the Task.
     * @param int    $expectedAccessResult The expected result from the TaskWorkflowVoter.
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
            array(Task::STATUS_ASSIGNED, Task::STATUS_STARTED, TaskWorkflowVoter::ACCESS_GRANTED),
            // Resources are not allowed to directly start on a new Task.
            // It needs to explicitly have the "assigned" status.
            array(Task::STATUS_NEW, Task::STATUS_STARTED, TaskWorkflowVoter::ACCESS_DENIED),
            // Resources are allowed to send in their completed work.
            array(Task::STATUS_STARTED, Task::STATUS_FINISHED, TaskWorkflowVoter::ACCESS_GRANTED),
        );
    }

    /**
     * Tests the vote() method for the logged in user as the Task resource.
     *
     * @param string $currentStatus        The current status of the Task.
     * @param string $newStatus            The requested new status of the Task.
     * @param int    $expectedAccessResult The expected result from the TaskWorkflowVoter.
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
        $voter = new TaskWorkflowVoter($roleHierarchy);

        $task = new Task();
        $task->setStatus($currentStatus);
        $task->setResource($user);

        $this->assertEquals($expectedAccessResult, $voter->vote($token, $task, array($newStatus)));
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
            array(User::ROLE_ADMIN, null, Task::STATUS_NEW, Task::STATUS_ASSIGNED, TaskWorkflowVoter::ACCESS_GRANTED),
            // Administrators may re-assign any new Task
            array(User::ROLE_ADMIN, null, Task::STATUS_STARTED, Task::STATUS_ASSIGNED, TaskWorkflowVoter::ACCESS_GRANTED),
            // Administrators may accept and start any assigned Task on behalf of anyone
            array(User::ROLE_ADMIN, new User(), Task::STATUS_ASSIGNED, Task::STATUS_STARTED, TaskWorkflowVoter::ACCESS_GRANTED),
            // Administrators may finish any started Task on behalf of anyone
            array(User::ROLE_ADMIN, null, Task::STATUS_STARTED, Task::STATUS_FINISHED, TaskWorkflowVoter::ACCESS_GRANTED),
            // Administrators may send any finished Task to the client on behalf of anyone
            array(User::ROLE_ADMIN, null, Task::STATUS_FINISHED, Task::STATUS_SENT, TaskWorkflowVoter::ACCESS_GRANTED),
            // Administrators may archive any sent Task.
            array(User::ROLE_ADMIN, null, Task::STATUS_SENT, Task::STATUS_ARCHIVED, TaskWorkflowVoter::ACCESS_GRANTED),
            // Resources may not assign any new Task to anyone.
            array(User::ROLE_RESOURCE, null, Task::STATUS_NEW, Task::STATUS_ASSIGNED, TaskWorkflowVoter::ACCESS_DENIED),
            // Resources may not send any finished Task to the client.
            array(USER::ROLE_RESOURCE, null, Task::STATUS_FINISHED, Task::STATUS_SENT, TaskWorkflowVoter::ACCESS_DENIED),
            // Resources may not archive any Tasks.
            array(User::ROLE_RESOURCE, null, Task::STATUS_SENT, Task::STATUS_ARCHIVED, TaskWorkflowVoter::ACCESS_DENIED),
            // Clients may not assign any Tasks.
            array(User::ROLE_CLIENT, null, Task::STATUS_NEW, Task::STATUS_ASSIGNED, TaskWorkflowVoter::ACCESS_DENIED),
            // Clients may not start any Tasks.
            array(User::ROLE_CLIENT, new User(), Task::STATUS_ASSIGNED, Task::STATUS_STARTED, TaskWorkflowVoter::ACCESS_DENIED),
            // Clients may not re-assign any Tasks.
            array(User::ROLE_CLIENT, new User(), Task::STATUS_STARTED, Task::STATUS_ASSIGNED, TaskWorkflowVoter::ACCESS_DENIED),
            // Clients may not finish any Tasks.
            array(User::ROLE_CLIENT, null, Task::STATUS_STARTED, Task::STATUS_FINISHED, TaskWorkflowVoter::ACCESS_DENIED),
            // Clients may not send any Tasks to the client.
            array(User::ROLE_CLIENT, null, Task::STATUS_FINISHED, Task::STATUS_SENT, TaskWorkflowVoter::ACCESS_DENIED),
            // Clients may not archive any Tasks.
            array(User::ROLE_CLIENT, null, Task::STATUS_SENT, Task::STATUS_ARCHIVED, TaskWorkflowVoter::ACCESS_DENIED),
        );
    }

    /**
     * Tests the vote() method for Task statuses that meet the requirements.
     *
     * @param string $roleString           The role name of the logged in user.
     * @param User   $taskResource         The Task resource.
     * @param string $currentStatus        The current status of the Task.
     * @param string $newStatus            The requested new status of the Task.
     * @param int    $expectedAccessResult The expected result from the TaskWorkflowVoter.
     *
     * @dataProvider providerTestVoteFallback
     */
    public function testVoteFallback($roleString, $taskResource, $currentStatus, $newStatus, $expectedAccessResult)
    {
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');

        $token->expects($this->once())
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

        $voter = new TaskWorkflowVoter($roleHierarchy);

        $task = new Task();
        $task->setStatus($currentStatus);

        if ($taskResource) {
            $task->setResource($taskResource);
        }

        $this->assertEquals($expectedAccessResult, $voter->vote($token, $task, array($newStatus)));
    }
}