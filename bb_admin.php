<?php
/*
This file is part of miniBB. miniBB is free discussion forums/message board software, supplied with no warranties.
Check COPYING file for more details.
Copyright (C) 2018 Paul Puzyrev. www.minibb.com
Latest File Update: 2018-Oct-31
*/
define ('INCLUDED776',1);

$unset=array('logged_admin', 'isMod', 'user_id', 'langu', 'includeHeader', 'includeFooter', 'mod_rewrite', 'inss', 'insres', 'cook', 'archives', 'rheader', 'allowHyperlinksProfile', 'set_archive', 'setArchive', 'brtag', 'url_follow', 'adminHTML', 'chunkStr', 'splitExpression');
for($i=0;$i<sizeof($unset);$i++) if(isset(${$unset[$i]})) { ${$unset[$i]}=''; unset(${$unset[$i]}); }

$metaRobots='NOINDEX,NOFOLLOW';

function get_microtime() {
$mtime=explode(' ',microtime());
return $mtime[1]+$mtime[0];
}

$starttime=get_microtime();

define ('ADMIN_PANEL',1);

if(isset($_POST['setArchive'])) $setArchive=$_POST['setArchive']; elseif(isset($_GET['setArchive'])) $setArchive=$_GET['setArchive']; else $setArchive='';

if($setArchive!='') define('ARCHIVE_EDIT', $setArchive);

include ('./setup_options.php');

if(!isset($GLOBALS['indexphp'])) $indexphp='index.php?'; else $indexphp=$GLOBALS['indexphp'];
if(!isset($rheader)) $rheader='Location:';
if(!isset($brtag)) $brtag='<br />';
if(!isset($bbimgmrg)) $bbimgmrg=10;
if(!isset($chunkStr)) $chunkStr='<wbr>';
if(!isset($splitExpression)) $splitExpression='//u';

if(!isset($_SERVER['QUERY_STRING'])) $_SERVER['QUERY_STRING']='';
$queryStr=(isset($_POST['queryStr'])?rawurlencode(rawurldecode($_POST['queryStr'])):rawurlencode($_SERVER['QUERY_STRING']));

if(isset($_COOKIE[$cookiename.'_csrfchk'])) $csrfval=$_COOKIE[$cookiename.'_csrfchk']; else $csrfval='';
if(isset($_POST['csrfchk'])) $csrfchk=$_POST['csrfchk']; elseif (isset($_GET['csrfchk'])) $csrfchk=$_GET['csrfchk']; else $csrfchk='';

$sgcp=session_get_cookie_params();
if(isset($sgcp['httponly'])) $httponlycookie=TRUE; else $httponlycookie=FALSE;

include ($pathToFiles."setup_$DB.php");
include ($pathToFiles.'bb_cookie.php');
include ($pathToFiles."bb_functions.php");
include ($pathToFiles.'bb_specials.php');
include ($pathToFiles."lang/$lang.php");

if(isset($_POST['mode'])) $mode=$_POST['mode']; elseif(isset($_GET['mode'])) $mode=$_GET['mode']; else $mode='';
if(isset($_POST['action'])) $action=$_POST['action']; elseif(isset($_GET['action'])) $action=$_GET['action']; else $action='start_admin_panel';

$l_adminpanel_link='';
$warning='';

$thisIp=getIP();

if(!isset($fIconWidth)) $fIconWidth=16;
if(!isset($fIconHeight)) $fIconHeight=16;
if(!isset($allowHyperlinksProfile)) $allowHyperlinksProfile=$allowHyperlinks;
if(!isset($url_follow)) { $url_follow=FALSE; $relFollowUrl=' rel="nofollow"'; } else { $url_follow=TRUE; $relFollowUrl=''; }

//-----
function getForumIcons() {
$iconList='';

if($handle=@opendir($GLOBALS['pathToFiles'].$GLOBALS['archiveImgFld'].'img/forum_icons')) {
//$ss=0;
while (($file=readdir($handle))!==false) {
if ($file != '.' && $file != '..' and (substr(strtolower($file),-3)=='gif' OR substr(strtolower($file),-3)=='jpg' OR substr(strtolower($file),-4)=='jpeg' OR substr(strtolower($file),-3)=='png')) {
$iconList.="<a href=\"JavaScript:paste_strinL('{$file}')\" onmouseover=\"window.status='{$GLOBALS['l_forumIcon']}: {$file}'; return true\" class=\"mnblnkn\"><img src=\"{$GLOBALS['main_url']}/{$GLOBALS['archiveImgFld']}img/forum_icons/{$file}\" alt=\"{$file}\" /></a>&nbsp;&nbsp;";
/*
$ss++;
if ($ss==5) {
$ss=0;
$iconList.=$GLOBALS['brtag']."\n";
}
*/
}
}
closedir($handle);
if ($iconList=='') $iconList=$GLOBALS['l_accessDenied'];
}
else $iconList=$GLOBALS['l_accessDenied'];
return $iconList;
}

