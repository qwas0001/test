<?php
/*
This file is part of miniBB. miniBB is free discussion forums/message board software, supplied with no warranties.
Check COPYING file for more details.
Copyright (C) 2017-2018 Paul Puzyrev. www.minibb.com
Latest File Update: 2018-Oct-13
*/
if (!defined('INCLUDED776')) die ('Fatal error.');
@set_time_limit(0);

$title.=$l_removeUser;

if(isset($_GET['step'])) $step=$_GET['step']; elseif(isset($_POST['step'])) $step=$_POST['step']; else $step='';
if(isset($_GET['user'])) $user=(int)$_GET['user']; elseif(isset($_POST['user'])) $user=(int)$_POST['user']; else $user=0;

if($user>1 and $row=db_simpleSelect(0, $Tu, $dbUserSheme['username'][1], $dbUserId, '=', $user)) {
$uname=$row[0];
$unameTitle='&ldquo;'.$row[0].'&rdquo;';
$title.=' &quot;'.$uname.'&quot;'; 
}
else {
$uname='';
}

//print_r($_POST);

$allowedToDelete=TRUE;
if(isset($excludeDeleteUsers) and in_array($user_id, $excludeDeleteUsers)) $allowedToDelete=FALSE;

if($step=='remove' and $uname=='') $allowedToDelete=FALSE; //that means user's profile was not found...

//admin is allowed to delete all profiles
//moderators can delete all profiles, except for admin's and other moderators
if($user==1 or ($user_id!=1 and checkModerator($mods, $user))) $allowedToDelete=FALSE;

if($step=='' and $user==0) $user='';

