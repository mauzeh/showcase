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

namespace Bb\Bundle\Workflow\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * User
 *
 * @ORM\Table(name="bb_core_user")
 * @ORM\Entity(repositoryClass="Bb\Bundle\Workflow\CoreBundle\Entity\UserRepository")
 */
class User extends BaseUser
{
    const ROLE_ADMIN = 'ROLE_BB_ADMIN';
    const ROLE_SUPER = 'ROLE_BB_SUPER';
    const ROLE_RESOURCE = 'ROLE_BB_RESOURCE';
    const ROLE_CLIENT = 'ROLE_BB_CLIENT';

    /**
     * Returns an array with translatable strings of the available roles.
     *
     * @return array
     */
    public static function getHumanFriendlyRoles()
    {
        return array(
            self::ROLE_SUPER => 'bb.i18n.workflow.core.user.role.super',
            self::ROLE_ADMIN => 'bb.i18n.workflow.core.user.role.admin',
            self::ROLE_RESOURCE => 'bb.i18n.workflow.core.user.role.resource',
            self::ROLE_CLIENT => 'bb.i18n.workflow.core.user.role.client',
        );
    }

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the string representation of this object.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getUsername();
    }
}
