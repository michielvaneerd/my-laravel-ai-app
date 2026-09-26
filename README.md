# Laravel AI app inside Docker container with embedded PI Coding Agent

Running PI locally for a Laravel application that runs inside a Docker container can work, but has some serious drawbacks:

- PI doesn't have guardrails and can do everything on your system what you could do, like installing packages.
- PI doesn't run in the Laravel environment, so it has no access to things like `php artisan` without duplicating and installing packages locally that match the ones on the container.

That's why PI is running inside the Laravel container in this example.

The [Dockerfile](./Dockerfile) is to build the image:

```
docker build -t laravel-dev .
```


Run it like this:

```
docker run --rm -it -p 8080:80 -v "$PWD":/var/www/html laravel-dev
```

The [docker-compose.yml](./docker-compose.yml) file is used to mount the volumes and link the services. Some parts:

- `image: laravel-dev` - Depends on the image built above.
- Mount the PI volume with `- ~/.pi/agent_docker:/root/.pi/agent` - see below.

## PI

PI is running inside the container, so it has access to the complete Laravel application and it can use `php artisan` to do things.
But the LLM model is running on your host machine, so make sure that PI can cannot to it from within the Docker container.
To make this work:

Use a models.json file:

```
{
  "providers": {
    "ollama": {
      "baseUrl": "http://host.docker.internal:11434/v1",
      "api": "openai-completions",
      "apiKey": "ollama",
      "models": [
        {
          "id": "north-mini-code-1.0:q4_K_M"
        }
      ]
    }
  }
}
```

Note that we need to connect to `host.docker.internal` instead of `localhost`!

Add some things to the settings.json file:

```
{
  "lastChangelogVersion": "0.87.1",
  "theme": "light",
  "defaultProvider": "ollama",
  "defaultModel": "north-mini-code-1.0:q4_K_M"
}
```

Now you can enter the container and run PI like you would do locally:

```
docker exec -it CONTAINER_NAME bash
pi
```