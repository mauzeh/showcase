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
use Bb\Bundle\Workflow\CoreBundle\Entity\User;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

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
 * @Service("bb.workflow.core.task.workflow.resource.voter")
 * @Tag("security.voter")
 */
class ResourceVoter extends AbstractVoter
{
    /**
     * @var \Symfony\Component\Security\Core\Role\RoleHierarchyInterface
     */
    protected $roleHierarchy;

    /**
     * Constructor of the AbstractVoter class.
     *
     * @param RoleHierarchyInterface $hierarchy
     *
     * @InjectParams({
     *   "hierarchy" = @Inject("security.role_hierarchy"),
     * })
     */
    public function __construct(RoleHierarchyInterface $hierarchy)
    {
        $this->roleHierarchy = $hierarchy;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $veto = $this->preVote($token, $object, $attributes);
        if ($veto !== null) {
            return $veto;
        }

        $user = $token->getUser();
        $attribute = $attributes[0];
        $currentStatus = $object->getStatus();
        $resource = $object->getResource();

        switch ($attribute) {

            case Task::STATUS_NEW:
                // The Resource him/herself may set this task back to "new",
                // only if the current status is "assigned".
                if ($user == $resource && $currentStatus === Task::STATUS_ASSIGNED) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;

            case Task::STATUS_STARTED:
                if (!$resource) {
                    return VoterInterface::ACCESS_DENIED;
                }
                if ($resource == $user && $currentStatus == Task::STATUS_ASSIGNED) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;

            case Task::STATUS_FINISHED:
                if ($resource == $user) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;

        }

        // Administrators (and higher...) can perform any remaining actions.
        if ($this->hasRole(User::ROLE_ADMIN, $token)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    /**
     * Determines whether the logged in user has or supersedes the given role.
     *
     * @param string         $role  The name of the role that is required.
     * @param TokenInterface $token The current security token.
     *
     * @return bool
     */
    protected function hasRole($role, TokenInterface $token)
    {
        $roles = $this->roleHierarchy->getReachableRoles($token->getRoles());
        if ($roles === null) {
            return false;
        }
        foreach ($roles as $roleInIterator) {
            if ($roleInIterator->getRole() === $role) {
                return true;
            }
        }

        return false;
    }
}
