<?php if (!defined('HTMLY')) die('HTMLy');

/**
 * Editorial theme helper functions.
 * HTMLy auto-includes this file when it exists inside the active theme
 * folder. All function names are prefixed with "editorial_" so they never
 * collide with core or other themes/plugins.
 */

/**
 * Cache-busting asset version.
 * HTMLy caches whole rendered pages, so a stylesheet/script <link> URL
 * only changes when we deliberately change it. Using the file's own
 * modified time means visitors always get the current CSS/JS the moment
 * you update the theme, without ever needing to purge HTMLy's page cache
 * just to see a style change, and without hurting long-term browser
 * caching in the meantime.
 */
if (!function_exists('editorial_ver')) {
    function editorial_ver($relativePath)
    {
        $full = __DIR__ . '/' . ltrim($relativePath, '/');
        return is_file($full) ? substr((string) filemtime($full), -6) : '1';
    }
}

/**
 * Safe excerpt builder.
 * Falls back gracefully: explicit description -> teaser from body -> empty.
 */
if (!function_exists('editorial_excerpt')) {
    function editorial_excerpt($description, $body, $url, $length = 190)
    {
        $text = trim((string) $description);
        if ($text === '' && function_exists('get_teaser')) {
            $text = trim(strip_tags(get_teaser($body, $url)));
        }
        if ($text === '') {
            $text = trim(strip_tags((string) $body));
        }
        if (function_exists('mb_strlen') && mb_strlen($text) > $length) {
            $text = mb_substr($text, 0, $length) . '…';
        } elseif (strlen($text) > $length) {
            $text = substr($text, 0, $length) . '…';
        }
        return $text;
    }
}

/**
 * One or two-letter initials used for the CSS-only avatar badge,
 * so the theme never depends on an external Gravatar request.
 */
if (!function_exists('editorial_initials')) {
    function editorial_initials($name)
    {
        $name = trim((string) $name);
        if ($name === '') {
            return '?';
        }
        $parts = preg_split('/\s+/u', $name);
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $chr = function_exists('mb_substr') ? mb_substr($part, 0, 1) : substr($part, 0, 1);
            $initials .= function_exists('mb_strtoupper') ? mb_strtoupper($chr) : strtoupper($chr);
        }
        return $initials !== '' ? $initials : '?';
    }
}

/**
 * Try to pull a usable thumbnail: the post's declared image first,
 * then the first image found inside the markdown body, else null so
 * the template can render the gradient monogram fallback instead.
 */
if (!function_exists('editorial_thumb')) {
    function editorial_thumb($post)
    {
        if (!empty($post->image)) {
            return $post->image;
        }
        if (function_exists('get_image')) {
            $img = get_image($post->body);
            if (!empty($img)) {
                return $img;
            }
        }
        return null;
    }
}
