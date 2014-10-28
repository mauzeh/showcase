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

namespace Bb\Bundle\Workflow\CoreBundle\Security\Voter;

use Bb\Bundle\Workflow\CoreBundle\Entity\Task;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use \JMS\DiExtraBundle\Annotation\Service as Service;
use \JMS\DiExtraBundle\Annotation\Tag as Tag;
use \JMS\DiExtraBundle\Annotation\InjectParams as InjectParams;
use \JMS\DiExtraBundle\Annotation\Inject as Inject;

/**
 * Task Workflow Security voter.
 *
 * Determines whether the logged in user is allowed to change the status of a
 * Task.
 *
 * @Service("bb.workflow.core.task.workflow.voter")
 * @Tag("security.voter")
 */
class TaskWorkflowVoter extends AbstractVoter
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
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $post, array $attributes)
    {
        // Check if class of this object is supported by this voter
        if (!$this->supportsClass(get_class($post))) {
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

        // make sure there is a user object (i.e. that the user is logged in)
        if (!$user instanceof UserInterface) {
            return VoterInterface::ACCESS_DENIED;
        }

        // Get the current status of the Task
        $currentStatus = $post->getStatus();

        // Get the Task resource, i.e. the user that is to perform the Task.
        $resource = $post->getResource();

        switch ($attribute) {

            case Task::STATUS_NEW:
                $allowedStatuses = array(
                    Task::STATUS_ASSIGNED,
                    Task::STATUS_STARTED,
                );
                if (!in_array($currentStatus, $allowedStatuses)) {
                    return VoterInterface::ACCESS_DENIED;
                }
                // The Resource him/herself may set this task back to "new",
                // only if the current status is "assigned".
                if ($user == $resource &&
                    $currentStatus === Task::STATUS_ASSIGNED
                ) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                // Fall back to final catch-all access check.
                break;

            case Task::STATUS_ASSIGNED:
                $allowedStatuses = array(
                    Task::STATUS_NEW,
                    Task::STATUS_STARTED
                );
                if (!in_array($currentStatus, $allowedStatuses)) {
                    return VoterInterface::ACCESS_DENIED;
                }
                // Fall back to final catch-all access check.
                break;

            case Task::STATUS_STARTED:
                $allowedStatuses = array(
                    Task::STATUS_ASSIGNED,
                    Task::STATUS_FINISHED,
                );
                if (!in_array($currentStatus, $allowedStatuses)) {
                    return VoterInterface::ACCESS_DENIED;
                }
                if (!$resource) {
                    return VoterInterface::ACCESS_DENIED;
                }
                if ($resource == $user && $currentStatus == Task::STATUS_ASSIGNED) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                // Fall back to final catch-all access check.
                break;

            case Task::STATUS_FINISHED:
                $allowedStatuses = array(
                    Task::STATUS_STARTED,
                );
                if (!in_array($currentStatus, $allowedStatuses)) {
                    return VoterInterface::ACCESS_DENIED;
                }
                if ($resource == $user) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                // Fall back to final catch-all access check.
                break;

            case Task::STATUS_SENT:
                $allowedStatuses = array(
                    Task::STATUS_FINISHED,
                );
                if (!in_array($currentStatus, $allowedStatuses)) {
                    return VoterInterface::ACCESS_DENIED;
                }
                // Fall back to final catch-all access check.
                break;

            case Task::STATUS_ARCHIVED:
                $allowedStatuses = array(
                    Task::STATUS_SENT,
                );
                if (!in_array($currentStatus, $allowedStatuses)) {
                    return VoterInterface::ACCESS_DENIED;
                }
                // Fall back to final catch-all access check.
                break;
        }

        // Administrators (and higher...) can perform any actions that weren't
        // explicitly denied above.
        if ($this->hasRole('ROLE_BB_ADMIN', $token)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}