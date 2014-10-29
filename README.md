Code sample project
========================

This repository contains a simplified selection of a real life Symfony project. The original project is a web-based workflow app and is currently in production by > 5000 users. In this code sample, no GUI is included. Instead, you may run the unit tests provided to verify that the code works.

The intended audience of this repository is code reviewers and technical recruiters.

The files that I think you should look at
--------------------------------
For an assessment of the code quality in this repository, please review in particular the following files:

1.  [The ```TaskWorkflowVoter``` class](src/Bb/Bundle/Workflow/CoreBundle/Security/Voter/TaskWorkflowVoter.php).
2.  [The unit test for the ```TaskWorkflowVoter``` class](src/Bb/Bundle/Workflow/CoreBundle/Tests/Security/Voter/TaskWorkflowVoterTest.php).
3.  [The ```Task``` entity](src/Bb/Bundle/Workflow/CoreBundle/Entity/Task.php).
4.  [The ```User``` entity](src/Bb/Bundle/Workflow/CoreBundle/Entity/User.php).

Description of the original project
---------------------------------
The original app is a workflow tool intended for groups of linguists working on document-centered tasks.

What this code sample contains
-----------------------------------
In this code sample, two Doctrine Entities are implemented: users and tasks. Users may have one of four roles: super, admin, resource, and client. In the original app, admins may assign tasks to resources. Resources complete tasks, after which admins review their work and send it off to the assigned client. Clients can log in to the app to download the finished work.Supers are admins with some extra privileges.

Tasks have one of five statuses:

1.  **New**. The task has just been created in the system.
2.  **Assigned**. The task has been assigned to a resource.
3.  **Started**. The task has been accepted by the resource and is currently being worked on.
4.  **Finished**. The task is finished by the resource.
5.  **Sent**. The task has been sent to the client.
6.  **Archived**. The task is archived.

This code sample includes a [```TaskWorkflowVoter```](src/Bb/Bundle/Workflow/CoreBundle/Security/Voter/TaskWorkflowVoter.php) 
class which checks whether or not a given user is allowed to change the status of a task.


Prerequisites
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
To install this code sample, clone this repository on your development box:

```
$ git clone https://github.com/mauzeh/showcase.git
```

Then, navigate to the top directory of the repository,

```
$ cd showcase/
```

And get the dependencies:

```
$ composer install
```

You will be asked a few questions about your development box set-up. Feel free to leave the default values as they are because you only need to run the unit tests which do not require any database connections, e-mail settings or other configuration.

To run the tests, stay in the top directory of the repository and run:

```
$ phpunit -c app
```
