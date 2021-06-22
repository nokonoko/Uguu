# What is Uguu?

Uguu is a simple lightweight file uploading and sharing platform, with the option for files to expire.

## Features

- One click uploading, no registration required
- A minimal, modern web interface
- Drag & drop supported
- Upload API with multiple response choices
  - JSON
  - HTML
  - Text
  - CSV
- Supports [ShareX](https://getsharex.com/) and other screenshot tools

### Demo

See the real world example at [uguu.se](https://uguu.se).

## Requirements

Original development environment is Nginx + PHP5.3 + SQLite, but is confirmed to
work with Apache 2.4 and newer PHP versions like PHP7.3.

## Install

A detailed installation and configuration can be found at [Uguu/Pomf Documentation](https://blog.yeet.nu/blog/uguu-docs).

## API
To upload using curl or make a tool you can post using: 
```
curl -i -F files[]=@yourfile.jpeg https://uguu.se/upload.php (JSON Response)
```
```
curl -i -F files[]=@yourfile.jpeg https://uguu.se/upload.php?output=text (Text Response)
```
```
curl -i -F files[]=@yourfile.jpeg https://uguu.se/upload.php?output=csv (CSV Response)
```
```
curl -i -F files[]=@yourfile.jpeg https://uguu.se/upload.php?output=html (HTML Response)
```

## Getting help

Hit me up at [@nekunekus](https://twitter.com/nekunekus) or email me at neku@pomf.se

## Credits

Uguu is based on [Pomf](http://github.com/pomf/pomf) which was written by Emma Lejack & Eric Johansson (nekunekus) and with help from the open source community.

## License

Uguu is free software, and is released under the terms of the Expat license. See
`LICENSE`.

## To do in release v1.1.0
* Mod feature and interface
* Blacklist DB (already exists on Uguu.se, but not in this code)
* Code cleanup
