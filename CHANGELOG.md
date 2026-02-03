# Changelog

## Release v1.2.1 - 2026-02-03

### Changed\n- Refactor drivers to use the Template Method pattern\n- Refactor phone number formatting to a dedicated helper class\n- Refactor log driver test to be more concise\n- Downgrade PHP version requirements to 8.2

## v1.2.0 - 2026-02-01

### Added

- Add `smsnoc` driver
- Rename project from `kotha` to `barta`

### Changed

- Update documentation

## v1.1.0 - 2026-01-29

### What's Changed

https://github.com/iRaziul/barta/pull/1

### New Contributors

- @dependabot[bot] made their first contribution in https://github.com/iRaziul/barta/pull/1

**Full Changelog**: https://github.com/iRaziul/barta/compare/v1.0.0...v1.1.0

## v1.0.0 - 2026-01-29

🎉 First stable release of Barta

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

**Full Changelog**: https://github.com/iRaziul/barta/commits/v1.0.0

## [1.0.0] - 2026-01-29

### Added

- Initial release
- eSMS driver for esms.com.bd
- MimSMS driver for mimsms.com
- Log driver for development/testing
- Bulk SMS support (send to multiple recipients)
- Queue support with `->queue()` method
- Laravel Notifications integration via 'barta' channel
- Automatic Bangladeshi phone number formatting
- Install command `php artisan barta:install`
