<?php
/**
 * Pure persistence record contract for checkout recovery attempts.
 * No database writes, Stripe calls or scheduling. Stores no parent PII/secrets.
 */
if (!defined('ABSPATH')) {exit;}
final class BH4_Checkout_Recovery_Record_Contract {
 public static function create($booking_id,$fingerprint,$created_at) {
  if(!is_int($booking_id)||$booking_id<1||!is_string($fingerprint)||
     !preg_match('/^[a-f0-9]{64}$/D',$fingerprint)||
     !is_int($created_at)||$created_at<1) {return array('ok'=>false,'reason'=>'invalid_input');}
  return array('ok'=>true,'record'=>array(
   'booking_id'=>$booking_id,'request_fingerprint'=>$fingerprint,
   'created_at'=>$created_at,'state'=>'creating','session_id'=>null
  ));
 }
 public static function transition($record,$event,$session_id=null) {
  if(!is_array($record)||!isset($record['state'])||!is_string($event)) {
   return array('ok'=>false,'reason'=>'invalid_record');
  }
  $state=$record['state'];
  $allowed=array(
   'creating'=>array('remote_timeout'=>'needs_reconciliation','verified_session_saved'=>'saved',
                     'remote_rejected'=>'needs_reconciliation'),
   'needs_reconciliation'=>array('verified_session_saved'=>'saved','review_required'=>'manual_review'),
   'manual_review'=>array('verified_session_saved'=>'saved'),
   'saved'=>array()
  );
  if(!isset($allowed[$state][$event])) {return array('ok'=>false,'reason'=>'transition_blocked');}
  if($event==='verified_session_saved') {
   if(!is_string($session_id)||!preg_match('/^cs_test_[A-Za-z0-9_]+$/D',$session_id)) {
    return array('ok'=>false,'reason'=>'invalid_verified_session');
   }
   $record['session_id']=$session_id;
  } elseif($session_id!==null) {return array('ok'=>false,'reason'=>'unexpected_session');}
  $record['state']=$allowed[$state][$event];
  return array('ok'=>true,'record'=>$record);
 }
 public static function may_create_new_session($record) {
  // An existing attempt can never silently create another session.
  return false;
 }
}
