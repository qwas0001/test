<?php
/*
This file is part of miniBB. miniBB is free discussion forums/message board software, provided with no warranties.
See COPYING file for more details.
Copyright (C) 2018 Paul Puzyrev www.minibb.com
Latest File Update: 2018-Dec-10
*/
if (!defined('INCLUDED776')) die ('Fatal error.');

$version='3.4';

if($DB=='mysql' or $DB=='mysqli' or $DB=='pgsql') $caseComp='lower'; elseif($DB=='mssql') $caseComp='lcase';

//--------------->
function makeUp($name,$addDir='') {
if($addDir=='') {
if(isset($GLOBALS['is_mobile']) and $GLOBALS['is_mobile'] and substr($name,0,5)=='email') $addDir=$GLOBALS['pathToFiles'].'templates/';
elseif(isset($GLOBALS['pathToTpl'])) $addDir=$GLOBALS['pathToTpl'];
else $addDir=$GLOBALS['pathToFiles'].'templates/';
}
if(isset($GLOBALS['forumClone']) and $GLOBALS['forumClone']!='' and ($name=='main_header' or $name=='main_footer') ) $addDir=$GLOBALS['pathToFiles'].$GLOBALS['forumClone'].'/templates/';
if (substr($name,0,5)=='email') $ext='txt'; else $ext='html';
$fload=$addDir.$name.'.'.$ext;
if(!file_exists($fload)) {
//an attempt to look up in the original folder
$fload=$GLOBALS['pathToFiles'].'templates/'.$name.'.'.$ext;
}
if(file_exists($fload)) {
$fd=fopen($fload, 'r');
$tpl=fread($fd, filesize($fload));
fclose($fd);
//parsing for standards compatible email templates
if($ext=='txt' and substr_count($tpl, "\r\n")>0) $tpl=str_replace("\r\n", "\n", $tpl);
return $tpl;
}
else die ("TEMPLATE NOT FOUND: $fload");
}

//--------------->
function ParseTpl($tpl){
$qs=array();
$qv=array();
$ex=explode ('{$',$tpl);
$exs=sizeof($ex);
for ($i=0; $i<$exs; $i++) {
if (substr_count($ex[$i],'}')>0) {
$xx=explode('}',$ex[$i]);
if (substr_count($xx[0],'[')>0) {
$clr=explode ('[',$xx[0]); $sp=str_replace('$','',substr($clr[1],0,strlen($clr[1])-1)); if(!is_integer($sp) and isset($GLOBALS[$sp])) $sp=$GLOBALS[$sp]; $clr=$clr[0];
if (!in_array($clr,$qs)) $qs[]=$clr;
if(isset($GLOBALS[$clr][$sp])) $to=$GLOBALS[$clr][$sp]; else $to='';
}
else {
if(!in_array($xx[0], $qv)) $qv[]=$xx[0];
if(isset($GLOBALS[$xx[0]])) $to=$GLOBALS[$xx[0]]; else $to='';
}
$tpl=str_replace('{$'.$xx[0].'}', $to, $tpl);
}
}
return $tpl;
}

