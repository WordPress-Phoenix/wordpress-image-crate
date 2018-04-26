#!/bin/bash

# Check that required variables are all set correctly
VARIABLES_SET() {
	[ -z "$SSH_USER" ] && echo "SSH_USER variable is not add set correctly" && exit 1

	[ -z "$SSH_HOST_STAGING" ] && echo "SSH_HOST_STAGING variable is not add set correctly" && exit 1

	[ -z "$PLUGIN_NAME" ] && echo "PLUGIN_NAME variable is not add set correctly" && exit 1

	[ -z "$PLUGINS_PATH_STAGING" ] && echo "PLUGINS_PATH_STAGING variable is not add set correctly" && exit 1

	[ -z "$PLUGINS_BACKUP_PATH_STAGING" ] && echo "PLUGINS_BACKUP_PATH_STAGING variable is not add set correctly" &&
	exit 1
}

VARIABLES_SET

STAGING_PLUGIN_PATH=$PLUGINS_PATH_STAGING$PLUGIN_NAME
BACKUP_PLUGIN_PATH=$PLUGINS_BACKUP_PATH_STAGING$PLUGIN_NAME

ssh -o StrictHostKeyChecking=no $SSH_USER@$SSH_HOST_STAGING 'exit'

# Install rsync if not availible
if ! [ -x "$(command -v rsync)" ]; then
	echo ">> Installing rsync..."
	sudo apt install rsync
	echo ">> Rsync install successful!"
fi

echo ">> Transferring files to temp directory on server..."
rsync -az --force --delete --progress --exclude 'node_modules' --exclude '.git/' ./ $SSH_USER@$SSH_HOST_STAGING:$STAGING_PLUGIN_PATH-temp
echo ">> Transfer complete."

echo ">> Backing up pervious version of plugin..."
ssh -o StrictHostKeyChecking=no $SSH_USER@$SSH_HOST_STAGING "rm -rf $BACKUP_PLUGIN_PATH &&
cp -r $STAGING_PLUGIN_PATH $BACKUP_PLUGIN_PATH"
echo ">> Backup complete."

echo ">> Deploying files to production..."
ssh -o StrictHostKeyChecking=no $SSH_USER@$SSH_HOST_STAGING "rm -rf $STAGING_PLUGIN_PATH && mv -f $STAGING_PLUGIN_PATH-temp $STAGING_PLUGIN_PATH"
echo ">> Deploy complete!"