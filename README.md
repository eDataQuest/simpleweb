# Simple Website

How to install on a Mac

Make you have access to command line git.

Install docker(stable) on your mac

https://docs.docker.com/docker-for-mac/install/

open terminal

mkdir projects

cd projects

git clone https://github.com/eDataQuest/simpleweb.git

cd simpleweb

docker build -t edataquest/simpleweb .

docker run --rm --name simpleweb -p 80:80 -t -i edataquest/simpleweb

Open browser and browse to localweb




