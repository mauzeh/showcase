[![Build Status](https://api.travis-ci.org/mauzeh/showcase-symfony.svg?branch=master)](https://travis-ci.org/mauzeh/showcase-symfony)

Code sample project
========================

This repository contains a simplified selection of a real life Symfony project. The original project is a web-based workflow app and is currently in production by > 5000 users. In this code sample, no GUI is included. Instead, you may run the unit tests provided to verify that the code works.

The intended audience of this repository is code reviewers and technical recruiters.

The files that I think you should look at
--------------------------------
For an assessment of the code quality in this repository, please review in particular the following files:

1.  [The Task-related events](src/Bb/Bundle/Workflow/CoreBundle/Event/), modeled separately (outside of the [Entity implementations](src/Bb/Bundle/Workflow/CoreBundle/Entity/)) using the Doctrine ```preFlush``` event.
2.  [The ```TaskWorkflow``` security voter classes](src/Bb/Bundle/Workflow/CoreBundle/Security/Voter/TaskWorkflow/).
3.  [The unit tests for the ```TaskWorkflow``` security voter classes](src/Bb/Bundle/Workflow/CoreBundle/Tests/Security/Voter/TaskWorkflow/).

The original project
---------------------------------
The original app is a workflow tool intended for groups of linguists working on document-centered tasks.

This code sample
-----------------------------------
In this code sample, two Doctrine Entities are implemented: users and tasks. Users may have one of four roles: super, admin, resource, and client. In the original app, admins may assign tasks to resources. Resources complete tasks, after which admins review their work and send it off to the assigned client. Clients can log in to the app to download the finished work. Supers are admins with some extra privileges.

Tasks have one of five statuses:

1.  **New**. The task has just been created in the system.
2.  **Assigned**. The task has been assigned to a resource.
3.  **Started**. The task has been accepted by the resource and is currently being worked on.
4.  **Finished**. The task is finished by the resource.
5.  **Sent**. The task has been sent to the client.
6.  **Archived**. The task is archived.

This code sample includes an [event subsystem](src/Bb/Bundle/Workflow/CoreBundle/Event/) and a [security voting subsystem](src/Bb/Bundle/Workflow/CoreBundle/Security/Voter/TaskWorkflow/). The event subsystem is the core of the workflow-related actions that are triggered throughout the app. The security voters check whether a certain task status change is allowed for a given user.

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
$ git clone https://github.com/mauzeh/showcase-symfony.git
```

Then, navigate to the top directory of the repository,

```
$ cd showcase-symfony/
```

And get the dependencies:

```
$ composer install
```

You will be asked a few questions about your development box set-up. Feel free to leave the default values as they are because you only need to run the unit tests which do not require any database connections, e-mail settings or other configuration.

To run the tests, stay in the top directory of the repository and run:

```
$ phpunit
```
