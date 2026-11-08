<?php
/**
 * Source-only safety assertion: BH4 features must remain off until staging parity.
 * Does not bootstrap WordPress or run any plugin code.
 */
$path=dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh-manager.php';
$source=file_get_contents($path);
if(!is_string($source) || $source==='') { fwrite(STDERR,"FAIL: manager source unavailable\n");exit(1); }
$flags=array('FEATURE_DIRECTORY','FEATURE_LEADER','FEATURE_FAMILY','FEATURE_BOOKINGS');
foreach($flags as $flag) {
 $pattern='/public\s+const\s+'.preg_quote($flag,'/').'\s*=\s*false\s*;/';
 if(preg_match_all($pattern,$source,$matches)!==1) {
  fwrite(STDERR,"FAIL: {$flag} must be declared exactly once and false\n");exit(1);
 }
 if(preg_match('/\b'.preg_quote($flag,'/').'\s*=\s*true\b/',$source)) {
  fwrite(STDERR,"FAIL: {$flag} enabled in manager source\n");exit(1);
 }
}
echo "PASS: four BH4 feature flags explicitly disabled in source\n";
