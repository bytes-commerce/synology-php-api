# Symfony Synology NAS API Bundle

The Symfony Synology NAS API Bundle seamlessly integrates the Synology NAS API into your Symfony application. Leveraging
the [bytes-commerce/synology-php-api](https://packagist.org/packages/bytes-commerce/synology-php-api) package, this
bundle simplifies connecting to and managing your Synology NAS directly from your Symfony project.

## Features

- **Easy Integration:** Quickly add Synology NAS functionalities to your Symfony app.
- **Robust API Connection:** Use a well-tested API to interact with your Synology NAS.
- **Configuration Flexibility:** Customize connection parameters to fit your environment.
- **Symfony-Friendly:** Designed for seamless integration with the Symfony ecosystem.

## Requirements

- PHP 8.2 or higher
- Symfony 7.2 or higher
- Composer

## Installation

Install the bundle using Composer:

```bash
composer require bytes-commerce/synology-php-api
```

If you are using Symfony Flex, the bundle will be automatically registered. Otherwise, ensure the bundle is added in
your `config/bundles.php`.

## Configuration

There is no configuration that needs to be done, besides passing your credentials in any way you'd like to the
`RequestManagerFactory`. The bundle does everything else for you.

```php
use BytesCommerce\SynologyApi\Factory\RequestManagerFactory;

        $manager = $this->managerFactory->createManager(
            'https://<quickconnectUrl>.de99.quickconnect.to',
            '<username>',
            '<password>'
        );
```

## NAS Configuration

You need to create all Shares that you wish to work with first. Top-level shares seem not to be creatable by the bundle,
so if you want to write to a specific location that is not existing yet, you need to create it first. Besides Shares,
the API can create subfolders recursively.

For more advanced functionality, refer to
the [Synology PHP API documentation](https://github.com/bytes-commerce/synology-php-api).

## Contributing

Contributions to the Symfony Synology NAS API Bundle are welcome! To contribute:

1. Fork the repository.
2. Create a new feature branch.
3. Commit your changes.
4. Open a pull request with a description of your changes.

## License

This bundle is open-sourced software licensed under the [MIT License](LICENSE).

## Support

If you encounter any issues or have suggestions for improvements, please open an issue on
the [GitHub repository](https://github.com/bytes-commerce/synology-php-api).
