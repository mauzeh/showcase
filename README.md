Code sample for review
========================

This repository contains a simplified selection of a real life Symfony project.
The original project is a web-based workflow app and is currently in 
production by > 5000 users. In this code sample, no GUI is included. Instead,
you may run the unit tests provided to verify that the code works.

The intended audience of this repository is code reviewers and technical
recruiters.

The original app is a workflow tool intended for groups of linguists working
on document-centered tasks. 

In this code sample, two Doctrine Entities are implemented: users and tasks. Users
may have one of four roles: super, admin, resource, and client. In the original app, admins
may assign tasks to resources. Resources complete tasks, after which admins
review their work and send it off to the assigned client. Clients can log in
to the app to download the finished work. 

Tasks have one of five statuses:

1.  **New**. The task has just been created in the system.
2.  **Assigned**. The task has been assigned to a resource.
3.  **Started**. The task has been accepted by the resource and is currently being worked on.
4.  **Finished**. The task is finished by the resource.
5.  **Sent**. The task has been sent to the client.
6.  **Archived**. The task is archived.

This code sample includes a TaskWorkflowVoter class which checks whether or not
a given user is allowed to perform a status transition of a task.

Interesting file to review
--------------------------------
For an assessment of the code quality in this repository, please review in 
particular the following files:

1.  [The ```TaskWorkflowVoter``` class](src/Bb/Bundle/Workflow/CoreBundle/Security/TaskWorkflowVoter.php)
2.  [The unit test for the ```TaskWorkflowVoter``` class](src/Bb/Bundle/Workflow/CoreBundle/Tests/Security/TaskWorkflowVoterTest.php)
3.  [The ```Task``` entity](src/Bb/Bundle/Workflow/CoreBundle/Entity/Task.php)
4.  [The ```User``` entity](src/Bb/Bundle/Workflow/CoreBundle/Entity/User.php)

Rerequisites
---------------------------------
[Composer](https://getcomposer.org/) is required to run this code. Install Composer by invoking:

```curl -sS https://getcomposer.org/installer | php```

[PHPUnit](https://phpunit.de/manual/current/en/installation.html) is required to run the tests. Install PHPUnit by invoking:

```
$ wget https://phar.phpunit.de/phpunit.phar
$ chmod +x phpunit.phar
$ sudo mv phpunit.phar /usr/local/bin/phpunit
$ phpunit --version
PHPUnit x.y.z by Sebastian Bergmann.
```

Installing and running the tests
----------------------------------
To install this code sample, navigate to the top directory of the repository and get the dependencies:

```
$ composer install
```

To run the tests,  navigate to the top directory of the repository and run:

```$ phpunit -c app```
