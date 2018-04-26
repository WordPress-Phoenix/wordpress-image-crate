#!/bin/bash

# Check that required variables are all set correctly
VARIABLES_SET() {
	[ -z "$CIRCLE_PROJECT_USERNAME" ] && echo "CIRCLE_PROJECT_USERNAME variable is not add set correctly" && exit 0

	[ -z "$CIRCLE_PROJECT_REPONAME" ] && echo "CIRCLE_PROJECT_REPONAME variable is not add set correctly" && exit 0

	[ -z "$CIRCLE_TAG" ] && echo "CIRCLE_TAG variable is not add set correctly" && exit 0

	[ -z "$CIRCLE_USERNAME" ] && echo "CIRCLE_USERNAME variable is not add set correctly" && exit 0

	[ -z "$NEW_RELIC_WEBHOOK" ] && echo "NEW_RELIC_WEBHOOK variable is not add set correctly" && exit 0

	[ -z "$NEW_RELIC_API_KEY" ] && echo "NEW_RELIC_API_KEY variable is not add set correctly" && exit 0
}

VARIABLES_SET

REPO_NAME=$CIRCLE_PROJECT_USERNAME'/'$CIRCLE_PROJECT_REPONAME
REPO_URL="https://github.com/$CIRCLE_PROJECT_USERNAME/$CIRCLE_PROJECT_REPONAME"
TAG=$CIRCLE_TAG
AUTHOR_LINK="https://github.com/$CIRCLE_USERNAME"
RELEASE_NOTES=$(git log --format="%B" -n 1)

PAYLOAD='{
  "deployment": {
	"revision": "'$REPO_NAME' v'$TAG'",
	"changelog": "'$REPO_URL'",
	"description": "'$RELEASE_NOTES'",
	"user": "'$AUTHOR_LINK'"
  }
}'

curl -X POST \
  $NEW_RELIC_WEBHOOK \
  -H 'cache-control: no-cache' \
  -H 'content-type: application/json' \
  -H 'x-api-key: '$NEW_RELIC_API_KEY'' \
  -d "$PAYLOAD"