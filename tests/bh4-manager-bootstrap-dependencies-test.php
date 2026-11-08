<?php
/** BH4 manager bootstrap source integrity: required local files must exist. */
$root=dirname(__DIR__).'/plugin/bubba-hub-manager/';
$manager=file_get_contents($root.'includes/class-bh-manager.php');
if(!is_string($manager)||$manager==='') {fwrite(STDERR,"FAIL: manager missing\n");exit(1);}
$pattern='/require_once\s+BUBBA_HUB_MANAGER_PATH\s*\.\s*[\x27\x22]([^\x27\x22]+)[\x27\x22]\s*;/';
preg_match_all($pattern,$manager,$matches);
$paths=array_unique($matches[1]);
$missing=array();
foreach($paths as $path) {
 if(strpos($path,'..')!==false || $path[0]==='/' || !is_file($root.$path)) {$missing[]=$path;}
}
if(count($paths)<40) {fwrite(STDERR,"FAIL: suspiciously few static manager includes: ".count($paths)."\n");exit(1);}
if($missing) {fwrite(STDERR,"FAIL: missing/unsafe manager includes: ".implode(', ',$missing)."\n");exit(1);}
echo "PASS: ".count($paths)." static manager bootstrap dependencies exist\n";
echo "NOTE: dynamic includes, runtime hooks and WordPress callbacks require staging tests\n";