//--------------->
function load_header() {
//we need to load this template separately, because we load page title
if(!isset($GLOBALS['forum'])) $GLOBALS['forum']=0;
if(!isset($GLOBALS['topic'])) $GLOBALS['topic']=0;
if(!isset($GLOBALS['page'])) $GLOBALS['page']=0;
if(isset($GLOBALS['is_mobile']) and $GLOBALS['is_mobile']) $isMobileHd=TRUE; else $isMobileHd=FALSE;
foreach($GLOBALS['l_menu'] as $key=>$val) $GLOBALS['l_menu'.$key]=$val;

define('HEADER_CALLED', 1);

if(!isset($GLOBALS['adminPanel'])) $GLOBALS['adminPanel']=0;

if(!($GLOBALS['action']=='' and $GLOBALS['page']==PAGE1_OFFSET+1) or (isset($GLOBALS['adminPanel']) and $GLOBALS['adminPanel']==1) or (isset($_POST['mode']) and $_POST['mode']=='login') ) {
if(!$isMobileHd) $GLOBALS['l_menu'][0]="<a href=\"{$GLOBALS['main_url']}/{$GLOBALS['startIndex']}\" class=\"mnblnk\">{$GLOBALS['l_menu'][0]}</a> {$GLOBALS['l_sepr']} ";
else $GLOBALS['l_menu'][0]="<option value=\"{$GLOBALS['main_url']}/{$GLOBALS['startIndex']}\">{$GLOBALS['l_menu'][0]}</option>";
}
else $GLOBALS['l_menu'][0]='';

if($GLOBALS['action']!='stats') {
if(!$isMobileHd) $GLOBALS['l_menu'][3]="<a href=\"{$GLOBALS['main_url']}/{$GLOBALS['indexphp']}action=stats\" class=\"mnblnk\">{$GLOBALS['l_menu'][3]}</a> {$GLOBALS['l_sepr']} ";
else $GLOBALS['l_menu'][3]="<option value=\"{$GLOBALS['main_url']}/{$GLOBALS['indexphp']}action=stats\">{$GLOBALS['l_menu'][3]}</option>";
}
else $GLOBALS['l_menu'][3]='';


if($GLOBALS['viewTopicsIfOnlyOneForum']==1 and $GLOBALS['action']=='vtopic') {
$GLOBALS['l_menu'][7]="<a href=\"#newtopic\" class=\"mnblnk\">{$GLOBALS['l_menu'][7]}</a> ".$GLOBALS['l_sepr'].' ';
}
elseif(isset($GLOBALS['nTop']) and $GLOBALS['nTop']==1 and (!isset($_GET['showSep']) or $_GET['showSep']!=1)){
if($GLOBALS['action']=='vtopic' and isset($_GET['showSep'])) $GLOBALS['l_menu'][7]=(isset($GLOBALS['newTopicLink'])?$GLOBALS['newTopicLink'].' '.$GLOBALS['l_sepr'].' ':'');
elseif($GLOBALS['action']=='vtopic') $GLOBALS['l_menu'][7]="<a href=\"#newtopic\" class=\"mnblnk\">{$GLOBALS['l_menu'][7]}</a> {$GLOBALS['l_sepr']} ";
elseif($GLOBALS['action']=='vthread') $GLOBALS['l_menu'][7]="<a href=\"#newreply\" class=\"mnblnk\">{$GLOBALS['l_reply']}</a> {$GLOBALS['l_sepr']} ";
else $GLOBALS['l_menu'][7]='';
}
elseif(isset($GLOBALS['mTop']) and $GLOBALS['mTop']==1 and $GLOBALS['action']=='') $GLOBALS['l_menu'][7]="<a href=\"#newtopic\" class=\"mnblnk\">{$GLOBALS['l_menu'][7]}</a> {$GLOBALS['l_sepr']} ";
else $GLOBALS['l_menu'][7]='';
if($isMobileHd){
if($GLOBALS['l_menu'][7]!='') {
$GLOBALS['l_menu'][7]=preg_replace("#<a href=\"(.+?)\"(.+?)>(.+?)</a>#", '<a href="\\1"><img src="'.$GLOBALS['main_url'].'/img/mobi/button_newtopic.png" alt="" title="" /></a>', $GLOBALS['l_menu'][7]);
}
}


if($GLOBALS['action']!='search') {
if(!$isMobileHd) $GLOBALS['l_menu'][1]="<a href=\"{$GLOBALS['main_url']}/{$GLOBALS['indexphp']}action=search\" class=\"mnblnk\">{$GLOBALS['l_menu'][1]}</a> {$GLOBALS['l_sepr']} ";
else $GLOBALS['l_menu'][1]="<option value=\"{$GLOBALS['main_url']}/{$GLOBALS['indexphp']}action=search\">{$GLOBALS['l_menu'][1]}</option>";
}
else $GLOBALS['l_menu'][1]='';

if($GLOBALS['action']!='registernew' and $GLOBALS['user_id']==0 and $GLOBALS['adminPanel']!=1 and $GLOBALS['enableNewRegistrations']) {
if(!$isMobileHd) $GLOBALS['l_menu'][2]="<a href=\"{$GLOBALS['main_url']}/{$GLOBALS['indexphp']}action=registernew\" class=\"mnblnk\" rel=\"nofollow\">{$GLOBALS['l_menu'][2]}</a> {$GLOBALS['l_sepr']} ";
else $GLOBALS['l_menu'][2]="<option value=\"{$GLOBALS['main_url']}/{$GLOBALS['indexphp']}action=registernew\">{$GLOBALS['l_menu'][2]}</option>";
}
else $GLOBALS['l_menu'][2]='';

if($GLOBALS['action']!='manual') {
if(isset($GLOBALS['mod_rewrite']) and $GLOBALS['mod_rewrite']) $urlp=$GLOBALS['manualIndex']; else $urlp="{$GLOBALS['indexphp']}action=manual";
if(!$isMobileHd) $GLOBALS['l_menu'][4]="<a href=\"{$GLOBALS['main_url']}/{$urlp}\" class=\"mnblnk\">{$GLOBALS['l_menu'][4]}</a> {$GLOBALS['l_sepr']} ";
else $GLOBALS['l_menu'][4]="<option value=\"{$GLOBALS['main_url']}/{$urlp}\">{$GLOBALS['l_menu'][4]}</option>";
}
else $GLOBALS['l_menu'][4]='';

if( ($GLOBALS['action']=='prefs' and isset($GLOBALS['adminUser']) and $GLOBALS['adminUser']==0) or $GLOBALS['user_id']==0 or !$GLOBALS['enableProfileUpdate']) {
$GLOBALS['l_menu'][5]='';
}
else {
if(!$isMobileHd) $GLOBALS['l_menu'][5]="<a href=\"{$GLOBALS['main_url']}/{$GLOBALS['indexphp']}action=prefs\" class=\"mnblnk\">{$GLOBALS['l_menu'][5]}</a> {$GLOBALS['l_sepr']} ";
else $GLOBALS['l_menu'][5]="<option value=\"{$GLOBALS['main_url']}/{$GLOBALS['indexphp']}action=prefs\">{$GLOBALS['l_menu'][5]}</option>";
}

if($GLOBALS['user_id']!=0) {
if(!$isMobileHd) $GLOBALS['l_menu'][6]="<a href=\"{$GLOBALS['main_url']}/{$GLOBALS['indexphp']}mode=logout\" class=\"mnblnk\">{$GLOBALS['l_menu'][6]}</a> {$GLOBALS['l_sepr']} ";
else $GLOBALS['l_menu'][6]="<option value=\"{$GLOBALS['main_url']}/{$GLOBALS['indexphp']}mode=logout\">{$GLOBALS['l_menu'][6]}</option>";
}
else $GLOBALS['l_menu'][6]='';

if (!isset($GLOBALS['title']) or $GLOBALS['title']=='') $GLOBALS['title']=$GLOBALS['sitename'];
if(isset($GLOBALS['includeHeader']) and $GLOBALS['includeHeader']!='') { include($GLOBALS['includeHeader']); return; }

$tplMenu=makeUp('main_header');

if((isset($GLOBALS['is_mobile_test']) and $GLOBALS['is_mobile_test']) or (isset($GLOBALS['is_mobile_browser']) and $GLOBALS['is_mobile_browser'])){
}
else{
$tplMenu=preg_replace("#<!--mobileMenuSwitch-->(.+?)<!--/mobileMenuSwitch-->#is", '', $tplMenu);
}

return ParseTpl($tplMenu);
}

