#!/bin/sh

#Setup of Terma, chmod a+x setup.sh
mkdir frogs wikis

#Give user:group permissions to all files
sudo chown -R terma:nginx *

#Give read/write permissions to frogs wikis
sudo cmod -R ug+rw frogs
sudo cmod -R ug+rw wikis

#Give read/write permissions to /stopwords/all.txt
sudo cmod ug+rw stopwords/all.txt

#Copy config file
sudo config/env_example.php config/env.php