TLD API
=======

This application expose an API endpoint for all other applications.

Install
-------

Create keys needed for JWT

If you have openssl, then follow instructions in the "Security" section.
Else (only in dev or test environment), you can use those distributed with the project

```
$ bin/install-keys.sh
```

Install dependencies:

```
$ composer install
```

Synchronize Legacy resources with the new schema:

```
$ app/console legacy:import:all
```

Tests
-----

```
$ ./bin/test.sh
```

Security
--------

This is API is protected using the [LexikJWTAuthenticationBundle](https://github.com/lexik/LexikJWTAuthenticationBundle).

For security reasons, you must generate dedicated keys for each environments:
```
openssl genrsa -out /MY/SECURED/PATH/private.pem -aes256 4096
openssl rsa -pubout -in /MY/SECURED/PATH/private.pem -out /MY/SECURED/PATH/public.pem
```
(Replace `/MY/SECURED/PATH` by a real path on the filesystem)

Then update your file `parameters.yaml`, or you can use environment variables (`JWT_PRIVATE_KEY_PATH`, `JWT_PUBLIC_KEY_PATH`, `JWT_KEY_PASS_PHRASE`)
to override default parameters defined in `parameters.yaml.dist`.


Deploy
------

Deployment is managed by gitlab-ci throught the `.gitlab-ci.yaml` file
However, the server have to be "ready"
 - Configure the environment variables in `/etc/environment`
 - Configure the file `parameters.yaml`
 - Configure the httpd vhost
 - Generate jwt token :
    - cd `/var/www/api.tld-group.com/shared/app/Resources/jwt/`
    - openssl genrsa -out private.pem -aes256 4096
    - openssl rsa -pubout -in private.pem -out public.pem

```
RewriteEngine On

RewriteCond %{HTTP:Authorization} .
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ app.php [QSA,L]
```

TODO
----

* Due to a bug in Doctrine [#1546](https://github.com/doctrine/doctrine2/pull/1546), We have to disable the fetch="EAGER"
  for the field Position::level. When the PR is merged, we should reactivate the feature to optimize read query.
* Security:
    - check acl to read/write resources
    - check acl when adding a comment to a given resource (assert the resource exists)
* Comments/Logs can be public/private
