<?php
/** Pure package identity and rollback preflight; does not access WordPress or files. */
if (!defined('ABSPATH')) { exit; }
final class BH4_Upgrade_Package_Preflight {
    public static function assess($facts) {
        $required=array('installed_plugin_basename','candidate_plugin_basename','installed_package_sha256',
            'candidate_package_sha256','rollback_package_sha256','backup_verified','rollback_restore_tested',
            'no_second_active_copy','schema_writes_disabled','staging_confirmed');
        $missing=array();
        if (!is_array($facts)) {
            return array('ready'=>false,'missing'=>array('invalid_facts'),'execution_allowed'=>false);
        }
        foreach($required as $key) {
            if (!array_key_exists($key,$facts)) { $missing[]=$key; }
        }
        if($missing) { return array('ready'=>false,'missing'=>$missing,'execution_allowed'=>false); }
        $identity=$facts['installed_plugin_basename'];
        if (!is_string($identity) || !preg_match('~^[a-zA-Z0-9_-]+/[a-zA-Z0-9_-]+\.php$~D',$identity)
            || $facts['candidate_plugin_basename'] !== $identity) {
            $missing[]='same_plugin_identity';
        }
        foreach(array('installed_package_sha256','candidate_package_sha256','rollback_package_sha256') as $key) {
            if(!is_string($facts[$key]) || !preg_match('/^[a-f0-9]{64}$/D',$facts[$key])) {
                $missing[]=$key.'_invalid';
            }
        }
        foreach(array('backup_verified','rollback_restore_tested','no_second_active_copy',
            'schema_writes_disabled','staging_confirmed') as $key) {
            if($facts[$key] !== true) { $missing[]=$key.'_unverified'; }
        }
        return array('ready'=>count($missing)===0,'missing'=>$missing,'execution_allowed'=>false);
    }
}
