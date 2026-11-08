<?php
/**
 * Fail-closed booking background-job eligibility. Pure logic; no scheduler registration.
 */
if (!defined('ABSPATH')) {exit;}
final class BH4_Booking_Job_Safety_Contract {
 public static function can_expire($booking_status,$payment_status,$has_active_checkout,$now,$expires_at) {
  if(!is_string($booking_status)||$booking_status!=='reserved') {return false;}
  if(!is_string($payment_status)||!in_array($payment_status,array('unpaid','pending'),true)) {return false;}
  if($has_active_checkout!==false) {return false;}
  if(!is_int($now)||!is_int($expires_at)||$expires_at<=0||$now<$expires_at) {return false;}
  return true;
 }
 public static function can_send_unconfirmed_reminder($booking_status,$payment_status,$consent,$recipient_verified) {
  return $booking_status==='awaiting_confirmation'
    && in_array($payment_status,array('paid','not_required'),true)
    && $consent===true && $recipient_verified===true;
 }
}
