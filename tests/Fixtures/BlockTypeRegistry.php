<?php

declare(strict_types=1);

if (! class_exists('WP_Block_Type_Registry')) {
    /**
     * Minimal stand-in for WordPress's block type registry: the singleton and the
     * method BlockTypeFilter calls, with the real signatures.
     */
    final class WP_Block_Type_Registry
    {
        /** @var array<string, object> */
        public static array $registered = [];

        private static ?WP_Block_Type_Registry $instance = null;

        public static function get_instance(): WP_Block_Type_Registry
        {
            return self::$instance ??= new self;
        }

        /**
         * @return array<string, object>
         */
        public function get_all_registered(): array
        {
            return self::$registered;
        }
    }
}
