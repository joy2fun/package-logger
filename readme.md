
Installation

```sh
composer global require joy2fun/package-logger
```

Configuration locally

```sh
composer config extra.package-logging-url {URL}
```

Usage

```sh
composer require psr/log:1.0.0
```

This would send a POST request to `{URL}` with following JSON request body

```json
{
  "packages":{
    "psr/log":"1.0.0"
  }
}
```