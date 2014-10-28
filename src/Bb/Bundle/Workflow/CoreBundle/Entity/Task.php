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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Task
 *
 * @ORM\Table(name="bb_core_task")
 * @ORM\Entity(repositoryClass="Bb\Bundle\Workflow\CoreBundle\Entity\TaskRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Task
{
    const STATUS_NEW = 'bb_task_status_new';
    const STATUS_ASSIGNED = 'bb_task_status_assigned';
    const STATUS_STARTED = 'bb_task_status_started';
    const STATUS_FINISHED = 'bb_task_status_finished';
    const STATUS_SENT = 'bb_task_status_sent';
    const STATUS_ARCHIVED = 'bb_task_status_archived';

    /**
     * Returns the available statuses that apply to any Task object.
     *
     * @return array
     */
    public static function getStatuses()
    {
        return array(
            self::STATUS_NEW => 'bb.i18n.workflow.core.task.status.new',
            self::STATUS_ASSIGNED => 'bb.i18n.workflow.core.task.status.assigned',
            self::STATUS_STARTED => 'bb.i18n.workflow.core.task.status.started',
            self::STATUS_FINISHED => 'bb.i18n.workflow.core.task.status.finished',
            self::STATUS_SENT => 'bb.i18n.workflow.core.task.status.sent',
            self::STATUS_ARCHIVED => 'bb.i18n.workflow.core.task.status.archived',
        );
    }

    /**
     * Returns the available workflow transitions that apply to any Task object.
     *
     * @return array
     */
    public static function getTransitions()
    {
        return array(
            self::STATUS_NEW => 'bb.i18n.workflow.core.task.transition.new',
            self::STATUS_ASSIGNED => 'bb.i18n.workflow.core.task.transition.assigned',
            self::STATUS_STARTED => 'bb.i18n.workflow.core.task.transition.started',
            self::STATUS_FINISHED => 'bb.i18n.workflow.core.task.transition.finished',
            self::STATUS_SENT => 'bb.i18n.workflow.core.task.transition.sent',
            self::STATUS_ARCHIVED => 'bb.i18n.workflow.core.task.transition.archived',
        );
    }

    /**
     * Return a translatable status string.
     *
     * @return string
     *   The human-readable status.
     */
    public function getStatusHuman()
    {
        $statuses = static::getStatuses();

        return $statuses[$this->getStatus()];
    }

    /**
     * @ORM\PrePersist
     */
    public function setDefaultValues()
    {
        if (!$this->getCreated()) {
            $this->created = new \DateTime();
        }
        if (!$this->getStatus()) {
            $this->status = static::STATUS_NEW;
        }
        if (!$this->getTitle()) {
            $documents = $this->getDocuments();
            $this->title = $documents[0]->getName();
        }
    }

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @ORM\ManyToMany(targetEntity="Document", inversedBy="tasks", cascade={"remove"})
     * @ORM\JoinTable(name="bb_core_task_document")
     */
    private $documents;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=FALSE)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, nullable=FALSE)
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deadline", type="datetime", nullable=FALSE)
     */
    private $deadline;

    /**
     * @var
     *
     * @ORM\ManyToOne(targetEntity="Skill")
     * @ORM\JoinColumn(name="skill_id", referencedColumnName="id", nullable=FALSE)
     */
    protected $skill;

    /**
     * @var
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id", nullable=FALSE)
     */
    protected $creator;

    /**
     * @var
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", nullable=FALSE)
     */
    protected $owner;

    /**
     * @var
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="resource_id", referencedColumnName="id")
     */
    protected $resource;

    /**
     * @ORM\OneToMany(targetEntity="LogEntry", mappedBy="task", cascade={"remove"})
     */
    private $logEntries;

    /**
     * @ORM\OneToMany(targetEntity="Calculation", mappedBy="task", cascade={"remove"})
     */
    private $calculations;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->documents = new ArrayCollection();
        $this->logEntries = new ArrayCollection();
        $this->calculations = new ArrayCollection();
    }

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
     * Set title
     *
     * @param string $title
     *
     * @return Task
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Task
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set deadline
     *
     * @param \DateTime $deadline
     *
     * @return Task
     */
    public function setDeadline($deadline)
    {
        $this->deadline = $deadline;

        return $this;
    }

    /**
     * Get deadline
     *
     * @return \DateTime
     */
    public function getDeadline()
    {
        return $this->deadline;
    }

    /**
     * Set resource
     *
     * @param \Bb\Bundle\Workflow\CoreBundle\Entity\User $resource
     *
     * @return Task
     */
    public function setResource(\Bb\Bundle\Workflow\CoreBundle\Entity\User $resource = null)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Get resource
     *
     * @return \Bb\Bundle\Workflow\CoreBundle\Entity\User
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Returns the string representation of this object.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getTitle();
    }
}
