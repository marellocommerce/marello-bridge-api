Marello Api Extension
=============

Marello Api extension is a wrapper which enables the applications to communicate with an
instance of Marello. This wrapper only provides authentication with X-WSSE headers and allows you to send or get data from a Marello Application instance.

The extension is based on the work Sylvain Rayé has done back in 2014, where he explains how to connect to an OroCRM/Akeneo instance with this method.
The extension uses a lot of work done by Sylvain Rayé regarding the X-WSSe authentication but adds a Client into it to make the create and send the calls to an instance.
The original post can found  on [Sylvain Rayé's blog](http://www.sylvainraye.com/2014/03/23/using-the-rest-api-of-oroplatform-orocrm-akeneo/).
Oro's cookbook now includes an [instruction](https://github.com/orocrm/documentation/blob/master/cookbook/how_to_use_wsse_authentication.rst) on how to generate the X-WSSE in an Oro based platform

## Features
- Pinging the Marello instance (\Marello\Api\Client::pingInstance());
- Getting data from Marello, including page/limit filter;
- Sending data to Marello with POST/PUT/DELETE requests

**Future Features**
- Disable denpendency from Marello Bridge on this wrapper for swapping out this wrapper for, for example GuzzleHttp

## Requirements

* PHP 5.5.0 or above with command line interface

## Installation instructions

In order to get the Bridge Api, you can easily install this through composer

```bash
composer require "marellocommerce/marello-bridge-api"
```

- Install dependencies with composer. If installation process seems too slow you can use `--prefer-dist` option.

```bash
composer install --prefer-dist --no-dev
```

## Usage
for usage see [docs/USAGE.md](doc/USAGE.md)

## Contact
Questions? Problems? Improvements?

Feel free to contact us either through [http://www.marello.com/contact/](http://www.marello.com/contact/), forum [http://www.marello.com/forum/marello/](http://www.marello.com/forum/marello/) or open an issue in the repository :) Thanks!