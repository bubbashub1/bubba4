<?php
/** Pure staging schema evidence gate. No DB connections, SQL execution or migrations. */
if (!defined('ABSPATH')) { exit; }
final class BH4_Staging_Schema_Evidence_Gate {
    public static function assess($facts) {
        if (!is_array($facts)) { return array('ready'=>false,'missing'=>array('invalid_facts'),'writes_allowed'=>false); }
        $required=array(
            'staging_environment_confirmed','read_only_schema_snapshot_collected',
            'activity_tables_verified','venue_organiser_tables_verified',
            'session_link_tables_verified','family_tables_verified',
            'booking_tables_verified','required_indexes_verified',
            'legacy_option_versions_recorded','backup_restore_tested',
            'no_unreviewed_schema_drift',
        );
        $missing=array();
        foreach($required as $key) {
            if (!array_key_exists($key,$facts) || $facts[$key] !== true) { $missing[]=$key; }
        }
        return array('ready'=>count($missing)===0,'missing'=>$missing,
            'writes_allowed'=>false,'migration_authorized'=>false,
            'evidence_keys'=>$required);
    }
}
