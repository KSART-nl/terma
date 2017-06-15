#!/bin/sh

#Setup of Terma, chmod a+x setup.sh
mkdir frogs wikis

#Give user:group permissions to all files, better terma:nginx
sudo chown -R root:root *

#Give read/write permissions to frogs wikis
sudo chmod -R ug+rw frogs
sudo chmod -R ug+rw wikis

#Give read/write permissions to /stopwords/all.txt
sudo chmod ug+rw stopwords/all.txt

#Copy config file
#sudo cp config/env_example.php config/env.php

#CREATE DATABASE result_terms CHARACTER SET utf8 COLLATE utf8_unicode_ci;