//--------------->
function getAccess($clForums, $clForumsUsers, $user_id){
$forb=array();
$acc='n';
if ($user_id!=1 and sizeof($clForums)>0){
foreach($clForums as $f){
if (isset($clForumsUsers[$f]) and !in_array($user_id, $clForumsUsers[$f])){
$forb[]=$f; $acc='m';
}
}
}
if ($acc=='m') return $forb; else return $acc;
}

//--------------->
function getIP(){
$ip1=trim($_SERVER['REMOTE_ADDR']);
if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $ip2=trim($_SERVER['HTTP_X_FORWARDED_FOR']); else $ip2='';
if($ip2!='' and preg_match("/^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$/", $ip2) and ip2long($ip2)!=-1) $finalIP=$ip2;
elseif($ip1!='' and preg_match("/^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$/", $ip1) and ip2long($ip1)!=-1) $finalIP=$ip1;
else $finalIP='';
if($finalIP!='') $finalIP=substr($finalIP,0,15);
if(!preg_match("/^[0-9.]+$/", $finalIP)) $finalIP='0.0.0.0';
return $finalIP;
}

//--------------->
if(!isset($timeDiff)) $currentTime=time(); else $currentTime=time()+$timeDiff;
$GLOBALS['today']=date('Y-m-d', $currentTime);
$GLOBALS['yesterday']=date('Y-m-d', $currentTime-86400);

function convert_date($dateR){

if(isset($GLOBALS['disableDates']) and $GLOBALS['disableDates'] and $GLOBALS['user_id']==0) return '';

else{

if(!isset($GLOBALS['engMon'])) $GLOBALS['engMon']=array('January','February','March','April','May','June','July','August','September','October','November','December',' ');
if(!isset($GLOBALS['months'])) {
$GLOBALS['months']=explode (':', $GLOBALS['l_months']);
$GLOBALS['months'][]='&nbsp;';
}

$dfval=strtotime($dateR);
$execDateConv=TRUE;

if(function_exists('convert_date_back') and !defined('DISABLE_CONVERT_DATE_BACK')) {
$dateR=convert_date_back($dfval);
if($dateR!='') $execDateConv=FALSE;
}
if($execDateConv) {

if(isset($GLOBALS['timeDiff']) and $GLOBALS['timeDiff']!=0) {
$dfval=$dfval+$GLOBALS['timeDiff'];
$dateRF=date('Y-m-d H:i:s', $dfval);
}
else $dateRF=$dateR;

if(isset($GLOBALS['dateOnlyFormat']) and isset($GLOBALS['l_today']) and isset($GLOBALS['l_yesterday'])) {
if(isset($GLOBALS['today']) and substr($dateRF, 0, 10)==$GLOBALS['today']) $dateR=$GLOBALS['l_today'].' '.date($GLOBALS['timeOnlyFormat'], $dfval);
elseif(isset($GLOBALS['yesterday']) and substr($dateRF, 0, 10)==$GLOBALS['yesterday']) $dateR=$GLOBALS['l_yesterday'].' '.date($GLOBALS['timeOnlyFormat'], $dfval);
else {
$dateR=date($GLOBALS['dateFormat'], $dfval);
$dateR=str_replace($GLOBALS['engMon'],$GLOBALS['months'],$dateR);
}
}
else {
$dateR=date($GLOBALS['dateFormat'], $dfval);
$dateR=str_replace($GLOBALS['engMon'],$GLOBALS['months'],$dateR);
}

}
return $dateR;
}

}

