Do you want to create own QGIS plugin repository ?
===================================================

This repository contains php-based solution to create QGIS user plugin repository.  
With each new plugin release GitHub webhook is triggered and the new version appears in repository. It is ready to dowload and install for end user.

idea
-----
The plugin repository (metadata.txt) along with released zips contains all necesarry data to publish plugin in user plugin repository.
Each new release shall update user plugin repository info with proper data. 

configuration
--------------
It is strongly recommended that only `release` event is set in webhook `index.php` does not implement reactions for other events.
If You would like to configure webhook secret it shall be provided to `$githubhook_secret` variable in `index.php` 

The `index.php` should be properly placed and accesible via request URL (example `http://somwhere.example.com/someting/`).

This URL should be provided to github webhooks `settings > webhooks > Add webhook` choose JSON payload format.
Set URL in QGIS repositories configuration `manage plugins > Settings > Add`as plugin repository (You can add tailing `/plugins.xml` if you wish)
You can provide URL to more than one webhook. In this case more than one plugin will be aviliable in repository

How it works
-------------

With each new release webhook is trigered and repository is updated via POST request.
The plugins zip files will be downloaded from github. The plugin repository takes care of proper rename.
Qgis which makes GET request recievs proper XML with plugin descriptions.
The local repository stores zips and plugins xml descriptions

Contributors 
-------------

Wiktor Żelazo, Maciej Kamiński
