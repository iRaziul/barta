# Changelog

## v1.1.0 - 2026-01-29

### What's Changed

* build(deps): bump actions/checkout from 5 to 6 by @dependabot[bot] in https://github.com/iRaziul/kotha/pull/1

### New Contributors

* @dependabot[bot] made their first contribution in https://github.com/iRaziul/kotha/pull/1

**Full Changelog**: https://github.com/iRaziul/kotha/compare/v1.0.0...v1.1.0

## v1.0.0 - 2026-01-29

🎉 First stable release of Kotha

Features:

- Multiple SMS gateway support (eSMS, MimSMS)
- Log driver for development/testing
- Bulk SMS to multiple recipients
- Queue support for background processing
- Laravel Notifications integration
- Automatic BD phone number formatting

Requirements:

- PHP 8.4+
- Laravel 11.x or 12.x

**Full Changelog**: https://github.com/iRaziul/kotha/commits/v1.0.0

## [1.0.0] - 2026-01-29

### Added

- Initial release
- eSMS driver for esms.com.bd
- MimSMS driver for mimsms.com
- Log driver for development/testing
- Bulk SMS support (send to multiple recipients)
- Queue support with `->queue()` method
- Laravel Notifications integration via 'kotha' channel
- Automatic Bangladeshi phone number formatting
- Install command `php artisan kotha:install`
