# Simple Product API

This is a simple API used to get or update a list of products. 
The database need to be synchronized with a specific source.


## Installation
### Requirements

- [Docker](https://www.docker.com/) is the only requirement


### Configuration

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

If your user UID or GID is not 1000, you can change them to prevent permission issue,
you can find them by running `` id -u `whoami` `` or `` id -g `whoami` ``
```env
WWWGROUP=1000
WWWUSER=1000
```

### Initialisation

To initialize to project, you need to run this command.  
It will download a temporary docker use to install the dependencies
```bash
make init
```

## Usage

A Makefile is provided to help the developer using application's commands.  
Make do not accept arguments, else you must pass variable with the command

### Containers

Start the containers
```bash
make sail-up
```

Stop the containers
```bash
make sail-down
```

Show the docker's log  
This will display the last 20 lines of log from all the containers
```bash
make sail-log
```

You can add those vars to see a specified docker or change the number of lines displayed :
```bash
TAIL="20" # to display a specific amount of lines
CONTAINER="laravel.test" # to display logs related to laravel container
```

### PHPDdoc Generation

A tool is provided to add PHPDoc comment on models, facade and PhpStorm meta.  
Actually, only PHPDoc models are generated.  
More details [here](https://github.com/barryvdh/laravel-ide-helper)

To generate PHPDoc model, run
```bash
make sail-artisan CMD="ide-helper:models -W"
```


### APIDoc

You can generate an API-doc, to do it run this command  
Then it will be available here http://localhost/api/documentation
```bash
make sail-artisan CMD="l5-swagger:generate"
```
More details [here](https://github.com/DarkaOnLine/L5-Swagger)


### Command

There are two available commands


#### product:import

Actually, there is only one service available _FakeStore_
```bash
make sail-artisan CMD="product:import FakeStore"
```
It will create products according to the FakeStoreApi model  
It the products are already imported, they will be synchronized  


#### user:token:upsert

If you want to use the API you need a bearer token. To generate one, you need tu run this command

```bash
make sail-artisan CMD="user:token:upsert user@mail.com"
```
The prompte will give you a usable token  
The token is regenerated if the user is already created


#### API

You can call the API at this link: http://localhost  
If you change the env var `APP_PORT=8080` you need to specify it at the end of the URL, like this: http://localhost:8080  
To see the routes available, check the APIDoc section

To fetch the API, you need a bearer token. see the _user:token:upsert_ section

example to fetch the product list : 

```bash
curl -X 'GET' \
  'http://localhost/api/product' \
  -H 'Authorization: Bearer TtMm0W7JFL9VIgzPpr05A6v7hSmumh89YPS0q8YO1944cb2d'
```

# Feature RoadMap

If we are planning to update the project, here are some possible improvements

- Improve ApiClient to allow other http methods, only get is actually allowed
- Improve product's image on import, download it, check if it's a valid image, store it locally
- Add a logger decorator to add a _context_ and _domaine_ for the log, it will help if the log is parsed in a monitoring tool
- Add cache on the product list, revoked by the update route and the synchronize command
