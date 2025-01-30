# TinyMCE Laravel Demo with AI Integration

A Laravel application demo with TinyMCE.

## Requirements

- PHP 8.1 or higher
- Node.js 16 or higher
- Composer
- TinyMCE API Key
- OpenAI API Key

## Setup

1. Clone the repository:
```bash
git clone <your-repo-url>
cd tinymce-laravel-demo
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install Node.js dependencies:
```bash
npm install
```

4. Create environment file:
```bash
cp .env.example .env
```

5. Configure your environment variables in `.env`:
```
VITE_TINYMCE_API_KEY=your_tinymce_api_key
VITE_OPENAI_API_KEY=your_openai_api_key
```

6. Generate application key:
```bash
php artisan key:generate
```

## Development

1. Start Vite development server:
```bash
npm run dev
```

2. Start Laravel development server:
```bash
php artisan serve
```

3. Visit http://localhost:8000 in your browser

## Features

- Rich text editing with TinyMCE
- AI-powered writing assistance
- Real-time AI suggestions
- Error logging and monitoring