//--------------->
function pageChk($page,$numRows,$viewMax){
if($numRows>0 and ($page>PAGE1_OFFSET+1 or $page<PAGE1_OFFSET+1)){
$max=ceil($numRows/$viewMax)+PAGE1_OFFSET;
if ($page<PAGE1_OFFSET+1) return PAGE1_OFFSET+1;
elseif($page>$max) return $max;
else return $page;
}
else return PAGE1_OFFSET+1;
}

//--------------->
function pgRng($rng, $pars){
if(isset($GLOBALS['l_pageN'])) $l_page=$GLOBALS['l_pageN']; else $l_page=$GLOBALS['l_page'];
$c='';
foreach($rng as $r) {
if($r+PAGE1_OFFSET==$pars[0]) $c.=(!$pars[6]||$pars[0]>PAGE1_OFFSET+1?$pars[5]:'').'<strong>'.$r.'</strong>';
else $c.=$pars[2].'<a href="'.call_user_func('add'.$pars[3].'URLPage', $pars[1], $r+PAGE1_OFFSET).'" class="navCell mnblnk mnblnkn" title="'.$l_page.' #'.$r.'">'.$r.'</a>';
}
return $c;
}

//--------------->
function pageNav($page, $numRows, $url, $viewMax, $navCell=FALSE, $pageType='Gen'){
//$htmlExt replaced with $pageType
if(isset($GLOBALS['is_mobile']) and $GLOBALS['is_mobile']) $isMobileHd=TRUE; else $isMobileHd=FALSE;
if(isset($GLOBALS['l_pageN'])) $l_page=$GLOBALS['l_pageN']; else $l_page=$GLOBALS['l_page'];
if(!$navCell){
if($isMobileHd) {
$sLim=6;$bLim=4;$cLim=1;
$nbspLim=' ';
if(isset($GLOBALS['l_mobilePreviousPage'])) $prevNav=$GLOBALS['l_mobilePreviousPage']; else $prevNav='&lt;&lt;';
if(isset($GLOBALS['l_mobileNextPage'])) $nextNav=$GLOBALS['l_mobileNextPage']; else $nextNav='&gt;&gt;';
}
else {
$sLim=11;$bLim=5;$cLim=3;
//$prevNav='&laquo;&laquo;';
$prevNav=$GLOBALS['l_previousPage'];
//$nextNav='&raquo;&raquo;';
$nextNav=$GLOBALS['l_nextPage'];
}
$stp=1;
}
else{
$sLim=6;$bLim=4;$cLim=1;
$stp=2;
}
$nbspLim='&nbsp;&nbsp;';
$pageNav='';
$frn=$nbspLim;
$spl=$nbspLim.'...';

if((integer)($numRows/$viewMax)<($numRows/$viewMax)) $pages=(integer)($numRows/$viewMax)+1;
else $pages=(integer)($numRows/$viewMax);

if($page>=$pages+PAGE1_OFFSET) $page=$pages+PAGE1_OFFSET;

if($pages>1){
if($page==-1) $spage=$pages; else $spage=$page-PAGE1_OFFSET;
//$spage - порядковый номер страницы, начинающийся с 1

$pars=array($page, $url, $frn, $pageType, $navCell, $nbspLim, $isMobileHd);

if($spage-1>=1 and !$navCell) {
if(!$isMobileHd) $nbspLim_s=$nbspLim; else $nbspLim_s='';
$pageNav.=$nbspLim_s.'<a href="'.call_user_func('add'.$pageType.'URLPage', $url, $page-1).'" title="'.$l_page.' #'.($spage-1).'" class="mnblnk mnblnkn">'.$prevNav.'</a>';
}

if($pages>$sLim){
if($spage>0 and $spage<$bLim){
$pageNav.=pgRng(range($stp,$bLim), $pars).$spl.pgRng(range($pages-$cLim,$pages), $pars);
}
elseif($spage==$bLim){
$pageNav.=pgRng(range($stp,$bLim+1), $pars).$spl.pgRng(range($pages,$pages), $pars);
}
elseif($spage>$bLim and $spage<=$pages-$bLim-1){
$pageNav.=pgRng(range($stp,1), $pars).$spl.pgRng(range($spage-1,$spage+1), $pars).$nbspLim.'...'.pgRng(range($pages-1,$pages), $pars);
}
elseif($spage==$pages-$bLim){
$pageNav.=pgRng(range($stp,1), $pars).$spl.pgRng(range($spage-1,$spage+1), $pars).$nbspLim.'...'.pgRng(range($pages,$pages), $pars);
}
elseif($spage>$pages-$bLim and $spage<=$pages){
if($spage==$pages-1) { $al=1; $bl=2; }
elseif($spage==$pages) { $al=3; $bl=2; }
else { $al=1; $bl=1; }
$pageNav.=pgRng(range($stp,$al), $pars).$spl.pgRng(range($spage-$bl,$pages), $pars);
}
}
else {
$pageNav.=pgRng(range($stp,$pages), $pars);
}

if($spage+1<=$pages and !$navCell) $pageNav.=$nbspLim.'<a href="'.call_user_func('add'.$pageType.'URLPage', $url, $page+1).'" title="'.$l_page.' #'.($spage+1).'" class="mnblnk mnblnkn">'.$nextNav.'</a>';

//$pageNav=$pN.'&nbsp;'.$GLOBALS['l_page'].' '.($page-PAGE1_OFFSET).' '.$GLOBALS['l_pageOf'].' '.$pages.':'.$pageNav;

if(!$navCell) {
if(!$isMobileHd) { $mtag=':'; $msp='&nbsp;'; } else { $mtag=$GLOBALS['brtag']; $msp=''; }

if(!defined('NO_PAGE_ICON')) $pN=(defined('CUSTOM_PAGE_ICON')?CUSTOM_PAGE_ICON.$msp:'<img src="'.$GLOBALS['main_url'].'/img/page.gif" alt="'.$l_page.'" />'.$msp); else $pN='';
$pageNav='<span class="noWrap txtNr">'.$pN.'&nbsp;'.$l_page.' '.($page-PAGE1_OFFSET).' '.$GLOBALS['l_pageOf'].' '.$pages.'</span>'.$mtag.$pageNav;
}
}
return $pageNav;
}

