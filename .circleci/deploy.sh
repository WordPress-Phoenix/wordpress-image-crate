#!/bin/bash

# Check that required variables are all set correctly
VARIABLES_SET() {
	[ -z "$SSH_USER" ] && echo "SSH_USER variable is not add set correctly" && exit 0

	[ -z "$SSH_HOST" ] && echo "SSH_HOST variable is not add set correctly" && exit 0

	[ -z "$PLUGIN_NAME" ] && echo "PLUGIN_NAME variable is not add set correctly" && exit 0

	[ -z "$PLUGINS_PATH" ] && echo "PLUGINS_PATH variable is not add set correctly" && exit 0

	[ -z "$PLUGINS_BACKUP_PATH" ] && echo "PLUGINS_BACKUP_PATH variable is not add set correctly" && exit 0
}

VARIABLES_SET

PRODUCTION_PLUGIN_PATH=$PLUGINS_PATH$PLUGIN_NAME
BACKUP_PLUGIN_PATH=$PLUGINS_BACKUP_PATH$PLUGIN_NAME

ssh -o StrictHostKeyChecking=no $SSH_USER@$SSH_HOST 'exit'

# Install rsync if not availible
if ! [ -x "$(command -v rsync)" ]; then
	echo ">> Installing rsync..."
	sudo apt install rsync
	echo ">> Rsync install successful!"
fi

echo ">> Transferring files to temp directory on server..."
rsync -az --force --delete --progress --exclude 'node_modules' --exclude '.git/' ./ $SSH_USER@$SSH_HOST:$PRODUCTION_PLUGIN_PATH-temp
echo ">> Transfer complete."

echo ">> Backing up pervious version of plugin..."
ssh -o StrictHostKeyChecking=no $SSH_USER@$SSH_HOST "rm -rf $BACKUP_PLUGIN_PATH &&
cp -r $PRODUCTION_PLUGIN_PATH $BACKUP_PLUGIN_PATH"
echo ">> Backup complete."

echo ">> Deploying files to production..."
ssh -o StrictHostKeyChecking=no $SSH_USER@$SSH_HOST "rm -rf $PRODUCTION_PLUGIN_PATH && mv -f $PRODUCTION_PLUGIN_PATH-temp $PRODUCTION_PLUGIN_PATH"
echo ">> Deploy complete!"