//-----
function get_forums_fast_preview () {
// Get forums fast order preview in admin panel
global $result;
$fast='';

if($GLOBALS['viewTopicsIfOnlyOneForum']==1) $fast="{$brtag}{$GLOBALS['l_topicsWillBeDisplayed']}";

else{
if ($row=db_simpleSelect(0,$GLOBALS['Tf'],'forum_id, forum_name, forum_desc, forum_order, forum_icon, forum_group','','','','forum_order')){
do{

if($row[5]!='') $fast.="<img src=\"{$GLOBALS['main_url']}/img/p.gif\" class=\"vmiddle\" alt=\"\" />&nbsp;<strong>{$row[5]}</strong>{$GLOBALS['brtag']}";

$fast.="<a href=\"{$GLOBALS['main_url']}/{$GLOBALS['bb_admin']}action=move&amp;where=1&amp;forumID={$row[0]}{$GLOBALS['addArcUrl']}\" class=\"mnblnk mnblnkn\">&uarr;</a>&nbsp;&nbsp;<a href=\"{$GLOBALS['main_url']}/{$GLOBALS['bb_admin']}action=move&amp;where=0&amp;forumID={$row[0]}{$GLOBALS['addArcUrl']}\" class=\"mnblnk mnblnkn\">&darr;</a>&nbsp;&nbsp;<img src=\"{$GLOBALS['main_url']}/{$GLOBALS['archiveImgFld']}img/forum_icons/{$row[4]}\" alt=\"{$row[1]}\" title=\"{$row[1]}\" id=\"forum{$row[0]}\" class=\"vmiddle\" />&nbsp;<b><a href=\"{$GLOBALS['main_url']}/{$GLOBALS['bb_admin']}action=editforum2&amp;forumID={$row[0]}{$GLOBALS['addArcUrl']}\" class=\"mnblnk\">{$row[1]}</a></b> [ORDER: {$row[3]}] - <span class=\"txtSm\">{$row[2]}&nbsp;</span>{$GLOBALS['brtag']}";

}
while($row=db_simpleSelect(1));
}

}
return '<div class="postedText">'.$fast.'</div>';
}

//-----

