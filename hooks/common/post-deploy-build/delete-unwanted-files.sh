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

site="$1"
target_env="$2"
source_branch="$3"
deployed_tag="$4"
repo_url="$5"
repo_type="$6"

# In BLT 12 the artifact is build in /tmp/blt-deploy.
BASE_DIR="/tmp/blt-deploy"

# Drop files that are not needed on production, information leakage ~ security.
rm -rf "$BASE_DIR"/blt/scripts/git-hooks
rm "$BASE_DIR"/.git/hooks/commit-msg
rm "$BASE_DIR"/.git/hooks/pre-push
rm "$BASE_DIR"/.git/hooks/pre-commit
rm -r "$BASE_DIR"/docroot/modules/contrib/ctools/tests
rm -r "$BASE_DIR"/docroot/modules/contrib/metatag/tests
find "$BASE_DIR"/docroot/sites -name "default.local.settings.php" | xargs rm
rm "$BASE_DIR"/docroot/core/install.php
rm "$BASE_DIR"/docroot/core/authorize.php
rm "$BASE_DIR"/docroot/core/LICENSE.txt
rm "$BASE_DIR"/docroot/core/package.json
rm "$BASE_DIR"/docroot/core/phpcs.xml.dist
rm "$BASE_DIR"/docroot/core/phpunit.xml.dist
rm "$BASE_DIR"/docroot/modules/contrib/search_api_solr/*.txt
rm "$BASE_DIR"/docroot/modules/contrib/search_api/*.txt
rm -r "$BASE_DIR"/docroot/modules/contrib/search_api_solr/tests
rm -r "$BASE_DIR"/docroot/modules/contrib/search_api_autocomplete/tests
rm -r "$BASE_DIR"/docroot/scripts
rm "$BASE_DIR"/docroot/README.md
rm "$BASE_DIR"/README.md
rm -r "$BASE_DIR"/docroot/modules/contrib/migrate_plus/tests
rm -r "$BASE_DIR"/docroot/core/modules/update/tests
rm -r "$BASE_DIR"/docroot/core/tests
rm "$BASE_DIR"/docroot/update.php
