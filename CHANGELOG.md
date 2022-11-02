# Changelog

# v1.1.0
Features:
- Added a DateTimeDefinition which represents the date / time part of cronjob lines. [PR #1](https://github.com/mintware-de/native-cron/pull/1)

Deprecated:
- `CronJobLine::getMinutes()` => `CronJobLine::getDateTimeDefinition()::getMinutes()`
- `CronJobLine::getHours()` => `CronJobLine::getDateTimeDefinition()::getHours()`
- `CronJobLine::getDays()` => `CronJobLine::getDateTimeDefinition()::getDays()`
- `CronJobLine::getMonths()` => `CronJobLine::getDateTimeDefinition()::getMonths()`
- `CronJobLine::getWeekdays()` => `CronJobLine::getDateTimeDefinition()::getWeekdays()`

# v1.0.0
Initial release
