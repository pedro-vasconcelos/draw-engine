# Draw Engine Laravel Package
[![Packagist Version](https://img.shields.io/packagist/v/pedro-vasconcelos/draw-engine?style=flat-square)](https://packagist.org/packages/pedro-vasconcelos/draw-engine)
[![GitHub](https://img.shields.io/github/license/pedro-vasconcelos/draw-engine?style=flat-square)](LICENSE)
[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/pedro-vasconcelos/draw-engine/run-tests?color=green&style=flat-square)](https://github.com/pedro-vasconcelos/draw-engine/actions)
[![Packagist Downloads](https://img.shields.io/packagist/dt/pedro-vasconcelos/draw-engine?color=green&style=flat-square)](https://packagist.org/packages/pedro-vasconcelos/draw-engine)

## Introduction

Introduction

This library has all the logic to support the Lucky Draw activity of several projects.

## TODO

## Install

```bash
php artisan vendor:publish --provider="PedroVasconcelos\DrawEngine\DrawServiceProvider" --tag="migrations"
php artisan migrate
php artisan vendor:publish --provider="PedroVasconcelos\DrawEngine\DrawServiceProvider" --tag="config"
# Check the config file and setup the models
# Add the trait `HasPrizeSchedule` to the model to access the schedule
php artisan db:seed --class="DrawsSeeder"
```

## Commands

```bash
php artisan app:create-prize-delivery-schedule 1
php artisan app:generate-winner-games 1 2021-09-07
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Pedro Vasconcelos](https://github.com/pedro-vasconcelos)
- [Laravel Framework](https://github.com/laravel/framework).

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
