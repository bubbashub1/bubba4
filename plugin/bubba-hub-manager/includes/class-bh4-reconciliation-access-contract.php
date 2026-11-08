<?php
/** Pure policy for future admin-only read-only reconciliation access. */
if (!defined('ABSPATH')) { exit; }
final class BH4_Reconciliation_Access_Contract {
    public static function authorize($authenticated, $can_manage_options, $is_admin_context, $request_method, $nonce_valid) {
        if ($authenticated !== true) { return array('allowed'=>false,'reason'=>'authentication_required'); }
        if ($can_manage_options !== true) { return array('allowed'=>false,'reason'=>'administrator_capability_required'); }
        if ($is_admin_context !== true) { return array('allowed'=>false,'reason'=>'admin_context_required'); }
        if ($request_method !== 'GET') { return array('allowed'=>false,'reason'=>'read_only_get_required'); }
        if ($nonce_valid !== true) { return array('allowed'=>false,'reason'=>'nonce_required'); }
        return array('allowed'=>true,'reason'=>'read_only_admin_review');
    }
}
