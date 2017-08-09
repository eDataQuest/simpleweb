# simpleweb
Simple Website

How to install on a Mac

Install docker stable for mac

https://docs.docker.com/docker-for-mac/install/

open terminal

mkdir projects

cd projects

git clone https://github.com/eDataQuest/simpleweb.git

cd simpleweb

docker build -t edataquest/simpleweb .

docker run --rm --name simpleweb -p 80:80 -t -i edataquest/simpleweb

Open browser and browse to localweb




