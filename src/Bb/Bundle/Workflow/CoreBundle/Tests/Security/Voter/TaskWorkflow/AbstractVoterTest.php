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
use Bb\Bundle\Workflow\CoreBundle\Security\Voter\TaskWorkflow\AbstractVoter;

/**
 * Tests the AbstractVoter.
 *
 * @package Bb\Bundle\Workflow\CoreBundle\Tests
 */
class AbstractVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The class that the AbstractVoter should support.
     *
     * @var string
     */
    private $_class = 'Bb\Bundle\Workflow\CoreBundle\Entity\Task';

    /**
     * The attributes that the AbstractVoter should support.
     *
     * @var array
     */
    private $_supportedAttributes = array();

    /**
     * Initializes the attributest that the AbstractVoter should support.
     */
    protected function setUp()
    {
        $this->_supportedAttributes = array_keys(Task::getStatuses());
    }

    /**
     * Creates the AbstractVoter.
     *
     * @return AbstractVoter
     */
    protected function createVoter()
    {
        $voter = $this->getMockForAbstractClass('Bb\Bundle\Workflow\CoreBundle\Security\Voter\TaskWorkflow\AbstractVoter');

        return $voter;
    }

    /**
     * Tests the getSupportedAttributes() method.
     */
    public function testGetSupportedAttributes()
    {
        $voter = $this->createVoter();
        $expected = $this->_supportedAttributes;
        $result = $voter->getSupportedAttributes();
        $this->assertSame($expected, $result);
    }

    /**
     * Tests the supportsAttribute() method.
     */
    public function testSupportsAttribute()
    {
        $voter = $this->createVoter();
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
        $voter = $this->createVoter();
        $this->assertTrue($voter->supportsClass($this->_class));
    }

    /**
     * The Voter should abstain from voting because the class is unsupported.
     */
    public function testVoteUnsupportedClass()
    {
        $voter = $this->createVoter();
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $this->assertEquals(AbstractVoter::ACCESS_ABSTAIN, $voter->vote($token, new \StdClass(), array()));
    }

    /**
     * The Voter should abstain from voting because the attribute is unsupported.
     */
    public function testVoteUnsupportedAttribute()
    {
        $voter = $this->createVoter();
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $this->assertEquals(AbstractVoter::ACCESS_ABSTAIN, $voter->vote($token, new Task(), array('unsupported')));
    }

    /**
     * Access should be denied because there is no valid logged in user.
     */
    public function testVoteAsUnAuthenticatedUser()
    {
        $voter = $this->createVoter();
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $user = new \StdClass();
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $this->assertEquals(AbstractVoter::ACCESS_DENIED, $voter->preVote($token, new Task(), array($this->_supportedAttributes[0])));
    }

    /**
     * Tests that invalid arguments should raise an exception.
     */
    public function testVoteInvalidArgumentException()
    {
        $voter = $this->createVoter();
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $this->setExpectedException('\InvalidArgumentException');
        $voter->preVote($token, new Task(), array('one', 'two'));
    }
}
