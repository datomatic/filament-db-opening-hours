# Changelog

All notable changes to `filament-db-opening-hours` will be documented in this file.

## v1.1.0 - 2026-11-24

**Full Changelog**: https://github.com/datomatic/filament-db-opening-hours/compare/v1.0.0...v1.1.0

## v1.0.0 - 2026-11-20

### Breaking

- Requires PHP ^8.2, Laravel 12/13, Filament ^5.0 and datomatic/db-opening-hours ^1.1.

### Added

- Plugin options: navigationGroup(), navigationIcon(), navigationSort(), resource().
- `OpeningHourSchedule` service to create/update/delete schedules in a transaction.
- Validation: time range end after start, unique exception dates.
- Table: linked model, days and exceptions counts, bulk delete.
- Tests, full README, CI for PHP 8.2–8.6.

### Fixed

- Removing a day, an exception or a whole schedule no longer leaves orphaned time ranges.
- Uses the opening hour model configured in db-opening-hours.

### What's Changed

* Bump dependabot/fetch-metadata from 2.2.0 to 2.3.0 by @dependabot[bot] in https://github.com/datomatic/filament-db-opening-hours/pull/2
* Bump aglipanci/laravel-pint-action from 2.4 to 2.5 by @dependabot[bot] in https://github.com/datomatic/filament-db-opening-hours/pull/3
* Bump dependabot/fetch-metadata from 2.3.0 to 2.4.0 by @dependabot[bot] in https://github.com/datomatic/filament-db-opening-hours/pull/4
* Bump aglipanci/laravel-pint-action from 2.5 to 2.6 by @dependabot[bot] in https://github.com/datomatic/filament-db-opening-hours/pull/6
* Bump actions/checkout from 4 to 6 by @dependabot[bot] in https://github.com/datomatic/filament-db-opening-hours/pull/9
* Bump stefanzweifel/git-auto-commit-action from 5 to 7 by @dependabot[bot] in https://github.com/datomatic/filament-db-opening-hours/pull/8
* Bump dependabot/fetch-metadata from 2.4.0 to 2.5.0 by @dependabot[bot] in https://github.com/datomatic/filament-db-opening-hours/pull/10

**Full Changelog**: https://github.com/datomatic/filament-db-opening-hours/compare/v0.2.0...v1.0.0

## v0.1.0 - 2024-08-22

First release

## 1.0.0 - 202X-XX-XX

- initial release
