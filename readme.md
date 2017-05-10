# Products Microservice

[![Build Status](https://scrutinizer-ci.com/g/adamprescott/products-microservice/badges/build.png?b=master)](https://scrutinizer-ci.com/g/adamprescott/products-microservice/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/adamprescott/products-microservice/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/adamprescott/products-microservice/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/adamprescott/products-microservice/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/adamprescott/products-microservice/?branch=master)

## How to run

There are multiple ways you can run this project for local development.

First make sure to copy the contents of `.env.example` to `.env` before any of these steps. And Create a file under the `database` folder called `database.sqlite` if it does not already exist.

### PHP Built-in Server
If you plan on using PHP's built-in web server to test this do the following:

* Make sure you have composer installed and run `composer install` from the project's root.
* From the project's root, now run `php artisan migrate` this command will prepare the contents of the sqlite database.
* From the public directory, run `php -S 127.0.0.1:8080` this will spawn PHP's built-in web-server and will
allow you us `http://127.0.0.1:8080` to run your checks.

### Using vagrant
First make sure you have vagrant installed along with virtualbox.

* From the Project's root, run `vagrant up`
* Once the machine has finished provisioning run `vagrant ssh` to access the machine,
if you're on a Windows Box, you can just use PuTTY or "Bash on Ubuntu on Windows" with `ssh -p 2222 vagrant@127.0.0.1`
with password `vagrant`
* Once ssh'd in, `cd /var/www` and run `composer install`
* You should now be able to access the server at `http://192.168.33.10`

## API End-Points
A list of available API end-points and their methods.

### GET - `/v1/product`
**Returns a list of available products**

#### Sample Format - generated with `/v1/products?limit=3`
```
{
  "data": [
    {
      "PLU": "AAA",
      "name": "Random product AAA."
    },
    {
      "PLU": "AAB",
      "name": "Random product AAB."
    },
    {
      "PLU": "AAC",
      "name": "Random product AAC."
    }
  ],
  "meta": {
    "cursor": {
      "current": "MA%3D%3D",
      "prev": "MA%3D%3D",
      "next": "Mw%3D%3D",
      "count": 3
    }
  }
}
```

#### Available URL Parameters
* `limit` - **Default 500** - Allows you to limit the number of records returned per request. Part of the pagination functionality.
* `cursor` - Change which portion of the records to return, the `next` pointer can be found within the `meta.cursor` object.
Part of the Pagination functionality.

### GET - `/v1/product/{PLU}`
**Returns a product and its available SKUs and sizes**

#### Sample Format - generated with `/v1/product/AAF`
```
{
  "PLU": "AAF",
  "name": "Random product AAF.",
  "sizes": [
    {
      "SKU": "130",
      "size": "S"
    },
    {
      "SKU": "131",
      "size": "M"
    },
    {
      "SKU": "132",
      "size": "L"
    },
    {
      "SKU": "129",
      "size": "XL"
    },
    {
      "SKU": "133",
      "size": "XXL"
    }
  ]
}
```
If a product is not found, a 404 will be returned with a JSON object:
```
{"error": "Product PLU not found"}
```

There are no defined URL Parameters defined for this end-point.

### POST - `/v1/import`
**Expects the POST body to be a CSV with the following format. Invalid and duplicate records will be skipped**

#### Expected Format
```
sku,plu,name,size,size_type
```

#### Example
```
112, AAC, "Random product AAC.", "XXL", CLOTHING_SHORT
113, AAC, "Random product AAC.", "XXXXL", CLOTHING_SHORT
114, AAC, "Random product AAC.", "XS", CLOTHING_SHORT
107, AAB, "Random product AAB.", "40", SHOE_EU
108, AAB, "Random product AAB.", "INVALID SIZE", SHOE_EU
110, AAB, "Random product AAB.", "25", SHOE_EU
111, AAB, "Random product AAB.", "35", INVALID_TYPE
125, AAE, "Random product AAE.", "11 (Child)", SHOE_UK
126, AAE, "Random product AAE.", "1 ", SHOE_UK
127, AAE, "Random product AAE.", "9 (Child)", SHOE_UK
128, AAE, "Random product AAE.", "4.5 (Child)", SHOE_UK
128, AAE, "Duplicate Random product AAE.", "4.5 (Child)", SHOE_UK
```
Each result is trimmed for whitespace

#### Sample Result - Using above Example Input on an empty database
**Return code is 201**
```
{
  "total_imported": 9,
  "failed_total": 2,
  "skipped_total": 1,
  "failed": [
    {
      "row": [
        "108",
        " AAB",
        "Random product AAB.",
        "INVALID SIZE",
        " SHOE_EU"
      ],
      "error": "Invalid size of type SHOE_EU"
    },
    {
      "row": [
        "111",
        " AAB",
        "Random product AAB.",
        "35",
        " INVALID_TYPE"
      ],
      "error": "Invalid sizeSort"
    }
  ],
  "skipped": [
    [
      "128",
      " AAE",
      "Duplicate Random product AAE.",
      "4.5 (Child)",
      " SHOE_UK"
    ]
  ]
}
```

## Elasticbeanstalk Notes

A `.env` file isn't required for Elasticbeanstalk setups since we set the environment vars in `.ebextensions/01-commands.config`.

Ideally, the `.ebextensions` folder should be pulled in as a part of a build/deployment/pipeline process, 
not kept in the same repo as the code. This separates credentials from code.

The format of .ebextensions configs are very similar to CloudFormation, since CloudFormation is used behind Elasticbeanstalk.
ElasticBeanstalk configs are initially opinionated but also very flexible.

### Commands used to start up dev box
* `eb init -r eu-west-1 -k amz_adam -p php-5.6 Product-Microservice` - The `-k` option is the ssh key
you intend to use, change this to one that exists in your AWS account
* `eb create -s -i t2.micro product-dev` - The `-s` options just single machine without a load-balancer,
handy/cheaper for dev/qa environments

Further deployments can be done using `eb deploy`, this handles bringing instances in/out of service
and deploying code to those instances.

### .ebextensions
* `01-commands.config` - contains the environment configs and commands to deploy the application.
* `02-applicationLogs.config` - allows Laravel/Lumen application logs to be sync'd and rotated to S3.
It's also possible to enable CloudWatch Logs on this environment and have it watch these logs for application
specific errors.