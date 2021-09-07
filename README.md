# Draw Engine Laravel Package
[![Packagist Version](https://img.shields.io/packagist/v/pedro-vasconcelos/draw-engine?style=flat-square)](https://packagist.org/packages/pedro-vasconcelos/draw-engine)
[![GitHub](https://img.shields.io/github/license/pedro-vasconcelos/draw-engine?style=flat-square)](LICENSE)
[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/pedro-vasconcelos/draw-engine/run-tests?color=green&style=flat-square)](https://github.com/pedro-vasconcelos/draw-engine/actions)
[![Packagist Downloads](https://img.shields.io/packagist/dt/pedro-vasconcelos/draw-engine?color=green&style=flat-square)](https://packagist.org/packages/pedro-vasconcelos/draw-engine)

## Introduction

Introduction

## TODO

Criar um Interface para garantir que quando recebemos um Draw (nos 2 console commands), temos todos os atributos que necessitamos: dailyPrizeCap, prizes, algorithm, type, start_period, end_period

Melhorar as variantes da table dos draws... campos null em função de algumas condições não está bem.

```
10494  docker compose exec laravel php artisan vendor:publish --provider="PedroVasconcelos\DrawEngine\DrawServiceProvider" --tag="migrations"
10495  docker compose exec laravel php artisan migrate
10496  docker compose exec laravel php artisan db:seed --class="DrawsSeeder"
10497  docker compose exec laravel php artisan app:create-prize-delivery-schedule 1
10498  docker compose exec laravel php artisan app:generate-winner-games 1 2021-09-07
```

## Install

```bash
php artisan vendor:publish --provider="PedroVasconcelos\DrawEngine\DrawServiceProvider" --tag="migrations"

php artisan vendor:publish --provider="PedroVasconcelos\DrawEngine\DrawServiceProvider" --tag="config"
```



Configurar o model referente aos Draws, por defeito aponta para `App\Models\Draw::class`

Adicionar o Trait `HasPrizeSchedule` se for necessário aceder ao planeamento

## Commands

```bash
app:create-prize-delivery-schedule
app:generate-winner-game
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Pedro Vasconcelos](https://github.com/pedro-vasconcelos)
- [Laravel Framework](https://github.com/laravel/framework).

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