//---------------------->
if(!function_exists('sendMail')){
function sendMail($email, $subject, $msg, $from_email, $errors_email) {
// Function sends mail with return-path */

if (!isset($GLOBALS['genEmailDisable']) or $GLOBALS['genEmailDisable']!=1){

if(substr_count($from_email,"\n")>0) $from_email=$GLOBALS['reply_to_email'];
if(substr_count($errors_email,"\n")>0) $errors_email=$GLOBALS['reply_to_email'];

if(!isset($GLOBALS['enablePhpMailer'])){
if(isset($GLOBALS['emailCharset'])) $charset="; charset={$GLOBALS['emailCharset']}"; else $charset='';
if(!isset($GLOBALS['eeol'])) $eeol="\r\n"; else $eeol=$GLOBALS['eeol'];
$from_email="From: {$GLOBALS['emailname']} <{$from_email}>{$eeol}Reply-To: {$from_email}{$eeol}Errors-To: {$errors_email}{$eeol}Return-Path: {$errors_email}{$eeol}MIME-Version: 1.0{$eeol}Content-Type: text/plain{$charset}{$eeol}";

$subject=trim(preg_replace("/&#[0-9]+;/", '', $subject));
$msg=trim(preg_replace("/&#[0-9]+;/", '', $msg));

if(!defined('EMAIL_NO_FLAGS')) mail($email, $subject, $msg, $from_email);
else mail($email, $subject, $msg, $from_email, '-r'.$errors_email);
}
else{
if(isset($GLOBALS['enablePhpMailer']['required']) and is_array($GLOBALS['enablePhpMailer']['required'])){
foreach($GLOBALS['enablePhpMailer']['required'] as $fn){
require_once ($fn);
}
$mail = new PHPMailer();
$mail->IsSMTP();
$mail->Host = $GLOBALS['enablePhpMailer']['smtp_host'];
if($GLOBALS['enablePhpMailer']['smtp_auth']) {
$mail->SMTPAuth = $GLOBALS['enablePhpMailer']['smtp_auth'];
$mail->Username = $GLOBALS['enablePhpMailer']['smtp_username'];
$mail->Password = $GLOBALS['enablePhpMailer']['smtp_pass'];
}
if(isset($GLOBALS['enablePhpMailer']['smtp_secure'])) $mail->SMTPSecure = $GLOBALS['enablePhpMailer']['smtp_secure'];
if(isset($GLOBALS['enablePhpMailer']['smtp_port'])) $mail->Port = $GLOBALS['enablePhpMailer']['smtp_port'];
$mail->FromName = $GLOBALS['sitename'];
$mail->From = $from_email;
$mail->AddAddress($email);
$mail->IsHTML(FALSE);
$mail->Subject = $subject;
$mail->Body = $msg;
$mail->Send();
}
}

}
}
}

