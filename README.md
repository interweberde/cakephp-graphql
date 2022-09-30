# GraphQL plugin for CakePHP
This Plugin allows you to provide a GraphQL-Endpoint from your CakePHP-App.
Be aware though, that some effort is required to get your stuff working.

## Project Status
This Package was presented by @xyng at CakeFest 2022 and published shortly after.
Documentation is currently not available but we'll work on it!
While we do use an earlier version of this package in production, you should currently take on this as being **unstable**.

## Maintenance
We currently cannot guarantee maintenance for this plugin. If you'd like to help us out voluntarily, please reach out via email!

## Dependencies
Currently, this plugin depends on the following plugins:
- `cakephp/authentication` for injecting user and limiting api-access to authenticated users.
- `cakephp/authorization` to scope mapped queries.

## Installation
You can install this plugin into your CakePHP application using [composer](https://getcomposer.org).
The recommended way to install composer packages is:

```
composer require interweberde/cakephp-graphql
```

## Security
We ask you to report security issues as a `responsible disclosure` via email to security@interweber.de.
