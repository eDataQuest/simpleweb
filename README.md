# simpleweb
Simple Website

How to install on a Mac

open terminal

md projects

cd projects

git clone https://github.com/eDataQuest/simpleweb.git

cd simpleweb

docker build -t edataquest/simpleweb .

docker run --rm --name simpleweb -p 80:80 -t -i edataquest/simpleweb

Open browser and browse to localweb




