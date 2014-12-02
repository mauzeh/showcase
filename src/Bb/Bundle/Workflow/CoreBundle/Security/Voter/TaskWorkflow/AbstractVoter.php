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

namespace Bb\Bundle\Workflow\CoreBundle\Security\Voter\TaskWorkflow;

use Bb\Bundle\Workflow\CoreBundle\Entity\Task;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

use \JMS\DiExtraBundle\Annotation\Service as Service;
use \JMS\DiExtraBundle\Annotation\Tag as Tag;
use \JMS\DiExtraBundle\Annotation\InjectParams as InjectParams;
use \JMS\DiExtraBundle\Annotation\Inject as Inject;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Provides shared operations for the TaskWorkflow security voters.
 *
 * @package Bb\Bundle\Workflow\CoreBundle\Security\Voter
 */
abstract class AbstractVoter implements VoterInterface
{
    /**
     * Returns the attributes that this Voter supports.
     *
     * @return array
     */
    public function getSupportedAttributes()
    {
        return array_keys(Task::getStatuses());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, $this->getSupportedAttributes());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        $supportedClass = 'Bb\Bundle\Workflow\CoreBundle\Entity\Task';

        return $supportedClass === $class || is_subclass_of($class, $supportedClass);
    }

    /**
     * Determines if the voter implementation should be invoked.
     *
     * @param TokenInterface $token      A TokenInterface instance
     * @param object|null    $object     The object to secure
     * @param array          $attributes An array of attributes associated with the method being invoked
     *
     * @return int|null either ACCESS_ABSTAIN, ACCESS_DENIED, or null if no verdict.
     */
    public function preVote(TokenInterface $token, $object, array $attributes)
    {
        // Check if class of this object is supported by this voter
        if (!$this->supportsClass(get_class($object))) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // Check if the voter is used correctly. This voter only allows one
        // attribute.
        if (1 !== count($attributes)) {
            throw new \InvalidArgumentException('Only one attribute is allowed');
        }

        // Set the attribute to check against
        $attribute = $attributes[0];

        // Check if the given attribute is covered by this voter
        if (!$this->supportsAttribute($attribute)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // Get currently logged in user
        $user = $token->getUser();

        // Make sure there is a user object (i.e. that the user is logged in)
        if (!$user instanceof UserInterface) {
            return VoterInterface::ACCESS_DENIED;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function vote(TokenInterface $token, $object, array $attributes);
}
