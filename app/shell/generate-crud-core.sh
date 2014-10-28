#!/bin/sh
app/console doctrine:generate:crud --with-write --no-interaction --overwrite --entity=BbWorkflowCoreBundle:Task
app/console doctrine:generate:crud --with-write --no-interaction --overwrite --entity=BbWorkflowCoreBundle:User
app/console doctrine:generate:crud --with-write --no-interaction --overwrite --entity=BbWorkflowCoreBundle:Skill
app/console doctrine:generate:crud --with-write --no-interaction --overwrite --entity=BbWorkflowCoreBundle:LogEntry

app/console doctrine:generate:crud --with-write --no-interaction --overwrite --entity=BbWorkflowCoreBundle:ProfileCore
app/console doctrine:generate:crud --with-write --no-interaction --overwrite --entity=BbWorkflowCoreBundle:ProfileTranslator