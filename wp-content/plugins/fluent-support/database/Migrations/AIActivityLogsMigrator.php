<?php

namespace FluentSupport\Database\Migrations;

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
class AIActivityLogsMigrator
{
    static $tableName = 'fs_ai_activity_logs';

    public static function migrate()
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();

        $table = $wpdb->prefix . static::$tableName;

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE $table (
                `id` BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `agent_id` BIGINT(20) NULL,
                `ticket_id` BIGINT(20) NULL,
                `model_name` VARCHAR(50) NULL,
                `tokens` MEDIUMTEXT NULL,
                `prompt` LONGTEXT NULL,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL
            ) $charsetCollate;";
            dbDelta($sql);
        }
    }
}
