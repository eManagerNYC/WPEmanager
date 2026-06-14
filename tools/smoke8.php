<?php
wp_set_current_user(1);
function r($m,$rt,$b=null){$q=new WP_REST_Request($m,$rt);if($b!==null){$q->set_header("Content-Type","application/json");$q->set_body(wp_json_encode($b));}$x=rest_do_request($q);return array($x->get_status(),$x->get_data());}
function line($l,$ok,$x=""){echo ($ok?"PASS":"FAIL")."  $l".($x?"  — $x":"")."\n";}
list($s,$ci)=r("POST","/em/v1/modules/coordination-issues/records",array("title"=>"Duct vs beam clash","description"=>"Needs design input","clash_type"=>"Hard Clash"));
$id=$ci["id"]??0;
list($s,$sp)=r("POST","/em/v1/modules/coordination-issues/records/$id/spawn",array("target"=>"rfis"));
line("Coordination Issue → RFI",($s==200||$s==201)&&!empty($sp["record"]["id"]),"RFI subject=".($sp["record"]["subject"]??"?"));
list($s,$lr)=r("POST","/em/v1/modules/labor-rates/records",array("classification"=>"Carpenter","base_rate"=>62.5));
$lid=$lr["id"]??0;
list($s1,$su)=r("POST","/em/v1/modules/labor-rates/records/$lid/transition",array("to"=>"Superseded"));
line("Labor rate Active→Superseded (versioning)",$s1==200 && ($su["status"]??"")=="Superseded","status=".($su["status"]??"?"));
global $wpdb; foreach(array("coordination_issues","rfis","labor_rates") as $t){$wpdb->query("DELETE FROM {$wpdb->prefix}em_$t");}
echo "DONE\n";
