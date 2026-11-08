<?php
define('ABSPATH', __DIR__);
require dirname(__DIR__) . '/plugin/bubba-hub-manager/includes/class-bh4-multi-attendee-selection-contract.php';
function check_selection($ids, $places, $capacity, $occupied, $expected) {
    $result = BH4_Multi_Attendee_Selection_Contract::validate($ids, $places, $capacity, $occupied);
    if ($result['reason'] !== $expected || $result['ok'] !== ($expected === 'eligible_for_owner_and_schedule_checks')) {
        fwrite(STDERR, 'FAIL: expected ' . $expected . ' got ' . $result['reason'] . "\n"); exit(1);
    }
}
check_selection(array(12,13), 2, 10, 4, 'eligible_for_owner_and_schedule_checks');
check_selection(array('12','13'), 2, 2, 0, 'eligible_for_owner_and_schedule_checks');
check_selection(array(12,12), 2, 10, 0, 'duplicate_profile');
check_selection(array(12), 2, 10, 0, 'invalid_selection_or_capacity');
check_selection(array(12,13), 2, 5, 4, 'insufficient_capacity');
check_selection(array(12,13), 2, 5, 5, 'insufficient_capacity');
check_selection(array(12), 1, 5, 6, 'invalid_selection_or_capacity');
check_selection(array(12), 0, 5, 0, 'invalid_selection_or_capacity');
check_selection(array(12), 21, 30, 0, 'invalid_selection_or_capacity');
check_selection(array('12x'), 1, 5, 0, 'invalid_profile_id');
check_selection(array(0), 1, 5, 0, 'invalid_profile_id');
check_selection(array(12), 1, 0, 0, 'invalid_selection_or_capacity');
echo "PASS: multi-child selection and capacity preflight fixtures (no database or payments)\n";
