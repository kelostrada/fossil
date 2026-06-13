<?php
/**
 * Shared page components, so every page uses the same header and content
 * scaffolding (consistent title sizing, spacing and container width).
 * Loaded globally via config.php.
 */

if (!function_exists('render_page_header')) {
    /**
     * Consistent page header used at the top of every page.
     *
     * @param string      $title    Plain-text page title (HTML-escaped).
     * @param string|null $subtitle Optional lead/description; raw HTML allowed
     *                              (callers are responsible for escaping it).
     */
    function render_page_header(string $title, ?string $subtitle = null): string
    {
        $html  = '<header class="page-header">';
        $html .= '<h1 class="page-title">' . htmlspecialchars($title) . '</h1>';
        if ($subtitle !== null && $subtitle !== '') {
            $html .= '<p class="page-subtitle">' . $subtitle . '</p>';
        }
        $html .= '</header>';
        return $html;
    }
}
