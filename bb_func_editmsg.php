<?php
/*
This file is a part of miniBB. miniBB is a free discussion forums/message board software, supplied with no warranties.
Check COPYING file for more details.
Copyright (C) 2018 Paul Puzyrev. www.minibb.com
Latest File Update: 2018-Nov-13
*/
if (!defined('INCLUDED776')) die ('Fatal error.');

if(isset($_GET['post'])) $post=(int)$_GET['post']+0; elseif(isset($_POST['post'])) $post=(int)$_POST['post']+0; else $post=0;
if(isset($_GET['anchor'])) $anchor=(int)$_GET['anchor']+0; elseif(isset($_POST['anchor'])) $anchor=(int)$_POST['anchor']+0; else $anchor=0;

/*
if ((isset($_COOKIE[$cookiename.'Update']) or (isset($_SESSION[$cookiename.'Update']) and $_SESSION[$cookiename.'Update']>time())) and !($user_id==1 or $isMod==1)) {
$errorMSG=$l_antiSpam; $correctErr=$backErrorLink;
$title.=$l_antiSpam;
echo load_header(); echo ParseTpl(makeUp('main_warning')); return;
}
*/

/* Check for: topic&post exist, user time or admin, user allowed or admin */
if($userAllow=db_simpleSelect(0,$Tp,'poster_id,post_status,topic_id,forum_id','post_id','=',$post)){
$whoEdited=(integer) $userAllow[1];
$topic=$userAllow[2];
$forum=$userAllow[3];
$userAllow=$userAllow[0];
}

if(defined('PREMOD_EDITMSG')){
$nextAction="<input type=\"hidden\" name=\"action\" value=\"premodpanel\" /><input type=\"hidden\" name=\"stepmod\" value=\"editmsg2\" />";
}
elseif(defined('PREMOD_EDITTPC')){
$nextAction="<input type=\"hidden\" name=\"action\" value=\"premodpanel\" /><input type=\"hidden\" name=\"stepmod\" value=\"edittpc2\" />";
}
else{
$nextAction="<input type=\"hidden\" name=\"action\" value=\"editmsg2\" />";
}

$tname="$Tp,$Tt";
$tsel="$Tp.post_text, $Tt.topic_title, $Tp.post_time, $Tt.topic_status, $Tt.forum_id";
$tcond1="$Tp.post_id";
$teq1='=';
$tval1=$post;
$tcond2="$Tp.topic_id";
$teq2='=';
$tval2="$Tt.topic_id";

