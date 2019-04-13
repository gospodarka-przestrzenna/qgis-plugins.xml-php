Do you want to create own QGIS plugin repository ?
===================================================

This repository contains php-based solution for recreate user plugin repository.  
Each time plugin GIT webhook is triggered with release, new version appears in repository and is ready to dowload and install to end user.

idea
-----
The plugin repository (metadata.txt) contains all necesarry data to publish plugin in user plugin repository.
Each new release shall update user plugin repository XML info with proper data. 

configuration
--------------
It is strongly recommended that only `release` event is set to fire webhook.
If You would like to configure webhook secret it must appear in `$githubhook_secret` variable in index.php 
The index.php must be accesible via request to  `http://somwhere.example.com/someting/` URL.

This URL should be provided to github webhooks `settings > webhooks > Add webhook`
as well as to QGIS `manage plugins > Settings > Add`




With each new release webhook is trigered and repository is updated
You can provide URL to more than one webhook. In this case more than one plugin will be aviliable in repository
The plugins zip files will be downloaded from plugin repository and striped with p
The plugin data provided in XML files 

