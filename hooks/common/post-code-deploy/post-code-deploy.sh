#!/bin/bash
#
# Cloud Hook: post-code-deploy
#
# The post-code-deploy hook is run whenever you use the Workflow page to
# deploy new code to an environment, either via drag-drop or by selecting
# an existing branch or tag from the Code drop-down list. See
# ../README.md for details.
#
# Usage: post-code-deploy site target-env source-branch deployed-tag repo-url
#                         repo-type

set -ev

site="$1"
target_env="$2"
source_branch="$3"
deployed_tag="$4"
repo_url="$5"
repo_type="$6"

# Prep for BLT commands.
repo_root="/var/www/html/$site.$target_env"
export PATH=$repo_root/vendor/bin:$PATH
cd $repo_root


# Default site.
drush cr
drush updb -y
drush cim -y
drush cr

# Bangladesh domain.
drush cr -l bn
drush updb -y -l bn
drush cim -y -l bn
drush cr -l bn

# Zimbabwe domain.
drush cr -l zw
drush updb -y -l zw
drush cim -y -l zw
drush cr -l zw

# Sierra Leone domain.
drush cr -l sl
drush updb -y -l sl
drush cim -y -l sl
drush cr -l sl

# Turkey Cross Border domain.
drush cr -l txb
drush updb -y -l txb
drush cim -y -l txb
drush cr -l txb

set +v
