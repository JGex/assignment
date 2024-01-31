# Requirements

- [Docker](https://www.docker.com/)


# Installation

You must create a `.env` file
```bash
cp .env.example .env
```

then, check if the default values fit to your needs

You can change the default ports used if the port 80 or 3306 is already used on your machine
```env
APP_PORT=80
FORWARD_DB_PORT=3306
```

If your user GID is not 1000, you can change it to prevent writing error, you can find it by running `` id -g `whoami` ``
```env
WWWGROUP=1000
```

Run this command to start the containers
```bash
make sail-up
```

Then install the dependencies
```bash
make composer CMD="install"
```


# Containers

A Makefile is provided to help the user using application's commands

To start the containers
```bash
make sail-up
```

If you want to stop the containers
```bash
make sail-down
```

If you want to see the docker's log
```bash
make sail-log
```

This will display the last 20 lines of the dockers' logs

You can add those vars to see a specified docker or change the number of lines displayed : 
```bash
TAIL="20" # to display a specific amount of lines
CONTAINER="laravel.test" # to display logs related to laravel container
```


# Usage
## API calls

When the docker is ready, you can call the API at this link: http://localhost  
If you change the env var `APP_PORT=8080` you need to specify it at the end of the URL, like this : http://localhost:8080  


## Commands

The Makefile can help you to easily run Laravel command, here is the must usefull

To list all the available commands used with artisan
```bash
make php-artisan CMD="list"
```

To run one of them : 
```bash
make php-artisan CMD="cache:clear"
```

Note the `CMD` var usage, it's mandatory to pass args into _make_ if you add arg without it, make will try to run rules for all arguments  
For exemple, this command will stop, then restart the docker :
```bash
make sail-stop sail-up
```

To see all the available command just run `make` in your favorite terminal to display the help


# Feature RoadMap

If we are planning to update the project, here are some possible improvements

- Improve ApiClient to allow other http methods, only get is actually allowed
- Improve product's image on import, download it, check if it's a valid image, store it locally
- Add a logger decorator to add a _context_ and _domaine_ for the log, it will help is the log is parsed in a monitoring tool for exemple