//---------------------->
function emailCheckBox() {

$checkEmail='';
if($GLOBALS['genEmailDisable']!=1){

if(isset($GLOBALS['sendid']) and is_array($GLOBALS['sendid']) and $GLOBALS['sendid'][2]==$GLOBALS['user_id']) $isInDb=TRUE; else $isInDb=FALSE;

$true0=($GLOBALS['emailusers']>0);
$true1=($GLOBALS['user_id']!=0);
$true2=($GLOBALS['action']=='vtopic' or $GLOBALS['action'] == 'vthread' or $GLOBALS['action']=='ptopic' or $GLOBALS['action']=='pthread');
$true3a=($GLOBALS['user_id']==1 and (!isset($GLOBALS['emailadmposts']) or $GLOBALS['emailadmposts']==0) and !$isInDb);
$true3b=($GLOBALS['user_id']!=1 and !$isInDb);
$true3=($true3a or $true3b);

if ($true0 and $true1 and $true2 and $true3) {
if(isset($GLOBALS['enchecked'])) $chk="checked=\"checked\""; else $chk='';
$checkEmail="<input type=\"checkbox\" name=\"CheckSendMail\" {$chk} /> {$GLOBALS['l_emailNotify']}";
}
elseif($isInDb) $checkEmail="<!--U--><a href=\"{$GLOBALS['main_url']}/{$GLOBALS['indexphp']}action=unsubscribe&amp;topic={$GLOBALS['topic']}&amp;usrid={$GLOBALS['user_id']}\" class=\"mnblnk\">{$GLOBALS['l_unsubscribe']}</a>";
}
return $checkEmail;
}

//---------------------->
function makeValuedDropDown($listArray,$selectName, $additional=''){
$out='';
if(isset($GLOBALS[$selectName])) $curVal=$GLOBALS[$selectName]; else $curVal='';
foreach($listArray as $key=>$val){
if($curVal==$key) $sel=' selected="selected"'; else $sel='';
$out.="<option value=\"$key\"{$sel}>$val</option>";
}
return "<select name=\"$selectName\" id=\"$selectName\" class=\"selectTxt\"{$additional}>$out</select>";
}

//---------------------->
function setCSRFCheckCookie(){
setcookie($GLOBALS['cookiename'].'_csrfchk', '', (time()-2592000), $GLOBALS['cookiepath'], $GLOBALS['cookiedomain'], $GLOBALS['cookiesecure']);
setcookie($GLOBALS['cookiename'].'_csrfchk', substr(preg_replace("[^0-9A-Za-z]", 'A', md5(uniqid(rand()))),0,20), 0, $GLOBALS['cookiepath'], $GLOBALS['cookiedomain'], $GLOBALS['cookiesecure']);
}

//---------------------->
if(!function_exists('get_magic_quotes_gpc')) {
function get_magic_quotes_gpc() {
return 0;
}
}

//--------------->
function addGenURLPage($full_url, $currPageNum, $amp='&amp;'){
if($currPageNum!=PAGE1_OFFSET+1) $full_url.=$amp.'page='.$currPageNum;
return $full_url;
}

if(!function_exists('addForumURLPage')){
function addForumURLPage($full_url, $currPageNum){
//if($currPageNum!=PAGE1_OFFSET+1) $full_url.='_'.$currPageNum.'.html';
//else $full_url.='.html';
$full_url.='_'.$currPageNum.'.html';
return $full_url;
}
}

if(!function_exists('addTopicURLPage')){
function addTopicURLPage($full_url, $currPageNum){
//if($currPageNum!=PAGE1_OFFSET+1) $full_url.='_'.$currPageNum.'.html';
//else $full_url.='.html';
$full_url.='_'.$currPageNum.'.html';
return $full_url;
}
}

if(!function_exists('addExtranavURLPage')){
function addExtranavURLPage($full_url, $currPageNum){
if($currPageNum==PAGE1_OFFSET+1) $full_url=substr($full_url, 0, strlen($full_url) - strlen($GLOBALS['indexphp'])).$GLOBALS['startIndex'];
elseif($currPageNum!=PAGE1_OFFSET+1) $full_url.='page='.$currPageNum;
return $full_url;
}
}

if(!function_exists('addExtranavmrURLPage')){
function addExtranavmrURLPage($full_url, $currPageNum){
if($currPageNum>PAGE1_OFFSET+1) $full_url.='/'.$currPageNum.'/';
else $full_url.='/'.$GLOBALS['startIndex'];
return $full_url;
}
}

