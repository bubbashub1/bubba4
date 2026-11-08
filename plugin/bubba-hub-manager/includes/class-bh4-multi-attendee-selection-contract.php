<?php
/**
 * Pure booking selection preflight for BH4 migration fixtures.
 * No database calls or booking writes; runtime continues to use BH3 methods.
 */
if (!defined('ABSPATH')) { exit; }
final class BH4_Multi_Attendee_Selection_Contract {
    public static function validate($profile_ids, $places, $capacity, $occupied) {
        if (!is_int($places) || $places < 1 || $places > 20
            || !is_int($capacity) || $capacity < 1
            || !is_int($occupied) || $occupied < 0 || $occupied > $capacity
            || !is_array($profile_ids) || count($profile_ids) !== $places) {
            return array('ok' => false, 'reason' => 'invalid_selection_or_capacity');
        }
        $ids = array();
        foreach ($profile_ids as $id) {
            if (!is_int($id) && !(is_string($id) && ctype_digit($id))) {
                return array('ok' => false, 'reason' => 'invalid_profile_id');
            }
            $id = (int) $id;
            if ($id < 1) { return array('ok' => false, 'reason' => 'invalid_profile_id'); }
            $ids[] = $id;
        }
        if (count(array_unique($ids)) !== count($ids)) {
            return array('ok' => false, 'reason' => 'duplicate_profile');
        }
        if ($places > $capacity - $occupied) {
            return array('ok' => false, 'reason' => 'insufficient_capacity');
        }
        return array('ok' => true, 'reason' => 'eligible_for_owner_and_schedule_checks',
            'places' => $places, 'available_before_hold' => $capacity - $occupied,
            'profile_ids' => $ids);
    }
}