if(($logged_admin==1 or $isMod==1) and $allowedToDelete){

if($step==''){
if($user>1) {
$editable_id='disabled="disabled"';
$user_field="<input type=\"hidden\" name=\"user\" value=\"{$user}\" />";
}
$tpl=makeUp('admin_removeuser1');
}
elseif($step=='remove'){
if($csrfchk=='' or $csrfchk!=$_COOKIE[$cookiename.'_csrfchk']) die('Can not proceed: possible CSRF/XSRF attack!');

if(isset($_POST['keepblocked']) and (int)$_POST['keepblocked']==1){
${$dbUserAct}=0;
$updArray=array($dbUserAct);
if(isset($_POST['removemessages'])) {
$updArray[]=$dbUserSheme['num_topics'][1]; ${$dbUserSheme['num_topics'][1]}=0;
$updArray[]=$dbUserSheme['num_posts'][1]; ${$dbUserSheme['num_posts'][1]}=0;
if(isset($archives) and sizeof($archives)>0){
$updArray[]=$dbUserSheme['num_live_topics'][1]; ${$dbUserSheme['num_live_topics'][1]}=0;
$updArray[]=$dbUserSheme['num_live_posts'][1]; ${$dbUserSheme['num_live_posts'][1]}=0;
}
}
updateArray($updArray, $Tu, $dbUserId, $user);
$warning=$l_userDeactivated;
}
else{
if(db_delete($Tu, $dbUserId, '=', $user)) $warning=$l_userDeleted." (".$uname.")"; else $warning=$l_userNotDeleted." (".$uname.")";
}

/*Delete from sendMails*/
db_delete($Ts,'user_id','=',$user);

if(isset($archives) and sizeof($archives)>0) $tables=$archives;
else $tables=array();
$tables['0']='';

if(isset($_POST['removemessages'])) {
//set_time_limit(0);

$aff=0;

foreach($tables as $tn=>$val){

if($tn=='0') {
$topicsTb=$Tt;
$postsTb=$Tp;
$forumsTb=$Tf;
}
else{
$tnd=str_replace('-', '_', $tn);
$topicsTb=$tnd.'_'.$Tt;
$postsTb=$tnd.'_'.$Tp;
$forumsTb=$tnd.'_'.$Tf;
}

/* Deleting user messages from posts and topics table. Topics - delete also all associated posts. Collect info about updated msgs amount in forums/topics. */
$updForums=array();
$updTopics=array();

if($row=db_simpleSelect(0, $topicsTb, 'forum_id, topic_id, topic_last_poster', 'topic_poster', '=', $user)){
do {
if(!isset($updForums[$row[0]])) $updForums[$row[0]]=TRUE;
}
while($row=db_simpleSelect(1));
}

$aff+=db_delete($topicsTb, 'topic_poster', '=', $user);

if($row=db_simpleSelect(0, $postsTb, 'forum_id, topic_id', 'poster_id', '=', $user)){
do {
if(!isset($updForums[$row[0]])) $updForums[$row[0]]=TRUE;
if(!isset($updTopics[$row[1]])) $updTopics[$row[1]]=TRUE;
}
while($row=db_simpleSelect(1));
}

/* Posts only */
foreach($updTopics as $tId=>$bool){

$topic_id=$tId;
$aff+=db_delete($postsTb,'topic_id','=',$topic_id,'poster_id','=',$user);
db_calcAmount($postsTb,'topic_id',$topic_id,$topicsTb,'posts_count');
$RES1=$result;
$CNT1=$countRes;

if($ld=db_simpleSelect(0, $topicsTb, 'topic_last_poster', 'topic_id','=',$topic_id) and $ld[0]==$uname and $lp=db_simpleSelect(0,$postsTb,'post_id, post_time, poster_name','topic_id','=',$topic_id,'post_id DESC',1)){
$topic_last_post_id=$lp[0];
$topic_last_post_time=$lp[1];
$topic_last_poster=$lp[2];
$fs=updateArray(array('topic_last_post_id', 'topic_last_post_time', 'topic_last_poster'),$topicsTb,'topic_id',$topic_id);
$aff+=$fs;
}

$result=$RES1;
$countRes=$CNT1;

}

/* Update forums posts, topics amount */
foreach($updForums as $fId=>$bool){
db_calcAmount($postsTb,'forum_id',$fId,$forumsTb,'posts_count');
db_calcAmount($topicsTb,'forum_id',$fId,$forumsTb,'topics_count');
}

}

if ($aff>0) $warning.="{$brtag}".$l_userMsgsDeleted; else $warning.="{$brtag}".$l_userMsgsNotDeleted;
}
else {
/* Make user posts as Guest in Live/Archived forums */

foreach($tables as $tn=>$val){

if($tn=='0') {
$topicsTb=$Tt;
$postsTb=$Tp;
}
else{
$tnd=str_replace('-', '_', $tn);
$topicsTb=$tnd.'_'.$Tt;
$postsTb=$tnd.'_'.$Tp;
}

$aff=0;
$poster_id=0; $topic_poster=0;
$updPosts=array('poster_id');
$updTopics=array('topic_poster');

if(isset($_POST['guestname']) and $_POST['guestname']=='guest') {
$updPosts[]='poster_name';
$updTopics[]='topic_poster_name';
$topic_poster_name=$l_anonymous;
$poster_name=$l_anonymous;
$topic_last_poster=$l_anonymous;
$aff+=updateArray(array('topic_last_poster'), $topicsTb, 'topic_last_poster', $uname);
}

$aff+=updateArray($updPosts, $postsTb, 'poster_id', $user);
$aff+=updateArray($updTopics, $topicsTb, 'topic_poster', $user);
}

if ($aff>0) $warning.="{$brtag}".$l_userUpdated0; else $warning.="{$brtag}".$l_userNotUpdated0;

/* Delete from archive_userstats */
db_delete($Taus, 'user_id', '=', $user);

}

$errorMSG=$warning;
$correctErr='';
$tpl=makeUp('main_warning');

}

}

else{
$errorMSG=$l_forbidden; $correctErr=$backErrorLink;
$loginError=1;
$tpl=makeUp('main_warning');
}

echo load_header();
if(isset($is_mobile) and $is_mobile) $tpl=preg_replace('#<!--desktop-->(.+?)<!--/desktop-->#is', '', $tpl);
echo ParseTpl($tpl);
return;

?>