switch ($mode) {
case 'logout':
deleteMyCookie();
if(isset($metaLocation)) { $meta_relocate="{$main_url}/{$bb_admin}"; echo ParseTpl(makeUp($metaLocation));
exit; } else { header("{$rheader}{$main_url}/{$bb_admin}"); exit; }

case 'login':
if ($mode=='login') {
if(!isset($_POST['adminusr'])) $_POST['adminusr']='';
if(!isset($_POST['adminpwd'])) $_POST['adminpwd']='';

if(strlen($admin_pwd)==32) { 
$encodePass=FALSE;
$comparePass=writeUserPwd($_POST['adminpwd']);
} else {
$encodePass=TRUE;
$comparePass=$_POST['adminpwd'];
}

//echo $comparePass;

if ($_POST['adminusr']==$admin_usr and $comparePass==$admin_pwd) {

$cook=$admin_usr.'|'.$comparePass.'|'.$cookieexptime;
deleteMyCookie();
setMyCookie($admin_usr,$admin_pwd,$cookieexptime,$encodePass);
setCSRFCheckCookie();
if(isset($metaLocation)) { $meta_relocate="{$main_url}/{$bb_admin}"; echo ParseTpl(makeUp($metaLocation));
exit; } else { header("{$rheader}{$main_url}/{$bb_admin}"); exit; }
}
else {
$warning=$l_incorrect_login;
}
} // if mode=login, for preventing login checkout

default:

$user_id=0;
$logged_admin=0;
user_logged_in();
if(isset($langu) and file_exists($pathToFiles."lang/{$langu}.php")) $lang=$langu;
include ($pathToFiles."lang/$lang.php");

if($logged_admin==1){

if(($action=='start_admin_panel' or $action=='editforum1' or $action=='editforum2' or $action=='editforum3' or $action=='addforum1' or $action=='addforum2') and $setArchive!='' and isset($archives[$setArchive])){

$addArcField='<input type="hidden" name="setArchive" value="'.$setArchive.'" />';
$addArcMark='<span class="txtSm">/ <strong>'.$archives[$setArchive][0].'</strong>'.$brtag.'</span>';
$addArcUrl='&amp;setArchive='.$setArchive;
$addArcUrlAdm='setArchive='.$setArchive;

$archiveImgFld='';
if(isset($archivesFolder) and $archivesFolder!='') $archiveImgFld.=$archivesFolder.'/';
$archiveImgFld.=$setArchive.'/';

}
else{
$addArcField=''; $addArcMark=''; $addArcUrl=''; $archiveImgFld=''; $addArcUrlAdm='';
}

$l_adminpanel_link="<p><a href=\"{$main_url}/{$bb_admin}{$addArcUrlAdm}\" class=\"mnblnk\">".$l_adminpanel."</a></p>";

$isMod=0;
$forum=0;
$topic=0;
if(!defined('PAGE1_OFFSET')) define('PAGE1_OFFSET', 0);
$page=PAGE1_OFFSET+1;
$user_usr=$admin_usr;
include ($pathToFiles.'bb_plugins.php');


switch ($action) {
case 'addforum1':
$iconList=getForumIcons();
$text2=ParseTpl(makeUp('admin_addforum1'));
break;

case 'addforum2':
if($csrfchk=='' or $csrfchk!=$csrfval) die('Can not proceed: possible CSRF/XSRF attack!');
$iconList=getForumIcons();

require($pathToFiles.'bb_func_txt.php');

$defFields=array('forum_name', 'forum_desc', 'forum_icon', 'forum_group');

foreach($_POST as $key=>$val) {
if(in_array($key, $defFields)){
$posts[$key]=operate_string($val);
$$key=textFilter($val, $post_text_maxlength, $post_word_maxlength, 1, FALSE, 1, $user_id);
}
}

if($forum_name!='') {

if($forum_icon=='') $forum_icon='default.gif';
else $forum_icon=$posts['forum_icon'];

if (file_exists($pathToFiles."{$archiveImgFld}img/forum_icons/{$forum_icon}")) {

$topics_count=0; $posts_count=0;
if($mx=db_simpleSelect(0,$Tf,'count(*)')) $forum_order=$mx[0]+1; else $forum_order=0;

$er=insertArray(array('forum_name','forum_desc','forum_icon','forum_order','topics_count','posts_count', 'forum_group'),$Tf);

if ($er==0) $warning=$l_forum_added.$brtag.$brtag; else $warning=$l_itseemserror.$brtag.$brtag;
$text2=ParseTpl(makeUp('admin_panel'));
}
else {
$warning=$l_error_addforumicon."'".$forum_icon."'";
foreach($posts as $key=>$val) $$key=$val;
$text2=ParseTpl(makeUp('admin_addforum1'));
}
}
else {
$warning=$l_error_addforum;
foreach($posts as $key=>$val) $$key=$val;
$text2=ParseTpl(makeUp('admin_addforum1'));
}
break;

case 'move':
if(isset($_GET['forumID'])) $forumID=(int)$_GET['forumID']; else $forumID=0;
if(isset($_GET['where'])) $where=$_GET['where']; else $where=0;
$c=0;
$num=db_simpleSelect(0,$Tf,'count(*)'); $num=$num[0];

$forums=array();
if($row=db_simpleSelect(0,$Tf,'forum_id, forum_order','','','','forum_order')){
$a=1;
do { $forums[$a]=$row[0]; $a++; }
while($row=db_simpleSelect(1));

$ch=0;

if($where==1){
for($i=1; $i<=sizeof($forums); $i++) {
$d=$i-1;
if($forums[$i]==$forumID){
if(isset($forums[$d])) { $tmp=$forums[$d]; $forums[$d]=$forums[$i]; $forums[$i]=$tmp; $ch=1; }
else { $forums[$num+1]=$forums[$i]; unset($forums[$i]); $ch=1; }
}
if($ch==1) break;
}
}
elseif($where==0){
for($i=1; $i<=sizeof($forums); $i++) {
$d=$i+1;
if($forums[$i]==$forumID){
if(isset($forums[$d])) { $tmp=$forums[$d]; $forums[$d]=$forums[$i]; $forums[$i]=$tmp; $ch=1; }
else { $forums[0]=$forums[$i]; unset($forums[$i]); $ch=1; }
}
if($ch==1) break;
}
}

ksort($forums);
reset($forums);

$forum_order=1;
foreach($forums as $key=>$val){
updateArray(array('forum_order'),$Tf,'forum_id',$val);
$forum_order++;
}

}

if($setArchive=='') $addArcUrl=''; elseif(isset($archives[$setArchive])) $addArcUrl='&setArchive='.$setArchive;
header("{$rheader}{$main_url}/{$bb_admin}action=editforum2&forumID={$forumID}{$addArcUrl}#forum{$forumID}");
exit;
break;

case 'archiveswitch1':
$frm=0;
$nextAction='archiveswitch2';
$l_chooseeditforum=$l_arcForumArchives;
$l_editforum=$l_submit;
$archivesSet=array();
foreach($archives as $key=>$val) $archivesSet[$key]=$val[0];
$listForums=makeValuedDropDown($archivesSet, 'set_archive');
$tt=makeUp('admin_editforum1');
$tt=preg_replace(array("#<select(.+?)>#i", "#</select>#i"), '', $tt);
$text2=ParseTpl($tt);
break;

case 'archiveswitch2':
$warning='';
if(isset($_POST['set_archive']) and isset($archives[$_POST['set_archive']])){
$addArcUrl='&amp;setArchive='.$_POST['set_archive'];
$addArcMark='<span class="txtSm">/ <strong>'.$archives[$_POST['set_archive']][0].'</strong></span>'.$brtag;
}
$text2=ParseTpl(makeUp('admin_panel'));
break;

case 'editforum1':
$frm=0;
$nextAction='editforum2';
include ($pathToFiles.'bb_func_forums.php');
$text2=ParseTpl(makeUp('admin_editforum1'));
break;

case 'editforum2':
require($pathToFiles.'bb_codes.php');

if(isset($_POST['forumID'])) $forumID=(int)$_POST['forumID']+0; elseif(isset($_GET['forumID'])) $forumID=(int)$_GET['forumID']+0; else $forumID=0;
if ($forumID!=0) {

$forumsPreview=get_forums_fast_preview();

if ($row=db_simpleSelect(0,$Tf,'forum_name, forum_desc, forum_icon, forum_group','forum_id','=',$forumID)) {

foreach($row as $key=>$val) {
$row[$key]=deCodeBB($row[$key]);
}

list($forum_name, $forum_desc, $forum_icon, $forum_group)=$row;
//$forum_group=operate_string($forum_group);

$iconList=getForumIcons();

$text2=ParseTpl(makeUp('admin_editforum2'));
}
else {
$warning=$l_noforums.$brtag.$brtag;
$text2=ParseTpl(makeUp('admin_panel'));
}
}
else {
$warning=$l_noforums.$brtag.$brtag;
$text2=ParseTpl(makeUp('admin_panel'));
}
break;

case 'editforum3':
if($csrfchk=='' or $csrfchk!=$csrfval) die('Can not proceed: possible CSRF/XSRF attack!');
$posts=array();
require($pathToFiles.'bb_func_txt.php');

$defFields=array('forum_name', 'forum_desc', 'forum_icon', 'forum_group');

foreach($_POST as $key=>$val) {
//if(get_magic_quotes_gpc()==0) $$key=addslashes(trim($val)); else $$key=trim($val);
if(in_array($key, $defFields)){
$posts[$key]=operate_string($val);
$$key=textFilter($val, $post_text_maxlength, $post_word_maxlength, 1, FALSE, 1, $user_id);
}
}

$forumID=(int)$_POST['forumID']+0;

if (!isset($_POST['deleteforum'])) {

if ($forum_name!='') {

if ($forum_icon=='') $forum_icon='default.gif';
else $forum_icon=$posts['forum_icon'];

if (!file_exists($pathToFiles."{$archiveImgFld}img/forum_icons/{$forum_icon}")) {
$warning=$l_error_addforumicon."'".$forum_icon."'";
}
else {

$fs=updateArray(array('forum_name','forum_desc','forum_icon','forum_group'),$Tf,'forum_id',$forumID);

if($fs>0) $warning=$l_forumUpdated; else $warning=$l_prefsNotUpdated;
}
} // if forum name is set
else {
$warning=$l_error_addforum;
}

$forumsPreview=get_forums_fast_preview();
$iconList=getForumIcons();
foreach($posts as $key=>$val) $$key=$val;
$text2=ParseTpl(makeUp('admin_editforum2'));
}
else {

set_time_limit(0);

$aff=0;

/* Amount of user topics */
$updatedUsers=array();
if($rrr=db_simpleSelect(0,$Tp,'poster_id','forum_id','=',$forumID,'','','poster_id','!=',0)){
do if(!isset($updatedUsers[$rrr[0]])) $updatedUsers[$rrr[0]]=TRUE;
while($rrr=db_simpleSelect(1));
}

/* Sendmails */

if($rrr=db_simpleSelect(0,"$Tt,$Ts","$Tt.topic_id","$Tt.forum_id",'=',$forumID,'','',"$Tt.topic_id",'=',"$Ts.topic_id")){
$ord='';
do $ord.="topic_id={$rrr[0]} or "; while($rrr=db_simpleSelect(1));
$ord=substr($ord,0,strlen($ord)-4);
$aff+=db_delete($Ts,$ord,'','');
}

/* Forums, posts, topics */

$aff+=db_delete($Tf,'forum_id','=',$forumID);
$aff+=db_delete($Tt,'forum_id','=',$forumID);
$aff+=db_delete($Tp,'forum_id','=',$forumID);

foreach($updatedUsers as $uu=>$val) {
db_calcTotalUserAmount($uu, $Tu, $Tt, $Tp, $Taus, $setArchive);
$aff++;
}

if ($aff>0) $warning=$l_forumdeleted.' ("'.stripslashes($forum_name).'") - '."$l_del $aff $l_rows".$brtag.$brtag; else $warning=$l_itseemserror.$brtag.$brtag;
$text2=ParseTpl(makeUp('admin_panel'));
}
break;


case 'delsendmails1':
if (!isset($_POST['warning'])) $warning='';
if (!isset($_POST['delemail'])) $delemail='';
$title=$l_deleteSendmails;
$warning=$l_deleteSendmails;
$text2=ParseTpl(makeUp('admin_sendmails1'));
break;

case 'delsendmails2':
$delemail=(isset($_POST['delemail'])?operate_string($_POST['delemail']):'');
if(substr_count($delemail, '*')>0) {
$delemaild=str_replace('*', '%', $delemail);
$cond=' like ';
}
else{
$delemaild=$delemail;
$cond='=';
}

$numdel=0;

if($delemail!='' and $rw=db_simpleSelect(0,$Tu,$dbUserId,$caseComp.'('.$dbUserSheme['user_email'][1].')',$cond,strtolower($delemaild))) {
do{
$fs=db_delete($Ts,'user_id','=',$rw[0]);
$numdel+=$fs;
}
while($rw=db_simpleSelect(1));
$row=$delemail;
}
elseif(isset($_POST['allsubs']) and (int)$_POST['allsubs']==1) {
$numdel=db_delete($Ts);
$row='ALL';
}
else {
$warning=$l_emailNotExists;
$text2=ParseTpl(makeUp('admin_sendmails1'));
break;
}

$warning=$l_completed." ({$row} &mdash; {$numdel})".$brtag.$brtag;
$text2=ParseTpl(makeUp('admin_panel'));
break;

case 'restoreData':
${$dbUserSheme['username'][1]}=$admin_usr;
${$dbUserSheme['user_password'][1]}=writeUserPwd($admin_pwd);
${$dbUserSheme['user_email'][1]}=$admin_email;
${$dbUserDate}=date('Y-m-d H:i:s');
$fields=array($dbUserSheme['username'][1],$dbUserSheme['user_password'][1],$dbUserSheme['user_email'][1]);
if($res=db_simpleSelect(0,$Tu,$dbUserId,$dbUserId,'=',1)) {$ins=1; $fs=updateArray($fields,$Tu,$dbUserId,1); }
else {$fields[]=$dbUserDate; $fields[]=$dbUserId; ${$dbUserId}=1; $ins=0; $fs=insertArray($fields,$Tu); }
if (($fs>0 and $ins==1) OR ($fs==0 and $ins==0)) $warning=$l_prefsUpdated.$brtag.$brtag; else $warning=$l_prefsNotUpdated.$brtag.$brtag;
$text2=ParseTpl(makeUp('admin_panel'));
break;

case 'exportemails':
if (db_simpleSelect(0,$Tu,$dbUserId,$dbUserId,'!=',1)) { $text2=makeUp('admin_export_emails'); }
else { $warning=$l_accessDenied.$brtag.$brtag; $text2=makeUp('admin_panel'); }
$text2=ParseTpl($text2);
break;

case 'exportemails2':
if ($row=db_simpleSelect(0,$Tu,$dbUserSheme['username'][1].','.$dbUserSheme['user_email'][1],$dbUserId,'!=',1,$dbUserId) and isset($_POST['expEmail'])) {
$cont='';
do {
$cont.=$row[1];
if (isset($_POST['expLogin'])) {
if ($_POST['separate']=='comma') $sep=','; else $sep=chr(9);
$cont.=$sep.$row[0];
}
if ($_POST['screen']==1) $cont.=$brtag; else $cont.="\r\n";
}
while ($row=db_simpleSelect(1));

if ($_POST['screen']==0) {
header("Content-Type: DUMP/unknown");
header("Content-Disposition: attachment; filename=".str_replace(' ', '_', $sitename)."_emails.txt");
}
echo $cont; exit;
}
$text2=ParseTpl(makeUp('admin_panel'));
break;

case 'searchusers':
$searchus='id';
$whatDropDown=makeValuedDropDown(array('id'=>'ID','login'=>$l_sub_name,'email'=>$l_email,'inactive'=>$l_inactiveUsers,'registr'=>$l_haventReg, 'notmember'=>$l_member.': ['.$l_no.']'),'searchus',' style="width:200px"');
$warning='';
$text2=ParseTpl(makeUp('admin_searchusers'));
break;

case 'searchusers2':

if(isset($_GET['searchus'])) $searchus=trim($_GET['searchus']); elseif(isset($_POST['searchus'])) $searchus=trim($_POST['searchus']); else $searchus='';
if(!in_array($searchus, array('id', 'login', 'email', 'inactive', 'registr', 'notmember'))) $searchus='';
if(isset($_GET['whatus'])) $whatus=trim($_GET['whatus']); elseif(isset($_POST['whatus'])) $whatus=trim($_POST['whatus']); else $whatus='';
if($whatus!='') $whatus=str_replace(array('>', '<', '%3C', '%3E', "\r", "\n"), '', $whatus);
if(isset($_GET['page'])) $page=(int)$_GET['page']+0; elseif(isset($_POST['page'])) $page=(int)$_POST['page']+0; else $page=PAGE1_OFFSET+1;
if($page<PAGE1_OFFSET+1) $page=PAGE1_OFFSET+1;
if(isset($_GET['sort'])) $sort=(int)$_GET['sort']; elseif(isset($_POST['sort'])) $sort=(int)$_POST['sort']; else $sort=0;
//sort: 0 - asc, 1 - desc

/* Delete users if selected */

if(isset($_POST['delus']) and is_array($_POST['delus']) and sizeof($_POST['delus'])>0) {
$newarr=array();
foreach($_POST['delus'] as $dl) {
$dli=(int)$dl+0;
if($dli!=1 and $dli!=0) $newarr[]=$dli;
}

if(sizeof($newarr)>0){
if(isset($_POST['anchor']) and $_POST['anchor']!=0) $anchor='#u'.((int)$_POST['anchor']+0); else $anchor='';
$xtr=getClForums($newarr,'','',$dbUserId,'or','=');
$row=db_delete($Tu,$xtr);
$row=db_delete($Ts,$xtr);
}
$addSort=($sort==1?'&sort=1':'');
header("{$rheader}{$main_url}/{$bb_admin}action={$action}&searchus={$searchus}&whatus={$whatus}{$addSort}&page={$page}{$anchor}");
exit;
}


$tR=makeUp('admin_searchusersres');

$whatDropDown=makeValuedDropDown(array('id'=>'ID','login'=>$l_sub_name,'email'=>$l_email,'inactive'=>$l_inactiveUsers,'registr'=>$l_haventReg, 'notmember'=>$l_member.': ['.$l_no.']'),'searchus',' style="width:200px"');

$sortSql=($sort==0?'asc':'desc');
$addSort=($sort==1?'&amp;sort=1':'');

$totch=0;
/* for any "dead" users found, we will display a checkbox, which will produce a possibility to delete many users fast at once */

$Results='';
$idArray='';

/* All users */
if($whatus=='' and $searchus!='inactive' and $searchus!='registr' and $searchus!='notmember' and $num=db_simpleSelect(0,$Tu,'count(*)')){

$num=$num[0];
$makeLim=makeLim($page,$num,$viewmaxsearch);
$urlSet="{$main_url}/{$bb_admin}action=searchusers2";
$pageNav=pageNav($page,$num,$urlSet.$addSort,$viewmaxsearch,FALSE);

if ($row=db_simpleSelect(0,$Tu,$dbUserId.','.$dbUserSheme['username'][1].','.$dbUserDate.','.$dbUserSheme['user_password'][1].','.$dbUserSheme['user_email'][1].', '.$dbUserSheme['num_posts'][1],'','','',"{$dbUserId} {$sortSql}",$makeLim)){

do {
$numReplies=$row[5];
$lReplies='&mdash;';
$idArray.=$row[0].', ';

if($numReplies>0){

$delCheckBox='';

$RES1=$result;
$CNT1=$countRes;

if ($lRepl=db_simpleSelect(0,$Tp,'post_time','poster_id','=',$row[0],'post_id DESC',1)) $lReplies=convert_date($lRepl[0]);

}
else {
$totch++;
$delCheckBox="<input type=\"checkbox\" name=\"delus[]\" value=\"{$row[0]}\" />&nbsp;";
}

$Rest=$tR;
$rDate=convert_date($row[2]);
$Results.=ParseTpl($Rest);

if($numReplies>0){
$result=$RES1;
$countRes=$CNT1;
}

}
while ($row=db_simpleSelect(1));
}
$warning=$l_recordsFound.' '.parseStatsNum($num);
}

/* Determine all inactive users, who have posted NOTHING */
elseif ($searchus=='inactive'){
$whatus='';
$makeLim='';
$num=0;
if ($num=db_inactiveUsers(0,'count(*)')) $num=$num[0];
$makeLim=makeLim($page,$num,$viewmaxsearch);
$urlSet="{$main_url}/{$bb_admin}action=searchusers2&amp;whatus={$whatus}&amp;searchus=inactive";
$pageNav=pageNav($page,$num,$urlSet.$addSort,$viewmaxsearch,FALSE);

//index of num_replies
$nr=$dbUserSheme['num_posts'][0];

if ($row=db_inactiveUsers(0,'*',$sortSql)){
$tot=0;
do {
$idArray.=$row[0].', ';
$totch++;
$delCheckBox="<input type=\"checkbox\" name=\"delus[]\" value=\"{$row[0]}\" />&nbsp;";
$Rest=$tR;
$lReplies='&mdash;';
$numReplies=$row[$nr];
$rDate=convert_date($row[$dbUserDateKey]);
$Results.=ParseTpl($Rest);
$tot++;
}
while($row=db_inactiveUsers(1));
}
$warning=$l_recordsFound.' '.parseStatsNum($num);
}

/* Search users by email or username (LIKE condition) */
elseif ($searchus=='email' OR $searchus=='login'){
$tot=0;
$whatx=($searchus=='email'?$dbUserSheme['user_email'][1]:$dbUserSheme['username'][1]);
$whatus=operate_string($whatus);

$makeLim='';
$num=0;
if ($num=db_simpleSelect(0, $Tu, 'count(*)', $whatx, ' like ', '%'.$whatus.'%')) $num=$num[0];
$makeLim=makeLim($page,$num,$viewmaxsearch);
$urlSet="{$main_url}/{$bb_admin}action=searchusers2&amp;whatus={$whatus}&amp;searchus=$searchus";
$pageNav=pageNav($page,$num,$urlSet.$addSort,$viewmaxsearch,FALSE);

if($row=db_simpleSelect(0,$Tu,$dbUserId.','.$dbUserSheme['username'][1].','.$dbUserDate.','.$dbUserSheme['user_password'][1].','.$dbUserSheme['user_email'][1].', '.$dbUserSheme['num_posts'][1], $whatx, ' like ', '%'.$whatus.'%', "{$dbUserId} {$sortSql}", $makeLim)){

do {
$user=$row[0];
$idArray.=$row[0].', ';

$numReplies=$row[5];
$lReplies='&mdash;';

if($numReplies>0){

$delCheckBox='';

$RES1=$result;
$CNT1=$countRes;

if ($lRepl=db_simpleSelect(0,$Tp,'post_time','poster_id','=',$row[0],'post_id DESC',1)) $lReplies=convert_date($lRepl[0]);

}
else {
$totch++;
$delCheckBox="<input type=\"checkbox\" name=\"delus[]\" value=\"{$row[0]}\" />&nbsp;";
}

$Rest=$tR;

$rDate=convert_date($row[2]);
$Results.=ParseTpl($Rest);
$tot++;

if($numReplies>0){
$result=$RES1;
$countRes=$CNT1;
}

}
while ($row=db_simpleSelect(1));
}
$warning=$l_recordsFound.' '.parseStatsNum($num);
}

/* Searching by dead users */
elseif ($searchus=='registr') {
$num=0;
if (!preg_match("/^[12][019][0-9][0-9]-[01][0-9]-[0123][0-9]$/", $whatus)) $warning=$l_wrongData;
else{
$less=$whatus.' 00:00:00';
if($row=db_deadUsers(0,$less)){
$num=$countRes;
$makeLim=makeLim($page, $num, $viewmaxsearch);
$urlSet="{$main_url}/{$bb_admin}action=searchusers2&amp;whatus={$whatus}&amp;searchus=registr";
$pageNav=pageNav($page, $num, $urlSet.$addSort, $viewmaxsearch, FALSE);
unset($result);
unset($countRes);

if($row=db_deadUsers(0,$less,$sortSql)){
$Rest=$tR;
do{
$idArray.=$row[0].', ';
$rDate=convert_date($row[2]);
$lReplies=$row[5];
$numReplies=$row[6];

$Results.=ParseTpl($Rest);
}
while($row=db_deadUsers(1,$less));
}
}
$warning=$l_recordsFound.' '.parseStatsNum($num);
}
}

/* Determine all disabled users */
elseif ($searchus=='notmember'){
$makeLim='';
$num=0;
if ($row=db_simpleSelect(0, $Tu, 'count(*)', $dbUserAct, '=', '0')) $num=$row[0];
$makeLim=makeLim($page,$num,$viewmaxsearch);
$urlSet="{$main_url}/{$bb_admin}action=searchusers2&amp;whatus={$whatus}&amp;searchus=notmember";
$pageNav=pageNav($page,$num,$urlSet.$addSort,$viewmaxsearch,FALSE);

if ($row=db_simpleSelect(0, $Tu, $dbUserId.','.$dbUserSheme['username'][1].','.$dbUserDate.','.$dbUserSheme['user_password'][1].','.$dbUserSheme['user_email'][1].', '.$dbUserSheme['num_posts'][1], $dbUserAct, '=', '0', "{$dbUserId} {$sortSql}", $makeLim)){

$tot=0;
$whatus='';

do {
$Rest=$tR;
$idArray.=$row[0].', ';
$numReplies=$row[5];

if($numReplies>0){

$delCheckBox='';

$RES1=$result;
$CNT1=$countRes;

if ($lRepl=db_simpleSelect(0,$Tp,'post_time','poster_id','=',$row[0],'post_id DESC',1)) $lReplies=convert_date($lRepl[0]);

}
else {
$totch++;
$delCheckBox="<input type=\"checkbox\" name=\"delus[]\" value=\"{$row[0]}\" />&nbsp;";
}

$rDate=convert_date($row[2]);
$Results.=ParseTpl($Rest);
$tot++;

if($numReplies>0){
$result=$RES1;
$countRes=$CNT1;
}

}
while($row=db_simpleSelect(1));

}
$warning=$l_recordsFound.' '.parseStatsNum($num);
}

/* Searching by user ID */
else{
$tot=0;
$whatus=(int)$whatus+0;
if($row=db_simpleSelect(0,$Tu,$dbUserId.','.$dbUserSheme['username'][1].','.$dbUserDate.','.$dbUserSheme['user_password'][1].','.$dbUserSheme['user_email'][1].', '.$dbUserSheme['num_posts'][1],$dbUserId,'=',$whatus)){
$Results=makeUp('admin_searchusersres');
$rDate=convert_date($row[2]);
$numReplies=$row[5];
$urlSet='';

if ($numReplies>0 and $lRepl=db_simpleSelect(0,$Tp,'post_time','poster_id','=',$row[0],'post_id DESC',1)) $lReplies=convert_date($lRepl[0]); else $lReplies='&mdash;';
$tot++;
$Results=ParseTpl($Results);
}
$num=$tot;
$warning=$l_recordsFound.' '.parseStatsNum($tot);
}

if($Results!='') $Results='<ul>'.$Results.'</ul>';
if($idArray!='') $idArray="var usx=new Array(".substr($idArray,0,strlen($idArray)-2).");\n";
else $idArray='var usx=new Array();';

if($totch>0){
$Results=<<<out
<script type="text/javascript">
<!--
{$idArray}

function turnAllLayers(sw){
var el=document.searchForm.elements;
var len=el.length;
for(var i=0;i<len;i++){
if (el[i].name.substring(0,5)=='delus'){el[i].checked=sw}
}
}

function submitAnch(){
var tmpn, fin;
var el=document.searchForm.elements;
var len=el.length;
for(var i=0;i<len;i++){
if (el[i].name.substring(0,5)=='delus' && el[i].checked) {tmpn=el[i].value;break;}
}
fin=0;
for (x in usx){ if(usx[x]<tmpn) fin=usx[x]; }
document.searchForm.anchor.value=fin;
document.searchForm.submit();
return;
}

//-->
</script>
<form action="{$main_url}/{$bb_admin}" method="post" name="searchForm">
{$Results}
<input type="hidden" name="action" value="{$action}" />
<input type="hidden" name="sort" value="{$sort}" />
<input type="hidden" name="page" value="{$page}" />
<input type="hidden" name="searchus" value="{$searchus}" />
<input type="hidden" name="whatus" value="{$whatus}" />
<input type="hidden" name="anchor" value="0" />
<input type="button" value="{$l_delete}" class="inputButton" onclick="JavaScript:submitAnch();" />
<input type="button" value="+" onclick="turnAllLayers(true);" class="inputButton" />
<input type="button" value="-" onclick="turnAllLayers(false);" class="inputButton" />
</form>
out;
}

if($num<=1 or (!isset($urlSet) or $urlSet=='')) $sortOptions='';
else{
if(!isset($l_asc)) $l_asc='&uarr;';
if(!isset($l_desc)) $l_desc='&darr;';
if($sort==0) $sortOptions="{$brtag}<b>{$l_asc}</b>&nbsp;&nbsp;<a href=\"{$urlSet}&amp;sort=1\" class=\"mnblnk mnblnkn\">{$l_desc}</a>";
else $sortOptions="{$brtag}<a href=\"{$urlSet}&amp;sort=0\" class=\"mnblnk mnblnkn\">{$l_asc}</a>&nbsp;&nbsp;<b>{$l_desc}</b>";
}

$mainTpl=makeUp('admin_searchusers');
if(!isset($pageNav) or $pageNav=='') $mainTpl=preg_replace("#<!--pageNav-->(.*?)<!--/pageNav-->#is", '', $mainTpl);
$text2=ParseTpl($mainTpl);
break;

case 'viewsubs':
$topic=(isset($_GET['topic'])?(int)$_GET['topic']+0:0);
$text2='';
if($tt=db_simpleSelect(0,$Tt,'topic_title','topic_id','=',$topic)){
$topicTitle=$tt[0];
$listSubs='';

if ($row=db_simpleSelect(0,"$Ts,$Tu","$Ts.id,$Ts.user_id,$Tu.{$dbUserSheme['username'][1]},$Tu.{$dbUserSheme['user_email'][1]},$Ts.active",'topic_id','=',$topic,'','',"$Ts.user_id",'=',"$Tu.$dbUserId")){
$listSubs="<form action=\"{$main_url}/{$bb_admin}\" method=\"post\" class=\"formStyle\"><input type=\"hidden\" name=\"action\" value=\"viewsubs2\" />
<input type=\"hidden\" name=\"topic\" value=\"$topic\" />";
do {
if($row[4]==0) $s='<span class="warning"><b>-</b></span>'; else $s='+';
$listSubs.="{$brtag}<input type=\"checkbox\" name=\"selsub[]\" value=\"{$row[0]}\" />&nbsp;<span class=\"txtNr\"><a href=\"{$main_url}/{$indexphp}action=userinfo&amp;user={$row[1]}\" class=\"mnblnk\">{$row[2]}</a> (<a href=\"mailto:{$row[3]}\" class=\"mnblnk\">{$row[3]}</a>) [{$s}]</span>\n";
}
while ($row=db_simpleSelect(1));
$listSubs.="{$brtag}{$brtag}&nbsp;<input type=\"submit\" value=\"$l_deletePost\" class=\"inputButton\" /></form>\n";
}

$tpltx=makeUp('admin_viewsubs');
if(isset($is_mobile) and $is_mobile) $tpltx=preg_replace('#<!--desktop-->(.+?)<!--/desktop-->#is', '', $tpltx);
$text2=ParseTpl($tpltx);

}
break;

case 'viewsubs2':
$fs=0;
if(isset($_POST['selsub']) and sizeof($_POST['selsub'])>0){
$selsubs=array();
foreach($_POST['selsub'] as $sb) {
$ssb=(int)$sb;
if($ssb>0) $selsubs[]=(int)$sb;
}
$xtr=getClForums($selsubs,'','','id','or','=');
$fs=db_delete($Ts,$xtr);
}
$errorMSG=$l_subscriptions.': '.$l_del.' '.$fs.' '.$l_rows;
$bTopic=(int)$_POST['topic'];
$correctErr="<a href=\"{$indexphp}action=vthread&amp;topic={$bTopic}#newreply\" class=\"mnblnk\">$l_back</a>";
$text2=ParseTpl(makeUp('main_warning'));
break;

default:
$warning='';
$text2=ParseTpl(makeUp('admin_panel'));
} // end of switch
}
else {
if (!$warning) $warning=$l_enter_admin_login;
$text2=ParseTpl(makeUp('admin_login'));
}

} // end of switch

echo load_header();
echo $text2;
include ($pathToFiles.'bb_plugins2.php');

display_footer();
?>