if(!function_exists('genForumURL')){
function genForumURL($main_url, $forum_id, $forum_name){
//if($forum_name=='#GET#') $forum_name=getForumTitleById($forum_id);
$full_url=$main_url.'/'.$forum_id;
return $full_url;
}
}

if(!function_exists('genTopicURL')){
function genTopicURL($main_url, $forum_id, $forum_name, $topic_id, $topicTitle){
//if($topicTitle=='#GET#') $topicTitle=getTopicTitleById($topic_id);
$full_url=$main_url.'/'.$forum_id.'_'.$topic_id;
return $full_url;
}
}

if(!function_exists('parseRequestForumURL')){
function parseRequestForumURL($main_url){
$url=explode('/', $_SERVER['REQUEST_URI']);
$urll=sizeof($url);
$url=$url[$urll-1];
return $main_url.'/'.$url;
}
}

if(!function_exists('parseRequestTopicURL')){
function parseRequestTopicURL($main_url){
$url=explode('/', $_SERVER['REQUEST_URI']);
$urll=sizeof($url);
$url=$url[$urll-1];
return $main_url.'/'.$url;
}
}

if(!function_exists('getForumTitleById')){
function getForumTitleById($forum_id){
$fName='';
if($fn=db_simpleSelect(0, $GLOBALS['Tf'], 'forum_name', 'forum_id', '=', $forum_id)) $fName=$fn[0];
return $fName;
}
}

function getTopicTitleById($topic_id){
$tName='';
if($tn=db_simpleSelect(0, $GLOBALS['Tt'], 'topic_title', 'topic_id', '=', $topic_id)) $tName=$tn[0];
return $tName;
}

//--------------->
function convertBBJS($mpf){
$mpfs='';
$mpf1=explode('<!--BBJSBUTTONS-->', $mpf);
if(sizeof($mpf1)>0) {
$mpfs=$mpf1[0];
for($i=1;$i<=sizeof($mpf1)-1;$i++){
$mpfa=explode('<!--/BBJSBUTTONS-->', trim($mpf1[$i]));
$mpfb=str_replace(array(chr(13), chr(10), "'", 'a href', '</a>', '</t', '</d', '</s', '\r', '\n'), array('', '', chr(92)."'", "a'+' h'+'re'+'f", "</'+'a'+'>", "</'+'t", "</'+'d", "</'+'s", '\\\r', '\\\n'), trim($mpfa[0]));
$mpfs.="<script type=\"text/javascript\">\r\n<!--\r\ndocument.write('{$mpfb}');\r\n//-->\r\n</script>".$mpfa[1];
}
}
return $mpfs;
}

//--------------->
if(!function_exists('parseStatsNum')){
function parseStatsNum($num){
if((int)$num<1000) return $num;
else return number_format($num,  0,  '.',  ',');
}
}

//--------------->
function checkModerator($mods,$userId){
if(is_array($mods)){
foreach($mods as $key=>$val) if(is_array($val) and in_array($userId, $val)) return TRUE;
return FALSE;
}
else return FALSE;
}

//--------------->
function operate_string($str, $backMode=FALSE){
$searchArr=array('&', '"', "'", '<', '>');
$replaceArr=array('&amp;', '&quot;', '&#039;', '&lt;', '&gt;');
if($backMode){ $s=$replaceArr; $replaceArr=$searchArr; $searchArr=$s; }
if(!is_array($str)) $str=(string)$str; else $str='';
return str_replace($searchArr, $replaceArr, trim($str));
}

//--------------->
if(!function_exists('display_footer')){
function display_footer(){
global $l_loadingtime;

$freeWareKeys=array(
'Web Forum Software',
'Chat Forum Software',
'Discussion Forum Software',
'Light Forum Script',
'PHP Forum Software',
'Forum Script',
'Forum Software',
'Free Forum Software',
'Open Source Forum Script',
'Simple Bulletin Board',
'Bulletin Board Script',
'Bulletin Board Software',
'Community Script',
'Online Community Software',
'Easy Forum Software',
'Online Community Script'
);

$rndNum=strlen($GLOBALS['sitename']);
$tk=sizeof($freeWareKeys)-1;
while($rndNum>$tk) $rndNum=$rndNum-$tk;
$software=$freeWareKeys[$rndNum];

$GLOBALS['violating_the_copyright_may_result_in_your_criminal_responsibility']=<<<out
<a href="http://www.minibb.com/" target="_blank" class="mnblnk mnblnkn"><img src="{$GLOBALS['main_url']}/img/minibb.png" alt="{$GLOBALS['sitename']} {$GLOBALS['l_poweredBy']} {$software} miniBB &reg;" title="{$GLOBALS['sitename']} {$GLOBALS['l_poweredBy']} {$software} miniBB &reg;" /></a>
out;

if(!isset($GLOBALS['minibb_copyright_txt'])) $GLOBALS['minibb_copyright_txt']=<<<out
{$GLOBALS['l_poweredBy']} <a href="http://www.minibb.com/" target="_blank" title="{$GLOBALS['sitename']} {$GLOBALS['l_poweredBy']} {$software} miniBB &reg;" class="mnblnk">{$software} miniBB</a>&reg;
out;

//Loading footer
$endtime=get_microtime();
$totaltime=sprintf ("%01.3f", ($endtime-$GLOBALS['starttime']));
if(isset($GLOBALS['includeFooter']) and $GLOBALS['includeFooter']!='') include($GLOBALS['includeFooter']);
else {
$mkf=makeUp('main_footer');
if(isset($GLOBALS['is_mobile_samsung']) and $GLOBALS['is_mobile_samsung']){
$mkf=preg_replace('#<!--scrollTop-->(.+?)<!--/scrollTop-->#is', '', $mkf);
}
echo ParseTpl($mkf);
}
}
}

