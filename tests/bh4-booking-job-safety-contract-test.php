<?php
define('ABSPATH',__DIR__);
require dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-booking-job-safety-contract.php';
$expire=array(
 array('reserved','unpaid',false,200,100,true),
 array('reserved','pending',false,200,100,true),
 array('confirmed','unpaid',false,200,100,false),
 array('enrolled','unpaid',false,200,100,false),
 array('cancelled','unpaid',false,200,100,false),
 array('reserved','paid',false,200,100,false),
 array('reserved','unpaid',true,200,100,false),
 array('reserved','unpaid',null,200,100,false),
 array('reserved','unpaid',false,99,100,false),
 array('reserved','unpaid',false,200,0,false),
 array('reserved','unpaid',false,'200',100,false),
);
foreach($expire as $i=>$case) {
 $expected=array_pop($case);
 if(BH4_Booking_Job_Safety_Contract::can_expire(...$case)!==$expected) {fwrite(STDERR,"FAIL expiry case $i\n");exit(1);}
}
$reminders=array(
 array('awaiting_confirmation','paid',true,true,true),
 array('awaiting_confirmation','not_required',true,true,true),
 array('reserved','paid',true,true,false),
 array('confirmed','paid',true,true,false),
 array('awaiting_confirmation','unpaid',true,true,false),
 array('awaiting_confirmation','paid',false,true,false),
 array('awaiting_confirmation','paid',true,false,false),
);
foreach($reminders as $i=>$case) {
 $expected=array_pop($case);
 if(BH4_Booking_Job_Safety_Contract::can_send_unconfirmed_reminder(...$case)!==$expected) {fwrite(STDERR,"FAIL reminder case $i\n");exit(1);}
}
echo "PASS: 18 conservative booking background-job fixtures\n";
