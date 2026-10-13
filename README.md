# Wishbox RadicalMart Ozon

Joomla package for synchronizing RadicalMart product identifiers, prices, and stocks with Ozon Seller API.

## Requirements

- Joomla 6.1.4 or later
- PHP 8.5 or later
- RadicalMart
- `pkg_wishboxradicalmart`
- `pkg_wishboxozon`

Install the RadicalMart and Wishbox Ozon dependencies before installing this package.

## Installation

Build the package:

```bash
php phing-latest.phar
```

Install `pkg_radicalmart_wishboxozon.zip` through the Joomla Extension Manager.

## Configuration

Configure the Ozon connection in **Components → Wishbox Ozon**:

- Client ID
- API key
- API URL
- proxy token, when a proxy is used
- FBS warehouse ID
- currency code

Configure the RadicalMart price multiplier in **Components → Wishbox RadicalMart Ozon**.

Each RadicalMart product must use its Ozon `offer_id` as the RadicalMart alias. The **Get Ozon IDs** action stores the matching Ozon `product_id` in the `ozon_market_id` product field.

## Product actions

The RadicalMart product list contains three Ozon actions:

- **Get Ozon IDs** — matches Ozon offers to RadicalMart products.
- **Update in Ozon** — updates prices and stocks for selected products.
- **Update all in Ozon** — updates all products linked through `ozon_market_id`.

The package also installs a Joomla Scheduler task for updating all linked products.

## Tests

```bash
composer install
composer test
```
