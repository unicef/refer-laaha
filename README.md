This is a Composer-based installer for the
[EzContent](https://www.drupal.org/project/ezcontent) Drupal distribution.

## Getting Started with Drupal 8 Installation

EzContent can be installed in two ways:

1. Via Drupal Composer

* Choose a name for your project, like “MY_PROJECT”
* Use the given command to create the project
* The command will download Drupal core along with necessary modules,
  EzContent profile, and all other dependencies necessary for the project
```bash
composer create-project srijanone/ezcontent-project:^1.0 MY_PROJECT --no-interaction
```

Note: In case you come across any memory issues, run this command -
```bash
php -d memory_limit=-1 /path/to/composer.phar create-project
srijanone/ezcontent-project:^1.0 MY_PROJECT --no-interaction
```

2. Via Acquia BLT

To create a new Acquia BLT project using EzContent, use the following commands
```bash
composer create-project --no-interaction acquia/blt-project MY_PROJECT;
cd MY_PROJECT;
composer require srijanone/ezcontent:^1.0;
```
Warning: There may be updates to BLT, Lightning which may break the setup. If
you see any issue, please open a new issue in the issue queue.

## Getting Started with Drupal 9 Installation

1. Via Drupal Composer

```bash
composer create-project srijanone/ezcontent-project:^2.0
MY_PROJECT --no-interaction
```
2. Via Acquia BLT

```bash
composer create-project --no-interaction acquia/drupal-recommended-project
MY_PROJECT;
cd MY_PROJECT;
composer require srijanone/ezcontent:^2.0.0-alpha1;
```

By using these commands you will get a EzContent site as your final output.
Login to your site using your database and webserver. Composer will install
all the required dependency into vendor folder. Always remember composer.json
and composer.lock files that exist they are controlling your dependencies.

## Upgrading from Drupal 8 to Drupal 9

Before upgrading, please make sure that the Drupal 9 environment requirements
are met and all the site-specific modules (required into root composer.json)
are D9 compatible.

Post that, please execute the following commands:

1. Drupal Composer Installation

```bash
# Require D9 compatible packages:
composer require drupal/core-composer-scaffold:~9.0.0 --no-update
 && composer require drupal/core-recommended:~9.0.0 --no-update
  && composer require drush/drush:^10 --no-update
   && composer require srijanone/ezcontent:^2.0.0-alpha1 --no-update;

# Update packages:
COMPOSER_MEMORY_LIMIT=-1 composer update --no-cache;

# Apply database updates:
drush updb;

# Rebuild cache:
drush cr;
```

2. Acquia BLT Installation

BLT 11 only supports Drupal 8, and BLT 12 only supports Drupal 9, meaning that
you must upgrade to Drupal 9 and BLT 12 simultaneously.

For upgrading from BLT 11 to BLT 12, please read: https://bit.ly/3wiojxI Please
ensure that you have followed the **Before upgrading** steps mentioned in the
link.

Next steps to upgrade:
```bash
# Remove blt-require-dev:
composer remove --dev acquia/blt-require-dev;

# Remove acquia/lightning as it is not required:
composer remove acquia/lightning;

# Require D9 compatible version of site specific modules eg. drupal/devel:
composer require drupal/devel:^4.1 --no-update;

# Require BLT 12.x and d9 compatible packages:
composer require acquia/blt:^12 --no-update
 && composer require drupal/core-composer-scaffold:~9.0.0 --no-update
  && composer require drupal/core-recommended:~9.0.0 --no-update;

# Require D9 compatible version of srijanone/ezcontent:
composer require srijanone/ezcontent:^2.0.0-alpha1 --no-update;

# Update the packages:
COMPOSER_MEMORY_LIMIT=-1 composer update --no-cache;
# If this step fails, ensure that you've first removed any incompatible modules
# as advised above.

# Apply database updates:
drush updb;

# Rebuild cache:
drush cr;
```

## Docksal Support
This project installer has [Docksal](https://docksal.io/) support.
