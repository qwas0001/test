<?php
/*
This file is part of miniBB. miniBB is free discussion forums/message board software, without any warranty. See COPYING file for more details.
Copyright (C) 2018 Paul Puzyrev. www.minibb.com
Latest File Update: 2018-Jun-11
*/
if (!defined('INCLUDED776')) die ('Fatal error.');

$allowedToBan=TRUE;
if(isset($excludeBanning) and in_array($user_id, $excludeBanning)) $allowedToBan=FALSE;

if(($user_id==1 or $isMod==1) and $allowedToBan){

if(isset($_GET['step'])) $step=$_GET['step']; elseif(isset($_POST['step'])) $step=$_POST['step']; else $step='';

if($step=='' or $step=='banUsr1' or $step=='banUsr2') $title.=$l_ban;
if($step=='deleteban1' or $step=='deleteban2') $title.=$l_unsetBan;

if($step=='banUsr2'){

$warning='';
foreach($_POST as $key=>$val) $$key=operate_string($val);
if (preg_match("/^[0-9.+]+$/", $banip) and trim($banip)!='0') {
$thisIp=$banip; $thisIpMask=array($banip,$banip,$banip);
if(db_ipCheck($thisIp,$thisIpMask,$user_id)) {
$errorMSG=$l_IpExists;
$correctErr="<a href=\"{$main_url}/{$indexphp}action=banip&amp;banip={$thisIp}\" class=\"mnblnk\">{$l_back}</a>";
$tpl=makeUp('main_warning');
}
else{
$fs=insertArray(array('banip','banreason'),$Tb);
header("{$rheader}{$main_url}/{$indexphp}action=banip&step=deleteban1&justban={$insres}#ban_{$insres}");
exit;
//$errorMSG=($fs==0?$l_IpBanned:$l_mysql_error);
//$correctErr="<a href=\"{$main_url}/{$indexphp}action=banip\">{$l_ban}</a> {$l_sepr} <a href=\"{$main_url}/{$indexphp}action=banip&amp;step=deleteban1\">{$l_unsetBan}</a>";
//$step='deleteban1';
}
}
else{
$warning=$l_incorrectIp;
$step='';
}

}

if($step=='deleteban2'){

$banip=(isset($_POST['banip'])?$_POST['banip']:array());
$i=0;
$row=0;
if (sizeof($banip)>0) {
foreach($banip as $key=>$val){
$delban[$i]=$key;
$i++;
}
$xtr=getClForums($delban,'','','id','or','=');
$row=db_delete($Tb,$xtr);
}

unset($xtr);
$step='deleteban1';

}

if($step=='deleteban1'){

$warning='';
$banipID='';
$bannedIPs='';
if ($banned=db_simpleSelect(0,$Tb,'id,banip,banreason','','','','banip')) {
do {

if(preg_match("#^[0-9]+$#", $banned[1])) {
$t1='<strong>';
$t2='</strong>';
}
else{
$t1='';
$t2='';
}

if(trim($banned[2])!='') $banned[2]='('.$banned[2].')';
if(isset($_GET['justban']) and $banned[0]==(int)$_GET['justban']) $mrk='<span class="warning">'.$l_IpBanned.$brtag.$brtag.'</span>'; else $mrk='';
$bannedIPs.='<a id="ban_'.$banned[0].'"></a><table><tr><td><input type="checkbox" name="banip['.$banned[0].']"  style="margin-right:5px" /></td><td>'.$t1.$banned[1].$t2.'</td></tr><tr><td>&nbsp;</td><td><span class="txtSm">'.$banned[2]."</span></td></tr><tr><td>&nbsp;</td><td>{$mrk}</td></tr></table>\n";
//if($banned[2]!='') $bannedIPs.=$brtag;
}
while($banned=db_simpleSelect(1));
$tpl=makeUp('admin_deleteban1');
}
else {
$errorMSG=$l_noBans;
$correctErr="<a href=\"{$main_url}/{$indexphp}action=banip\" class=\"mnblnk\">{$l_back}</a>";
$tpl=makeUp('main_warning');
}

}

if($step=='banUsr1' or $step==''){
if(!isset($warning)) $warning='';
$banip=(isset($_GET['banip'])?preg_replace("#[^0-9.]#", '', trim($_GET['banip'])):'');
$banreason=(isset($_GET['banreason'])?$_GET['banreason']:'');
$tpl=makeUp('admin_banusr1');
}

}
else{
$errorMSG=$l_forbidden; $correctErr=$backErrorLink;
$title.=$l_forbidden; $loginError=1;
$tpl=makeUp('main_warning');
}

echo load_header();
if(isset($is_mobile) and $is_mobile) $tpl=preg_replace('#<!--desktop-->(.+?)<!--/desktop-->#is', '', $tpl);
echo ParseTpl($tpl);
return;

?>