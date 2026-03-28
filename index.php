<?php

use Kirby\Cms\App;
use Rllngr\KirbyLlmsizer\Generator;

// Autoload plugin classes
load([
    'Rllngr\\KirbyLlmsizer\\Generator' => __DIR__ . '/src/Generator.php',
]);

App::plugin('rllngr/kirby-llmsizer', [

    'options' => [
        // 'classic' — Kirby serves /llms.txt directly
        // 'headless' — exposes /__llms__ JSON endpoints for the frontend
        'mode'          => 'classic',
        'cache'         => true,
        'cacheDuration' => 60, // minutes
        'siteUrl'       => null, // auto-detected; override in headless mode
        'trailingSlash' => false,
        'sections'      => [], // see README for structure

        // Page/template exclusions (applied across all auto-resolved sections)
        'exclude' => [
            'templates' => ['error'],
            'pages'     => [],
        ],
        // Force-include specific page slugs despite exclusion rules
        'include' => [
            'pages' => [],
        ],

        // Sitemap XML (classic mode only)
        'sitemap' => true,

        // Panel field names (can be overridden)
        'fields' => [
            'description'  => 'llmsdescription',
            'instructions' => 'llmsinstructions',
        ],
    ],

    // Register panel blueprints
    'blueprints' => [
        'sections/llms' => __DIR__ . '/blueprints/sections/llms.yml',
    ],

    'routes' => function (App $kirby) {
        $mode   = $kirby->option('rllngr.kirby-llmsizer.mode', 'classic');
        $routes = [];

        if ($mode === 'classic') {
            $routes[] = [
                'pattern' => 'llms.txt',
                'method'  => 'GET',
                'action'  => function () use ($kirby) {
                    $generator = new Generator($kirby);
                    header('Content-Type: text/plain; charset=utf-8');
                    header('Cache-Control: public, max-age=3600');
                    die($generator->generate());
                },
            ];

            $routes[] = [
                'pattern' => 'llms-full.txt',
                'method'  => 'GET',
                'action'  => function () use ($kirby) {
                    $generator = new Generator($kirby);
                    header('Content-Type: text/plain; charset=utf-8');
                    header('Cache-Control: public, max-age=3600');
                    die($generator->generate(full: true));
                },
            ];

            if ($kirby->option('rllngr.kirby-llmsizer.sitemap', true)) {
                $routes[] = [
                    'pattern' => 'llms-sitemap.xml',
                    'method'  => 'GET',
                    'action'  => function () use ($kirby) {
                        $generator = new Generator($kirby);
                        header('Content-Type: application/xml; charset=utf-8');
                        header('Cache-Control: public, max-age=3600');
                        die($generator->generateSitemap());
                    },
                ];
            }
        }

        // Headless mode: structured JSON endpoints for the frontend
        if ($mode === 'headless') {
            $routes[] = [
                'pattern' => '__llms__',
                'method'  => 'GET',
                'action'  => function () use ($kirby) {
                    $generator = new Generator($kirby);
                    header('Content-Type: application/json; charset=utf-8');
                    die(json_encode(['result' => $generator->toArray()]));
                },
            ];

            $routes[] = [
                'pattern' => '__llms-full__',
                'method'  => 'GET',
                'action'  => function () use ($kirby) {
                    $generator = new Generator($kirby);
                    header('Content-Type: application/json; charset=utf-8');
                    die(json_encode(['result' => $generator->toArray(full: true)]));
                },
            ];
        }

        return $routes;
    },

    // Flush cache whenever content changes in the panel
    'hooks' => [
        'page.update:after'       => function () { Generator::clearCache(kirby()); },
        'page.create:after'       => function () { Generator::clearCache(kirby()); },
        'page.delete:after'       => function () { Generator::clearCache(kirby()); },
        'page.changeStatus:after' => function () { Generator::clearCache(kirby()); },
        'site.update:after'       => function () { Generator::clearCache(kirby()); },
    ],

]);
