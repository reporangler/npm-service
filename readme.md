# Repo Rangler: NPM Service

This docker service will mimic a NPM repository, but a lot more powerful and flexible.

# Installation

You should be able to resolve `npm.reporangler.develop` locally on your computer. 
Maybe you need to edit `/etc/hosts` file or add to dns?

Run the following commands:
- You can change the network, but change it in all the commands
- You can change the port, but then obviously you need to change the curl command too
```
# Create two variables to make it easier to run
port=80
network=reporangler

# Create the docker network (this might already exist, it's safe to ignore errors if it does)
docker network create ${network}

# Create the containers
docker run --rm --network=${network} --name npm_service_phpfpm reporangler/npm_service_phpfpm
docker run --rm --network=${network} --name npm_service_nginx -p ${port}:80 reporangler/npm_service_nginx

# See whether they are running
docker ps

# Query the container to see what it replies
curl -vvv http://npm.reporangler.develop:${port}/healthz
```

It should output
```
> GET /healthz HTTP/1.1
> Host: npm.reporangler.develop
> User-Agent: curl/7.54.0
> Accept: */*
> 
< HTTP/1.1 200 OK
< Server: nginx/1.16.0
< Content-Type: application/json
< Transfer-Encoding: chunked
< Connection: keep-alive
< X-Powered-By: PHP/7.3.4
< Cache-Control: no-cache, private
< Date: Sat, 31 Aug 2019 17:32:06 GMT
< Access-Control-Allow-Origin: *.reporangler.develop
< Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS
< Access-Control-Allow-Credentials: true
< Access-Control-Allow-Headers: *
< 
* Connection #0 to host npm.reporangler.develop left intact
{"statusCode":200,"service":"http:\/\/npm.reporangler.develop"} 
```

# Usage

There are no usage instructions as yet

# Future Ideas 

None yet
