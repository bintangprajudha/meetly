# GEMINI Project Analysis: Meetly

This document provides a comprehensive overview of the "Meetly" project, intended as a guide for future AI-assisted development sessions.

## Project Overview

Meetly is a full-stack web application built with Laravel and Vue.js. Based on the file structure and route definitions, it functions as a social media platform with features like posts, comments, likes, reposts, user following, and real-time messaging.

The project is configured as a "starter kit," providing a robust foundation for a modern web application with pre-built authentication, UI components, and development tooling.

**Key Technologies:**
- **Backend:** Laravel 12, PHP 8.2
- **Frontend:** Vue 3, Inertia.js, TypeScript
- **Database:** MySQL/MariaDB or SQLite (configurable)
- **Authentication:** Laravel Fortify (including 2FA support)
- **Styling:** Tailwind CSS v4 with `clsx` and `tailwind-merge` for utility class management. UI components are sourced from `lucide-vue-next` and `reka-ui`.
- **Build Tool:** Vite.js for frontend asset compilation.
- **Messaging:** Uses Pusher for real-time capabilities.

**Architecture:**
- The application follows a standard Laravel Model-View-Controller (MVC) pattern for the backend.
- The frontend is a Single-Page Application (SPA) integrated via Inertia.js, which allows for building a modern Vue frontend without the complexity of a separate API. Vue components are located in `resources/js/components` and pages in `resources/js/pages`.
- Routes are defined in `routes/web.php`, separating guest, authenticated, and public-facing endpoints.

## Building and Running

The project includes clear scripts in both `composer.json` and `package.json` for managing its lifecycle.

### Initial Setup

For a fresh installation, the `setup` Composer script automates the entire process:

```bash
# This command installs PHP & JS dependencies, creates .env, generates an app key, runs migrations, and builds frontend assets.
composer run setup
```

### Development

To run the application in a local development environment, use the `dev` Composer script. This concurrently starts the Laravel server, Vite dev server, queue listener, and the Pail log viewer.

```bash
# Start all development services
composer run dev
```

- **Backend Server:** `http://127.0.0.1:8000`
- **Vite Server:** `http://localhost:5173`

### Building for Production

To compile and version the frontend assets for a production environment:

```bash
npm run build
```

### Running Tests

The project uses Pest for testing. Tests are located in the `tests/` directory.

```bash
# Run the entire test suite
composer test
```

## Development Conventions

### Coding Style & Formatting

- **PHP:** The project uses `laravel/pint` for enforcing PHP code style. To format PHP files, run:
  ```bash
  php artisan pint
  ```

- **JavaScript/TypeScript/Vue:** Code quality is maintained using ESLint and Prettier.
  - To check and fix linting issues:
    ```bash
    npm run lint
    ```
  - To format frontend files:
    ```bash
    npm run format
    ```
  Configuration for these tools can be found in `eslint.config.js` and `.prettierrc`.

### API and Data

- The application uses Laravel's Eloquent ORM for database interactions. Migrations in `database/migrations` define the schema for users, posts, comments, followers, messages, etc.
- The frontend receives data directly from Laravel controllers via Inertia.js props, as seen in `routes/web.php`. For example, the `Dashboard` page receives `user` and `posts` data.
- For client-side interactions requiring server data (like fetching new messages), there are dedicated "API" routes under `middleware('auth')` (e.g., `/api/messages`) that are consumed by the frontend.
