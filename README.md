# NeoLabs AI

A Laravel 13 + Inertia v2 + React 19 single-page application for AI-powered creative tools including image generation, image editing, chat, documents, video, and transcription.

## Tech Stack

### Backend

- **PHP** 8.4
- **Laravel** 13
- **Inertia.js** (server) v2
- **Laravel Fortify** — authentication with 2FA support
- **Laravel Wayfinder** — auto-generated TypeScript route bindings
- **Pest** v4 — testing framework

### Frontend

- **React** 19 (with React Compiler via Babel plugin)
- **Inertia.js** (client) v2
- **TypeScript** 5.7
- **Tailwind CSS** v4
- **shadcn/ui** (New York style) with Radix primitives
- **Lucide** icons
- **Vite** 8

## Requirements

- PHP >= 8.3
- Composer
- Node.js >= 20
- npm
- SQLite (default) or MySQL/PostgreSQL
- **FFmpeg** — required for the Video Editor (trim, speed control, auto captions, etc.)

### Installing FFmpeg

The Video Editor's **auto captions** feature requires FFmpeg compiled with `libass` and `libfreetype` for the `subtitles` filter. On macOS, install `ffmpeg-full` instead of the base `ffmpeg` formula:

```bash
# macOS (Homebrew) — use ffmpeg-full for subtitle/caption support
brew install ffmpeg-full
brew link ffmpeg-full --force

# Ubuntu / Debian (includes libass by default)
sudo apt update && sudo apt install ffmpeg

# Windows (Chocolatey)
choco install ffmpeg
```

Verify the installation and subtitle support:

```bash
ffmpeg -version
ffmpeg -filters 2>/dev/null | grep subtitles
# Expected output: .. subtitles  V->V  Render text subtitles onto input video using the libass library.
```

> **Note:** If you previously installed the base `ffmpeg` formula on macOS, unlink it first: `brew unlink ffmpeg`

## Installation

### 1. Clone the repository

```bash
git clone <repository-url> neolabs-ai
cd neolabs-ai
```

### 2. Quick setup

Run the built-in setup script which installs dependencies, copies `.env`, generates an app key, runs migrations, and builds the frontend:

```bash
composer run setup
```

### 3. Configure environment

Edit `.env` and set your values:

```dotenv
APP_NAME="NeoLabs AI"
APP_URL=https://neolabs-ai.test   # or your local URL

# AI provider key
GEMINI_API_KEY=your-key-here
```

### 4. Seed the database (optional)

```bash
php artisan db:seed
```

## Manual Installation (step-by-step)

If you prefer to run each step individually:

```bash
# Install PHP dependencies
composer install

# Copy environment file and generate app key
cp .env.example .env
php artisan key:generate

# Run database migrations
php artisan migrate

# Install Node dependencies
npm install

# Build frontend assets
npm run build
```

## Development

Start the development server (runs Laravel server, queue worker, Pail logs, and Vite concurrently):

```bash
composer run dev
```

If using **Laravel Herd**, the app is automatically served at `https://neolabs-ai.test` — no need to run `php artisan serve`.

For SSR development:

```bash
composer run dev:ssr
```

### Individual commands

| Command | Description |
|---|---|
| `npm run dev` | Start Vite dev server |
| `npm run build` | Build frontend for production |
| `npm run lint` | Lint & fix TypeScript (ESLint) |
| `npm run format` | Format TypeScript (Prettier) |
| `npm run types:check` | TypeScript type checking |
| `vendor/bin/pint --dirty` | Format changed PHP files (Pint) |

## Testing

```bash
# Run all tests
php artisan test --compact

# Run a specific test file
php artisan test --compact tests/Feature/Settings/ProfileUpdateTest.php

# Run a filtered test
php artisan test --compact --filter=testName
```

## CI Check

Run all linting, formatting, type checks, and tests in one command:

```bash
composer run ci:check
```

## Project Structure

```
app/
├── Actions/          # Fortify action classes
├── Concerns/         # Shared traits
├── Http/
│   ├── Controllers/  # Organized by domain
│   └── Requests/     # Form request validation
├── Models/           # Eloquent models
└── Providers/        # Service providers

resources/js/
├── actions/          # Wayfinder-generated (do not edit)
├── routes/           # Wayfinder-generated (do not edit)
├── components/
│   └── ui/           # shadcn/ui primitives
├── hooks/            # Custom React hooks
├── layouts/          # App and auth layouts
├── pages/            # Inertia page components
└── types/            # Shared TypeScript types

tests/
├── Feature/          # Feature tests (Pest)
└── Unit/             # Unit tests (Pest)
```

## License

MIT
