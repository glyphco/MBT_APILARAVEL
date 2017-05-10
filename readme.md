# MBT_API

This is the API server for MBT.  Created in December 2016 (the year of the suck). Switched BACK to Laravel... This is the NEW OLD hotness


## Getting Started

Why do i need to tell you?

### Prerequisites (Homestead setup)

setup yo webserver:

```
    - map: mbtapi.dev
      to: /home/vagrant/Code/[THISPACKAGEDIR]/public

```
and a database

```
    - mbt

```

### Installing


```
Composer Update
```


```
php artisan migrate
```

set up your .env with these items:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mbt
DB_USERNAME=homestead
DB_PASSWORD=secret
```

## Authors

* **Shawn Dalton** - *All the work so far* 


## Acknowledgments

* Jefferson
* Tessa and Samantha