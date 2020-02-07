<?php
/**
 *
 */
namespace FishPig\WordPress\Api\Data;

interface ShortcodeInterface
{
    /**
     *
     *
     */
    public function renderShortcode($input, array $args = []);
}
