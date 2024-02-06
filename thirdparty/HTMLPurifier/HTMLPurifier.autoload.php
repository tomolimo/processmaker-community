<?php

/**
 * @file
 * Convenience file that registers autoload handler for HTML Purifier.
 * It also does some sanity checks.
 */

spl_autoload_register(function($class)
{
    return HTMLPurifier_Bootstrap::autoload($class);
});

// vim: et sw=4 sts=4
