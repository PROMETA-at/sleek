# CLAUDE.md

## Project Overview

Sleek is a Laravel package (`prometa/sleek`) providing Bootstrap UI components with aggressive defaults for rapid Laravel development.

**Stack**: PHP 8.1+, Laravel, Bootstrap 5.3+, Sass, Vite, PHPUnit with Orchestra Testbench.

## Design Principle

Aggressively default behavior to the most likely use case, while leaving every part of it explicitly customizable.

## Development Commands

This is a Laravel package — development uses the Orchestra Testbench workbench.

```bash
composer install && npm install
composer build        # Build the package
composer serve        # Serve workbench app
npm run dev           # Asset dev server with hot reload
composer test         # Run tests (or ./vendor/bin/phpunit)
composer lint         # Static analysis
./vendor/bin/pint     # Code formatting
```
