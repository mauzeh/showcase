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

namespace Bb\Bundle\Workflow\CoreBundle;

/**
 * Class BbWorkflowCoreEvents
 */
final class BbWorkflowCoreEvents
{
    const TASK_NEW = 'workflow.task.new';
    const TASK_ASSIGN = 'workflow.task.assign';
    const TASK_REJECT = 'workflow.task.reject';
    const TASK_START = 'workflow.task.start';
    const TASK_FINISH = 'workflow.task.finish';
    const TASK_UNFINISH = 'workflow.task.unfinish';
    const TASK_SEND = 'workflow.task.send';
    const TASK_ARCHIVE = 'workflow.task.archive';
    const TASK_UNARCHIVE = 'workflow.task.unarchive';
}
