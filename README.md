# Uguu

[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=nokonoko_Uguu&metric=alert_status)](https://sonarcloud.io/dashboard?id=nokonoko_Uguu)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)

# What is Uguu?

Uguu is a simple lightweight temporary file hosting and sharing platform, but can also be used as a permanent file host.

## Features

- One click uploading, no registration required.
- A minimal, modern web interface.
- Drag & Drop & Paste supported.
- Upload API with multiple response choices:
    - JSON
    - HTML
    - Text
    - CSV
- Supports [ShareX](https://getsharex.com/) and other screenshot tools.
- File extension & MIME black/whitelisting.
- Upload rate limiting.
- File (hash) blacklisting.
- Run bare or as a container.

### Demo

See the real world site at [uguu.se](https://uguu.se). (down right now)

## Requirements

Tested and working with Nginx + PHP-8.1 + SQLite/MySQL/PostgreSQL.

Node is used to compile Uguu, after that it runs on PHP.

## Installation

Installation and configuration can be found at [Uguu Documentation](https://github.com/nokonoko/Uguu/wiki/Uguu-Configuration-&-Installation).

If you need a admin panel check out [Moe Panel](https://github.com/Pomf/MoePanel).

## Getting help

Hit me up at [@nekunekus](https://twitter.com/nekunekus) or email me at neku@pomf.se

## Contributing

We'd really like if you can take some time to make sure your coding style is
consistent with the project. Uguu follows [PHP
PSR-12](https://www.php-fig.org/psr/psr-12/) and [Airbnb JavaScript
(ES5)](https://github.com/airbnb/javascript/tree/es5-deprecated/es5) (`airbnb/legacy`)
coding style guides. We use ESLint and PHPCS tools to enforce these standards.

You can also help by sending us feature requests or writing documentation and
tests.

## Upcoming Features

* S3 Bucket Support
* Azure File Storage Support
* Temporal/RR Support

## Credits

Uguu is based on [Pomf](http://github.com/pomf/pomf) which was written by Emma Lejack & Go Johansson (nekunekus) and with help from the open source community.

## License

Uguu is free software, and is released under the terms of the GPL-3.0 license. See
`LICENSE`.
