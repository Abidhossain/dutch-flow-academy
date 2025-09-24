<?php

namespace FluentSupportPro\Database\Migrations;

class TimeTrackMigrator
{
    static $tableName = 'fs_time_tracks';

    public static function migrate()
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();

        $table = $wpdb->prefix . static::$tableName;

        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") != $table) {
            $sql = "CREATE TABLE IF NOT EXISTS `$table` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `agent_id` BIGINT UNSIGNED NOT NULL,
            `customer_id` BIGINT UNSIGNED NOT NULL,
            `ticket_id` BIGINT UNSIGNED NOT NULL,
            `mailbox_id` BIGINT UNSIGNED NOT NULL,
            `started_at` TIMESTAMP NULL,
            `completed_at` TIMESTAMP NULL,
            `message` TEXT NULL,
            `status` VARCHAR(50) NULL DEFAULT 'committed',
            `working_minutes` INT UNSIGNED NOT NULL DEFAULT 0,
            `billable_minutes` INT UNSIGNED NOT NULL DEFAULT 0,
            `is_manual` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `agent_id` (`agent_id`),
            KEY `status` (`status`),
            KEY `ticket_id` (`ticket_id`)
        ) $charsetCollate;";
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }
    }
}