if ($user_id!=0 and $row=db_simpleSelect(0, $tname, $tsel, $tcond1, $teq1, $tval1, '', '', $tcond2, $teq2, $tval2) and $row[4]==$forum and (!isset($useredit) or $useredit==0 or $useredit>(strtotime('now')-strtotime($row[2])) or $user_id==1 or $isMod==1) and ($userAllow==$user_id or $user_id==1 or $isMod==1) ) {

if ($step!=1 and $step!=0) $step=0;

if($row[3]==1 and !($user_id==1 or $isMod==1)) $whoEdited=2;

if (($whoEdited==2 or $whoEdited==3) and !($logged_admin==1 or $isMod==1)) {
$errorMSG=$l_onlyAdminCanEdit; $correctErr=$backErrorLink;
$title.=$l_onlyAdminCanEdit;
echo load_header(); echo ParseTpl(makeUp('main_warning')); return;
}
else {

/*First post?*/
if(defined('PREMOD_EDITMSG')){
$first=FALSE;
}
elseif(defined('PREMOD_EDITTPC')){
$first=TRUE;
}
else{
if($frt=db_simpleSelect(0,$Tp,'post_id','topic_id','=',$topic,'post_id',1) and $frt[0]==$post and (($logged_admin==1 or $isMod==1) OR (isset($usersEditTopicTitle) and $usersEditTopicTitle))) $first=TRUE; else $first=FALSE;
}

if($step==1) {
$errorMSG='';

if(!isset($_POST['disbbcode']) or (isset($_POST['disbbcode']) and $_POST['disbbcode']=='') ) $disbbcode=FALSE; else $disbbcode=TRUE;
$post_text=textFilter($_POST['postText'],$post_text_maxlength,$post_word_maxlength,1,$disbbcode,1,$logged_admin);
$compareTL=strlen_unicode(trim(strip_tags($post_text)));
$sce=FALSE; if(isset($simpleCodes)) foreach($simpleCodes as $e) { if(substr_count($post_text, $e)>0) { $sce=TRUE; break; } }

if( ( ($compareTL==0 or ($compareTL>0 and $compareTL<$post_text_minlength)) and !$sce) or (isset($_POST['topicTitle']) and strlen(trim($_POST['topicTitle']))==0)) {
$title.=$l_emptyPost; $errorMSG=$l_emptyPost; $correctErr=$backErrorLink;
}
else {

if(($user_id==1 or $isMod==1) and (isset($_POST['fEdit']) and strlen($_POST['fEdit'])>0)) $fEdit=1; else $fEdit=0;

//Update topic title if admin is logged, if it is first post
if ($first) {
$topicTitle=(isset($_POST['topicTitle'])?$_POST['topicTitle']:'');
$topic_title=textFilter($topicTitle,$topic_max_length,$post_word_maxlength,0,1,0,$logged_admin,255);
$fif=updateArray(array('topic_title'),$Tt,'topic_id',$topic);
if($fif!=0) $errorMSG.=$l_topicTitleUpdated.$brtag;
}

if(($user_id==1 or $isMod==1) and $fEdit==1){
if ($logged_admin==1 and $userAllow!=1) $post_status=2;
elseif ($isMod==1 and $userAllow!=$user_id) $post_status=3;
else $post_status=1;
}
elseif(($user_id==1 or $isMod==1) and $fEdit==0) {
if (($logged_admin==1 and $userAllow==1) OR ($isMod==1 and $userAllow==$user_id)) $post_status=1;
else {
$post_status=$whoEdited;
if($post_status==2 or $post_status==3) $post_status=0;
}
}
else $post_status=1;

$fif=updateArray(array('post_text','post_status'),$Tp,'post_id',$post);

if ($fif!=0) $errorMSG.=$l_topicTextUpdated;

$title.=$l_editPost;

if(defined('PREMOD_EDITMSG') or defined('PREMOD_EDITTPC')){
if(defined('PREMOD_EDITMSG')) $stepmod='posts'; else $stepmod='topics';
$furl=addGenURLPage("{$main_url}/{$indexphp}action=premodpanel&amp;stepmod={$stepmod}", $page, '&')."#msg{$post}";
$topicsLink='';
}

else{

if(isset($mod_rewrite) and $mod_rewrite) {
if(isset($topic_title)) $tt=$topic_title; else $tt='#GET#';
$furl=$furlCl=addTopicURLPage(genTopicURL($main_url, $forum, '#GET#', $topic, $tt), $page)."#msg{$anchor}";
$topicsLink="<a href=\"".addForumURLPage(genForumURL($main_url, $forum, '#GET#'), PAGE1_OFFSET+1)."\" class=\"mnblnk\">{$l_returntotopics}</a>{$brtag}";
} else {
$furl=addGenURLPage("{$main_url}/{$indexphp}action=vthread&amp;forum=$forum&amp;topic=$topic", $page)."#msg{$anchor}";
$furlCl=addGenURLPage("{$main_url}/{$indexphp}action=vthread&forum=$forum&topic=$topic", $page, '&')."#msg{$anchor}";
$topicsLink="<a href=\"".addGenURLPage("{$main_url}/{$indexphp}action=vtopic&amp;forum={$forum}", PAGE1_OFFSET+1)."\" class=\"mnblnk\">{$l_returntotopics}</a>{$brtag}";
}

}

$correctErr="<a href=\"{$furl}\" class=\"mnblnk\">$l_back</a>";
}

if(file_exists($pathToFiles.'bb_plugins2.php')) require_once($pathToFiles.'bb_plugins2.php');

if(isset($editMsgReloc) and $editMsgReloc) {
header("{$rheader}{$furlCl}"); exit;
}
else {
echo load_header(); echo ParseTpl(makeUp('main_warning')); return;
}

}

else{
//default edit
require($pathToFiles.'bb_codes.php');

$postText=deCodeBB($row[0]);
$topicTitle=$row[1];

$l_messageABC=$l_editPost;

if ($first) {
$mainPostForm=ParseTpl(makeUp('tools_edit_topic_title'));
} else $mainPostForm='';

if($user_id==1 or $isMod==1) {
if($whoEdited==2 or $whoEdited==3) $ch='checked'; else $ch='';
if(isset($l_editLock)) $ledt=$l_editLock; else $ledt=' <s>'.$l_edit.'</s>';
$emailCheckBox='<input type="checkbox" name="fEdit"'.$ch.' /> '.$ledt;
} else $emailCheckBox='';

//dynamic BB buttons
$mpf=ParseTpl(makeUp('main_post_form'));
$mpfs=convertBBJS($mpf);
if($mpfs!='') $mainPostForm.=$mpfs; else $mainPostForm.=$mpf;

//$mainPostForm.=ParseTpl(makeUp('main_post_form'));
$title.=$l_editPost;
echo load_header(); echo ParseTpl(makeUp('tools_edit_post'));
}

}

}
else {
$errorMSG=$l_accessDenied; $correctErr=$backErrorLink;
$title.=$l_accessDenied;
echo load_header(); echo ParseTpl(makeUp('main_warning')); return;
}

?>