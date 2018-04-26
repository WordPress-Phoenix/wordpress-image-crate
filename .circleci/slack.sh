#!/bin/bash

# Check that required variables are all set correctly
VARIABLES_SET() {
	[ -z "$CIRCLE_PROJECT_USERNAME" ] && echo "CIRCLE_PROJECT_USERNAME variable is not add set correctly" && exit 0

	[ -z "$CIRCLE_PROJECT_REPONAME" ] && echo "CIRCLE_PROJECT_REPONAME variable is not add set correctly" && exit 0

	[ -z "$CIRCLE_TAG" ] && echo "CIRCLE_TAG variable is not add set correctly" && exit 0

	[ -z "$CIRCLE_USERNAME" ] && echo "CIRCLE_USERNAME variable is not add set correctly" && exit 0

	[ -z "$NEW_RELIC_ACCOUNT_ID" ] && echo "NEW_RELIC_ACCOUNT_ID variable is not add set correctly" && exit 0

	[ -z "$NEW_RELIC_APPLICATION_ID" ] && echo "NEW_RELIC_APPLICATION_ID variable is not add set correctly" && exit 0

	[ -z "$SLACK_WEBHOOK" ] && echo "SLACK_WEBHOOK variable is not add set correctly" && exit 0
}

VARIABLES_SET

REPO_NAME=$CIRCLE_PROJECT_USERNAME'/'$CIRCLE_PROJECT_REPONAME
REPO_URL="https://github.com/$CIRCLE_PROJECT_USERNAME/$CIRCLE_PROJECT_REPONAME"
TAG=$CIRCLE_TAG
AUTHOR_NAME=$CIRCLE_USERNAME
AUTHOR_LINK="https://github.com/$CIRCLE_USERNAME"
AUTHOR_ICON="https://github.com/$CIRCLE_USERNAME.png"
RELEASE_NOTES=$(git log --format="%B" -n 1)

PAYLOAD='{
  "channel": "#fansided-releases",
  "username": "CircleCi Deployment",
  "icon_emoji": ":circleci:",
  "link_names": 1,
  "attachments": [
	{
	  "fallback": "Released '$REPO_NAME' version '$TAG'",
	  "color": "#36a64f",
	  "author_name": "fansided - '$AUTHOR_NAME'",
	  "author_link": "'$AUTHOR_LINK'",
	  "author_icon": "'$AUTHOR_ICON'",
	  "title": "Released '$REPO_NAME' version '$TAG'",
	  "title_link": "'$REPO_URL'",
	  "text": "'$RELEASE_NOTES'",
	  "footer": "Pagely Production Deployment",
	  "footer_icon": "https:\/\/s3-us-west-2.amazonaws.com\/slack-files2\/avatars\/2016-03-15\/26963954738_9e0d7b2047b49f4121c9_68.png",
	  "ts": '$(date +"%s")'
	},
	{
	  "text": "https:\/\/rpm.newrelic.com\/accounts\/'$NEW_RELIC_ACCOUNT_ID'\/applications\/'$NEW_RELIC_APPLICATION_ID'"
	}
  ]
}'

curl -X POST \
  $SLACK_WEBHOOK \
  -H "cache-control: no-cache" \
  -H "content-type: application/json" \
  -d "$PAYLOAD"