# Docker for TLD

## Install

* make sure that you don't have a local apache and/or mysql instance
* clone the repository along side the others projects
* run the command `make build`
* download the database backup from `webdbprod1.tld-america.com:/home/backup/mysql.1.tar.gz`
* import the `tld` dump
* run `make composer-install`
* update your symfony api configuration:
    * in `parameters.yml` (pointing to local Docker containers) to configure the local databases connection
    * copy `app/Resources/jwt/*.pem.dist` to `app/Resources/jwt/*.pem`
* update your legacy config in the `shared_php_inc` repository:
    * `config.credentials.inc.php` (pointing to local Docker containers) to configure the local databases connection
* run `make import-legacy`
    * run `yarn-install`
* run `make webpack-deploy`
* open the website in your browser at for example `http://localhost/en/private`


## Usage

Use your default DNS server (configured for docker) to access the contains

 * api <= contains the api application
 * gse <= contains the intranet application
 * smtp <= contains an instance of MailHog: A smtp stub
 * mariadb <= contains an instance of mariadb with the databases

## Makefile commands

Run `make help` to read the auto-generated documentation of the Makefile targets

## CI/CD Pipeline

Pipeline is only launched in threes cases:
- on merge requests,
- or on tags,
- or on `$default_branch` (master).

When pipeline is launched by a merge request, tests will be launched only on changes versus the target branch.
- no changes : no pipeline
- intranet changes : intranet tests
- admin changes : admin tests
- api changes : api tests, and test of all other components using the api (intranet...)

### Suggested workflow

If your feature on intranet needs a new API :
- create a branch for your API feature, create a merge request to master to check that your API makes no regressions on API test nor on all other tools using the API.
- then create a branch for your intranet feature from your api feature branch, and create a merge request from you intranet feature to your api feature. The pipeline will now only run you intranet tests.
- merge your intranet branch on your api branch
- merge your api branch on main

