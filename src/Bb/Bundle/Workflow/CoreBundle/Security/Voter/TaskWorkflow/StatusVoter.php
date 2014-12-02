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

/**
 * Task Workflow Security voter.
 *
 * Determines whether the logged in user is allowed to change the status of a
 * Task.
 *
 * @Service("bb.workflow.core.task.workflow.status.voter")
 * @Tag("security.voter")
 */
class StatusVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $veto = $this->preVote($token, $object, $attributes);
        if ($veto !== null) {
            return $veto;
        }

        $attribute = $attributes[0];
        $allowedStatuses = array(
            Task::STATUS_NEW => array(
                Task::STATUS_ASSIGNED,
                Task::STATUS_STARTED,
            ),
            Task::STATUS_ASSIGNED => array(
                Task::STATUS_NEW,
                Task::STATUS_STARTED,
            ),
            Task::STATUS_STARTED => array(
                Task::STATUS_ASSIGNED,
                Task::STATUS_FINISHED,
            ),
            Task::STATUS_FINISHED => array(
                Task::STATUS_STARTED,
            ),
            Task::STATUS_SENT => array(
                Task::STATUS_FINISHED,
            ),
            Task::STATUS_ARCHIVED => array(
                Task::STATUS_SENT,
            ),
        );

        if (in_array($object->getStatus(), $allowedStatuses[$attribute])) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
