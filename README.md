# Unicef eRPW Drupal application

This is Unicef eRPW Drupal application based on EzConent Drupal distribution created by Srijan technologies PVT LTD

## RequirEment

1. Docker
2. Docksal https://docs.docksal.io/

## Installation

Simply clone this repo and run the following command from the repo root.

```bash
fin init
```

This will spin up the required development stack with docker and install the Drupal. The installation output will show the admin credential to login.

To see all available `fin` commands run:

```bash
fin help
```

This project uses composer to install all dependency and BLT to setup Drupal instance. **Docksal should be used to extend the available development tooling instead of manually installing any development tools. BLT should be used to setup drupal, run test and for deployment.**

Most of the Drupal development toolings are already provided by docksal. See https://docs.docksal.io/tools/ for more detail.

## Important Note

This project is created with composer with following command:

```bash
composer create-project srijanone/ezcontent-project:^2.0 MY_PROJECT --no-interaction
```

This project is not using `acquia/drupal-recommended-project` because that is based on acquia lightning profile and acquia lightning is going away. See [Frequently Asked Questions (FAQ) regarding End of Support for Acquia Lightning](https://support.acquia.com/hc/en-us/articles/1500006393601-Frequently-Asked-Questions-FAQ-regarding-End-of-Support-for-Acquia-Lightning)

## Debugging

Docksal comes with xdebug installed. Xdebug can be enabled by setting `XDEBUG_ENABLED=1` in `.docksal/docksal.env` file and restarting the project with `fin project restart`

## Code Linting

PHPCode sniffer

```
fin phpcs
```

Eslint

```
fin eslint
```

## Development tools

Run NPM

```
fin npm
```

Run NPX

```
fin npx
```

Run BLT

```
fin blt
```

Run Drush
```
fin drush
```

Run drupal console

```
fin drupal
```
