# mage2_fix_bug5630

Standalone fix for [5630](https://github.com/magento/magento2/issues/5630).

## Install

Add repo to composer.json:
```json
{
    "type": "vcs",
    "url": "https://github.com/LuxTechnology/mage2_fix_bug5630"
}
```

Run commands
```bash
$ cd ${DIR_MAGE_ROOT}
$ composer require luxtechnology/mage2_fix_bug5630
$ bin/magento module:enable LuxTechnology_FixBug5630
$ bin/magento setup:upgrade
$ bin/magento setup:static-content:deploy
$ bin/magento cache:clean
```
