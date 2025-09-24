<?php

namespace FluentSupportPro\Database\Migrations;

class WorkflowMigrator
{
    static $tableName = 'fs_workflows';

    public static function migrate()
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();

        $table = $wpdb->prefix . static::$tableName;

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE $table (
                `id` BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `created_by` BIGINT(20) NULL,
                `priority` INT(10) NULL DEFAULT 10,
                `title` VARCHAR(192) NULL,
                `trigger_key` VARCHAR(192) NULL,
                `trigger_type` VARCHAR(50) default 'manual',
                `settings` LONGTEXT NULL,
                `status` VARCHAR(50) default 'draft',
                `last_ran_at` TIMESTAMP NULL,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL
            ) $charsetCollate;";
            dbDelta($sql);
        } else {
            $existing_columns = $wpdb->get_col("DESC {$table}", 0);
            if(!in_array('priority', $existing_columns)) {
                $query = 'ALTER TABLE '.$table.' ADD `priority` INT(10) NULL DEFAULT 10 AFTER `created_by`';
                $wpdb->query($query);
            }
        }
    }
}
