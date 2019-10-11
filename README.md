# Repo Rangler: NPM Service

This docker service will mimic a NPM repository, but a lot more powerful and flexible.

# Installation

You should be able to resolve `npm.reporangler.develop` locally on your computer. 
Maybe you need to edit `/etc/hosts` file or add to dns?

At first I had instructions which let people decide whether you want to run the manual way or the preconfigured way. 
The problem is that it becomes really hard to explain correctly to everybody how to do this. So I decided to provide
only one way to do this and you can decide whether you want to follow it or not.

### 1. Configure the frontend proxy to receive all requests

The easiest and probably best way to run the services side by side on the same machine is to use the `jwilder/nginx-proxy`

The reason why you'd want a frontend proxy, is that your machine only has a single port 80. But we run multiple webservices
all running on the default port 80. Which gives us a problem. A way around this is to use the docker-compose files in combination
with the frontend proxy described here. It automatically configures the frontend proxy to hook up the container without
any complex configuration.

This proxy listens on the docker socket for container start/stops and scans the environment variables, looking for recognised
configuration parameters it can use to auto-configure the routing/upstream connections between your host machine and the docker container

If you don't want to use this, then I'm afraid you'll need to reconfigure everything to work with your desired setup. But since
you're an advanced user. I will let you do that however you want.

```
network_name=repo_rangler_proxy
docker_image=christhomas/nginx-proxy:alpine
docker network create ${repo_rangler_proxy}
docker run -d --restart always --network=${repo_rangler_proxy} --name=${repo_rangler_proxy} -p 80:80 -p 443:443 -v /var/run/docker.sock:/tmp/docker.sock:ro ${docker_image}
```

### 2. Run Docker Compose
Now once the proxy is up and running, we can run the containers that will be accessible via the proxy

```
docker-compose stop
docker-compose rm -f
docker-compose build
docker-compose up
```
NOTE: This command optionally can take the `-d` parameter if you want to run in the background

#### 3. See whether they are running
docker ps

#### 4. Query the container to see what it replies
```
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
