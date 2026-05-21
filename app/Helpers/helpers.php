<?php

/**
 * Global helper functions for the Legal Management System.
 */

if (! function_exists('money_display')) {
    function money_display($amount): string
    {
        $value = is_numeric($amount) ? $amount : 0;
        $symbol = html_entity_decode('&#8369;', ENT_QUOTES | ENT_HTML5);

        return $symbol . number_format((float) $value, 2, '.', ',');
    }
}
