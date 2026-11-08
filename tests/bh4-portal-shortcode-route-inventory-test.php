<?php
/** Read-only source inventory of registered BH4 shortcodes and expected portal routes. */
$root=dirname(__DIR__).'/plugin/bubba-hub-manager';
$files=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS));
$shortcodes=array();
foreach($files as $file) {
 if(!$file->isFile() || substr($file->getFilename(),-4)!=='.php') {continue;}
 $source=file_get_contents($file->getPathname());
 if(!is_string($source)) {continue;}
 if(preg_match_all('/\badd_shortcode\s*\(\s*[\x27\x22]([a-zA-Z0-9_-]+)[\x27\x22]/',$source,$matches)) {
  foreach($matches[1] as $tag) {$shortcodes[$tag]=true;}
 }
}
$required=array('bh_parent_booking','bh_leader_booking_dashboard');
$missing=array_values(array_filter($required,static function($tag)use($shortcodes){return !isset($shortcodes[$tag]);}));
sort($missing);
if($missing) {fwrite(STDERR,"FAIL required checkout/leader shortcodes absent: ".implode(',',$missing)."\n");exit(1);}
$routes=array(
 '/my-hub/','/my-hub/preferences/','/my-hub/profile/','/my-hub/children/',
 '/my-hub/planner/','/my-hub/favourites/',
 '/leader/','/leader/about/','/leader/new-activity/','/leader/bookings/',
 '/leader/messages/','/leader/venues/','/leader/business/','/leader/payment/',
);
foreach($routes as $route) {
 if($route[0]!=='/' || substr($route,-1)!=='/') {fwrite(STDERR,"FAIL malformed route\n");exit(1);}
}
echo "PASS: required checkout/leader shortcode tags present; ".count($shortcodes)." source tags inventoried\n";
echo "NOTE: ".count($routes)." expected page routes are NOT verified against WordPress page records\n";
