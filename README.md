MyBB-Redis-Cache
================

A [Redis](http://redis.io/) cache handler for [MyBB](http://www.mybb.com/).

Why?
----

I currently use Memcache to cache both the MyBB settings and preparsed versions of each post (To save processing time on
subsequent page loads of posts) however Memcache does not make it easy to clear items if you do not know the name of the
item (preparsed posts are stored as hashes) so this was built to offer more control over flushing items (and I've always
wanted an excuse to try Redis).

It may also be an idea to disable persistance for the Redis database (Use it purely as a in-memory cache) as MyBB will
store persistant caches in its database.

When I understand Redis better I'll add more flexibility to the configuration of it.

Requirements
----

* A Redis server
* The [phpredis](https://github.com/nicolasff/phpredis) extension
* The desire to use code that has not been thoroughly tested

Installation/Configuration
----

1. Merge the contents of the repository into your MyBB installation, making sure to overwrite `class_datacache.php`.

2. Edit `inc/config.php` and add the following:

    ~~~
    $config['redis']['host'] = '127.0.0.1';
    $config['redis']['port'] = '6379';
    ~~~

    Note that the port will default to 6379 if not specified.

3. Enable it by setting:
    ~~~
    $config['cache_store'] = 'redis';
    ~~~
    in `inc/config.php`.