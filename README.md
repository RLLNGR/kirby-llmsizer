# kirby-llmsizer

A Kirby CMS plugin that generates `/llms.txt` and `/llms-sitemap.xml` for AI search engines (Perplexity, ChatGPT, Claude…). Part of the **rllngr** plugin set.

## Features

- Generates `/llms.txt` and `/llms-full.txt` in standard Markdown format
- Generates `/llms-sitemap.xml` in XML sitemap format
- Classic mode (direct Kirby routes) and headless mode (JSON API)
- Configurable sections with Kirby query strings or PHP callables
- Template and page exclusions with include-override
- Optional trailing slash on URLs
- Built-in caching with auto-invalidation on content changes
- Panel blueprint section (`sections/llms`) with description and AI instructions fields

## Installation

```bash
composer require rllngr/kirby-llmsizer
```

Or place the plugin folder in `site/plugins/kirby-llmsizer`.

## Endpoints

| URL | Format | Description |
|-----|--------|-------------|
| `/llms.txt` | Markdown | Standard LLM content file |
| `/llms-full.txt` | Markdown | Full version with extended page content |
| `/llms-sitemap.xml` | XML | Sitemap for LLM indexers |
| `/__llms__` | JSON | Headless mode API |
| `/__llms-full__` | JSON | Headless mode API (full) |

## Configuration

In `config.php`:

```php
return [
    'rllngr.kirby-llmsizer' => [
        // 'classic' or 'headless'
        'mode' => 'classic',

        // Caching
        'cache'         => true,
        'cacheDuration' => 60, // minutes

        // Trailing slash on page URLs
        'trailingSlash' => false,

        // Enable /llms-sitemap.xml (classic mode only)
        'sitemap' => true,

        // Override base URL (headless mode)
        'siteUrl' => null,

        // Exclusion rules (applied across all sections)
        'exclude' => [
            'templates' => ['error'],
            'pages'     => ['private-page'],
        ],

        // Force-include pages despite exclusion rules
        'include' => [
            'pages' => [],
        ],

        // Panel field names
        'fields' => [
            'description'  => 'llmsdescription',
            'instructions' => 'llmsinstructions',
        ],

        // Content sections
        'sections' => [
            [
                'heading'         => 'Projects',
                'pages'           => 'site.find("projects").children.listed',
                'itemDescription' => 'excerpt',
                'itemContent'     => 'text', // used in llms-full.txt
            ],
            [
                'heading'         => 'About',
                'pages'           => 'site.find("about")',
                'single'          => true,
                'itemDescription' => 'text',
            ],
        ],
    ],
];
```

### Section options

| Key | Type | Description |
|-----|------|-------------|
| `heading` | string | Section title (H2 in llms.txt) |
| `pages` | string\|callable | Kirby query string or `function($kirby)` returning a `Page` or `Pages` |
| `single` | bool | Treat result as a single page (default: `false`) |
| `limit` | int | Max number of items |
| `itemTitle` | string\|callable | Field name or callable for item title |
| `itemUrl` | callable | Custom URL resolver |
| `itemDescription` | string\|callable | Field for short description |
| `itemContent` | string\|callable | Field for full content (llms-full.txt only) |

## Panel Integration

Add the section to any blueprint:

```yaml
sections:
  geo:
    extends: sections/llms
```

This adds the **llmsdescription** and **llmsinstructions** fields to the panel page.

## License

MIT — Nicolas Rollinger
