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

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

use \JMS\DiExtraBundle\Annotation\Service as Service;
use \JMS\DiExtraBundle\Annotation\Tag as Tag;
use \JMS\DiExtraBundle\Annotation\InjectParams as InjectParams;
use \JMS\DiExtraBundle\Annotation\Inject as Inject;

/**
 * This voter includes logic to check the user role from within a voter.
 *
 * @package Bb\Bundle\Workflow\CoreBundle\Security\Voter
 */
abstract class AbstractVoter implements VoterInterface
{
    /**
     * @var \Symfony\Component\Security\Core\Role\RoleHierarchyInterface
     */
    private $roleHierarchy;

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
    abstract public function supportsAttribute($attribute);

    /**
     * {@inheritdoc}
     */
    abstract public function supportsClass($class);

    /**
     * {@inheritdoc}
     */
    abstract public function vote(TokenInterface $token, $post, array $attributes);

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
        foreach ($roles as $roleInIterator) {
            if ($roleInIterator->getRole() === $role) {
                return true;
            }
        }

        return false;
    }
}