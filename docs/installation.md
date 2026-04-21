# Installation

## Table of contents

- [Quick setup](#quick-setup)

Follow these quick steps to get the project running locally.

```bash
git clone https://github.com/arafat-anwar/zkteco-biometric-integration.git
cd zkteco-biometric-integration
composer install
cp .env.example .env
# update .env DB settings
php artisan key:generate
php artisan migrate
npm install # optional (assets)
npm run build
php artisan serve
```
