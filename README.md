# Bea Logger

Basic and simple logger for WordPress.

## Usage

```php
$logger = new Bea_Log( WP_CONTENT_DIR . '/my-logger' );
$logger->log_this( 'Log this message', Bea_Log::gravity_0 );
```

Produces a line like:

```
[d-m-Y H:i:s][Emerg] Log this message
```

## Constructor

```php
new Bea_Log( $file_path, $file_extension = '.log', $retention_size = '', $write_enabled = true );
```

- `$file_path`: base path without extension.
- `$file_extension`: file extension including the dot (default `.log`).
- `$retention_size`: max file size in bytes before rotation (default `419430400`, ~400 MB).
- `$write_enabled`: when `false`, `log_this()` is a no-op (no disk I/O).

## Rotation & compression

When the log file exceeds `$retention_size`, it is renamed to `{file_path}-{Y-m-d-H-i-s}{ext}` and, when `ZipArchive` is available, compressed into a sibling `.zip` (the plain rotated file is then removed).

## Gravity levels

```php
const gravity_0 = 'Emerg';
const gravity_1 = 'Alert';
const gravity_2 = 'Crit';
const gravity_3 = 'Err';
const gravity_4 = 'Warning';
const gravity_5 = 'Notice';
const gravity_6 = 'Info';
const gravity_7 = 'Debug';
```

Default level is `gravity_7` (`Debug`).
