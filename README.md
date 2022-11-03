# Native Cron

This package helps you to manage and modify your native *nix crontabs.

See also [`mintware-de/native-cron-bundle`](https://github.com/mintware-de/native-cron-bundle) if you're looking for a Symfony bundle.

## Features

- View / add / edit / delete system cron jobs
- View / add / edit / delete drop in cron jobs
- View / add / edit / delete user cron jobs
- Fully tested (except the adapter for native file system functions)
- Flexible / you can easily replace any important part of the package

## Installation
```bash
composer require mintware-de/native-cron
```

## Usage
```php
<?php

declare(strict_types=1);

use MintwareDe\NativeCron\Content\BlankLine;
use MintwareDe\NativeCron\Content\CommentLine;
use MintwareDe\NativeCron\Content\CronJobLine;
use MintwareDe\NativeCron\CrontabManager;
use MintwareDe\NativeCron\Filesystem\DarwinCrontabFileLocator;
use MintwareDe\NativeCron\Filesystem\FileHandler;

require_once __DIR__.'/vendor/autoload.php';

// Create a new crontab manager
$manager = new CrontabManager(
    // with a file locator for macOS; Use DebianCrontabFileLocator for debian based distros
    new DarwinCrontabFileLocator(),

    // and a default file handler
    new FileHandler(),
);

// Read the crontab for the user max
$crontab = $manager->readUserCrontab('max');

// Display the current content of the crontab
echo $crontab->build();

$crontab
    // Add a new cronjob
    ->add(new CronJobLine('* * * * * echo "Hello World" >> /tmp/mylog'))
    // Remove all comments and blank lines
    ->removeWhere(fn ($line) => $line instanceof CommentLine || $line instanceof BlankLine);

// Display the new content of the crontab
echo $crontab->build();

// Write the crontab for the user max
// Keep in mind that reading or writing crontab files may require higher user privileges.
$manager->writeUserCrontab($crontab, 'max');
```

## Compatibility matrix

| Feature           | Linux | macOS | Win |
|-------------------|:-----:|:-----:|-----|
| System Cron jobs  |  Yes  |  Yes  | No  |
| User Cron jobs    |  Yes  |  Yes  | No  |
| Drop-In Cron jobs |  Yes  |  No   | No  |


## Supported platforms
At the moment are Debian based distros and macOS supported. 
If you need to add support for a different platform, take a look at the [CrontabFileLocatorInterface](./src/Filesystem/CrontabFileLocatorInterface.php) and implement it for your platform.