//--------------->
function scanFilePHP($fileName, $bufferLength=1024){

if(!function_exists('checkTags')){
function checkTags($str){

$scanTags=array('php', 'Php', 'PHp', 'PhP', 'PHP', 'pHp', 'pHP', 'phP');

$ret=FALSE;

foreach($scanTags as $s){
if(substr_count($str, '<?'.$s)>0) { $ret=TRUE; break; }
}

return $ret;
}
}

$ret=FALSE;

$handle = fopen($fileName, "r");
if ($handle) {
while (($buffer = fgets($handle, $bufferLength)) !== false) {
$chk=checkTags($buffer);
if($chk) { $ret=TRUE; break; }
}
//if (!feof($handle)) {
// echo "Error: unexpected fgets() fail\n";
//}
fclose($handle);
}

return $ret;
}

//--------------->
if(!function_exists('registerButton')){
function registerButton($user_id, $enableNewRegistrations, $l_menu, $main_url, $indexphp){
$addCancelBtn='';
if(isset($GLOBALS['is_mobile']) and $GLOBALS['is_mobile']){
$addCancelBtn='&nbsp;<input type="button" class="inputButton" value="'.$GLOBALS['l_mobileCancel'].'" onclick="javascript:display_hide(\'mnbl_headtblogin\',\'mnbl_headtb\');" />';
}
if($user_id==0 and $enableNewRegistrations) return "<input type=\"button\" value=\"{$l_menu[2]}\" onclick=\"JavaScript:document.location='{$main_url}/{$indexphp}action=registernew'\" class=\"inputButton\" />{$addCancelBtn}";
else return $addCancelBtn;
}
}

//--------------->
function special_substr($text, $limit){
/* analogue of default substr() with exception it cuts text off not by every symbol, but by actual symbols, even if they are encoded as unicode (something like &#...; is one symbol) */

$total=0;
$returned='';
$foundUni=0;
for($i=0;$i<strlen($text);$i++){
if($text[$i]=='&') $foundUni=1;
if($foundUni==1)  { if($text[$i]==';') { $total++; $foundUni=0; } }  else $total++;
$returned.=$text[$i];
if($total>=$limit) break;
}

return $returned;
}

//--------------->
/*
function substr_unicode_old($str, $s, $l = null) {
if(!isset($GLOBALS['splitExpression'])) $splitExpression='//u'; else $splitExpression=$GLOBALS['splitExpression'];
return join('', array_slice(preg_split($splitExpression, $str, -1, PREG_SPLIT_NO_EMPTY), $s, $l));
}
*/

function substr_unicode($str, $s, $l = null){
if(function_exists('mb_substr')){
return mb_substr($str, $s, $l, 'UTF-8');
}
else{
if(strlen($str) == strlen(utf8_decode($str))){
return substr($str, $s, $l);
}
else{
$r = '/^.{'.(int)$s.'}(.';
$r .= ($l === null) ? '*)$' : '{'.(int)$l.'})';
$r .= '/su';
preg_match($r, $str, $o);
if(isset($o[1])) return $o[1]; else return $str;
}
}
}

//--------------->
function strlen_unicode($str){
if(function_exists('mb_strlen')){
return mb_strlen($str, 'UTF-8');
}
else{
$chkStr=strlen($str);
if($chkStr == strlen(utf8_decode($str))){
return $chkStr;
}
else{
$length = preg_match_all('(.)su', $str, $matches);
//$length = strlen( utf8_decode( $string ) );
return $length;
}
}
}

//--------------->
function strtolower_unicode($str){
if(function_exists('mb_strtolower')){
return mb_strtolower($str, 'UTF-8');
}
else{
return strtolower($str);
}
}

?>