<?php
/*
This file is part of miniBB. miniBB is free discussion forums/message board software, provided with no warranties.
See COPYING file for more details.
Copyright (C) 2018 Paul Puzyrev. www.minibb.com
Latest File Update: 2018-Apr-23
*/
if (!defined('INCLUDED776')) die ('Fatal error.');

if (!isset($user_sort) or $user_sort=='') $user_sort=$sortingTopics; // Sort messages default by last answer (0) desc OR 1 - by last new topics

if(isset($lastOut) and is_array($lastOut)){
foreach($lastOut as $l){
if(!in_array($l,$clForums)) $clForums[]=$l;
$clForumsUsers[$l]=array();
}
}

if(isset($clForumsUsers)) $closedForums=getAccess($clForums, $clForumsUsers, $user_id); else $closedForums='n';
if($closedForums!='n') $xtr=getClForums($closedForums,'where','','forum_id','and','!='); else $xtr='';

$lPosts=array();
if ($user_sort==1) $orderBy='topic_id DESC'; else $orderBy='topic_last_post_id DESC';

$colls=array();

$makeLim=makeLim($page,$totalForumsTopics,$viewlastdiscussions);

$nr=db_simpleSelect(0, $Tt, 'count(*)');

if($cols=db_simpleSelect(0, $Tt, 'topic_id, topic_title, topic_poster, topic_poster_name, topic_time, forum_id, posts_count, topic_last_post_id, topic_views, topic_last_post_time, topic_last_poster','','','',$orderBy,$makeLim)){

do {
if(!isset($textLd)) $lPosts[]=$cols[7];
else { if($user_sort==0) $lPosts[]=$cols[7]; else $lPosts[]=$cols[0]; }
$colls[]=array($cols[0], $cols[1], $cols[2], $cols[3], $cols[4], $cols[5], $cols[6], $cols[7], $cols[8], $cols[9], $cols[10]);
}
while($cols=db_simpleSelect(1));
}

if(isset($textLd)) {

if(sizeof($lPosts)>0) {
if($user_sort==0) { $ordb='post_id'; $ordSql='DESC'; $linkToForums="{$main_url}/{$startIndex}"; } else { $ordb='topic_id'; $ordSql='ASC'; $linkToForums="{$main_url}/{$indexphp}sortBy=1"; }
$xtr=getClForums($lPosts,'where','',$ordb,'or','=');
}
else $xtr='';

if($xtr!=''){
if($row=db_simpleSelect(0, $Tp, 'poster_id, poster_name, post_time, topic_id, post_text, post_id', '', '', '', 'post_id '.$ordSql))
do 
if(!isset($pVals[$row[3]])) $pVals[$row[3]]=array($row[0],$row[1],$row[2],$row[4],$row[5]); else continue;
while($row=db_simpleSelect(1));
}

}

if(!isset($disableSuperSticky)) require($pathToFiles.'bb_func_supersticky.php');
else $specialThreadsArr=array();

$list_topics='';

unset($result);

$i=1;
if($page==PAGE1_OFFSET+1){
if(!isset($startPageModern) or !$startPageModern) $tpl=makeUp('main_last_discuss_cell'); else $tpl=makeUp('main_modern_lcell');
}
else {
$tpl=makeUp('main_topics_cell');
}

if($sortBy==1) $linkToForums="{$main_url}/{$indexphp}sortBy=1"; else $linkToForums="{$main_url}/{$startIndex}";

foreach($colls as $cols){

$topic=$cols[0];

if(!in_array($topic, $specialThreadsArr)){

$forum=$cols[5];
$numReplies=$cols[6]; if($numReplies>=1) $numReplies-=1;
$topic_views=$cols[8];
$lm=$cols[7];
$topic_reverse='';
if(isset($themeDesc) and in_array($topic,$themeDesc)) $topic_reverse="<img src=\"{$main_url}/img/topic_reverse.gif\" class=\"authorIcon\" alt=\"\" />&nbsp;";

$topic_title=$cols[1];
if($topic_title=='') $topic_title=$l_emptyTopic;
$topicTitle=$topic_title;

if(isset($pVals[$topic][0])) $lastPosterID=$pVals[$topic][0]; else $lastPosterID='N/A';

if(!isset($lastPosterIcon)) $lastPosterIcon="<img src=\"{$main_url}/img/s.png\" class=\"authorIcon\" alt=\"{$l_lastAuthor}\" title=\"{$l_lastAuthor}\" />&nbsp;";

if($numReplies>0 and isset($cols[10]) and $cols[10]!='') $lastPoster=$lastPosterIcon.$cols[10];
elseif($numReplies>0 and isset($pVals[$topic][1])) $lastPoster=$lastPosterIcon.$pVals[$topic][1];
else $lastPoster='&mdash;';

if($numReplies>0 and isset($cols[9])) $lastPostDate=convert_date($cols[9]);
elseif($numReplies>0 and isset($pVals[$topic][2])) $lastPostDate=convert_date($pVals[$topic][2]);
else $lastPostDate='';

if(isset($textLd) and isset($pVals[$topic][3])) {
$lptxt=($textLd==1?$pVals[$topic][3]:strip_tags(str_replace(array('<br />', '<br>'), ' ', $pVals[$topic][3])));
$lastPostText=$lptxt;
}
else $lastPostText='N/A';

if($cols[3]=='') $cols[3]=$l_anonymous;
$topicAuthor=$cols[3];

if($i>0) $bg='tbCel1'; else $bg='tbCel2';

//Link to latest post in topic
if(isset($themeDesc) and in_array($topic, $themeDesc)) $vvpn=TRUE; else $vvpn=FALSE;
if($numReplies<$viewmaxreplys or $vvpn) $pagelm=PAGE1_OFFSET+1; else $pagelm=ceil(($numReplies+1)/$viewmaxreplys)+PAGE1_OFFSET;

if(isset($mod_rewrite) and $mod_rewrite) {
$urlp=genTopicURL($main_url, $forum, $fTitle[$forum], $topic, $topic_title);
$urlForum=addForumURLPage(genForumURL($main_url, $forum, $fTitle[$forum]), PAGE1_OFFSET+1);
$urlType='Topic';
$lmurl1=addTopicURLPage($urlp, $pagelm)."#msg{$lm}";
}
else{
$urlp="{$main_url}/{$indexphp}action=vthread&amp;forum=$forum&amp;topic=$topic";
$urlForum="{$main_url}/{$indexphp}action=vtopic&amp;forum=$forum";
$urlType='Gen';
$lmurl1="{$main_url}/{$indexphp}action=vthread&amp;forum={$forum}&amp;topic={$topic}&amp;page={$pagelm}#msg{$lm}";
}

$pageNavCell='';
if($numReplies>0) {

if($numReplies>$viewmaxreplys){
if(!(isset($is_mobile) and $is_mobile)){
$pageNavCell=pageNav(PAGE1_OFFSET+1,$numReplies+1,$urlp,$viewmaxreplys,TRUE,$urlType);
}
else{
if(!isset($l_mobilePages)) $l_mobilePages='Pages';
$totalPages=ceil($numReplies/$viewmaxreplys);
$pageNavCell='&nbsp;&nbsp;<span class="txtSm pages noWrap"><img src="'.$main_url.'/img/page.gif" alt="'.$l_mobilePages.'" title="'.$l_mobilePages.'" />&nbsp;<b>'.$totalPages.'</b></span>';
}
}

$LinkToLastPostInTopic='<a href="'.$lmurl1.'" class="mnblnk">'.$lastPostIcon.'</a>';
}
else {
$LinkToLastPostInTopic='';
}

$whenPosted=convert_date($cols[4]);
if(trim($cols[1])=='') $cols[1]=$l_emptyTopic;

//Forum icon
if(isset($fIcon[$forum])) $forumIcon=$fIcon[$forum]; else $forumIcon='default.gif';
$topicIcon="<img src=\"{$main_url}/img/forum_icons/{$forumIcon}\" class=\"forumIcon\" alt=\"{$topic_title}\" title=\"{$topic_title}\" />";

if(isset($mod_rewrite) and $mod_rewrite) {
$linkToTopic=addTopicURLPage(genTopicURL($main_url, $forum, $fTitle[$forum], $topic, $topic_title), PAGE1_OFFSET+1);
}
else $linkToTopic="{$main_url}/{$indexphp}action=vthread&amp;forum={$forum}&amp;topic={$topic}";

$numReplies=parseStatsNum($numReplies);
$topic_views=parseStatsNum($topic_views);

if(function_exists('parseTopic')) parseTopic();

$list_topics.=ParseTpl($tpl);

$i=-$i;
}
}

/* Handling not-available pages in Extra navigation */
$pageChk=pageChk($page,$totalForumsTopics,$viewlastdiscussions);
if($page>$pageChk or (isset($_GET['page']) and $_GET['page']<2)){
header($proto.' 404 Not Found');
header('Status: 404 Not Found');
$metaRobots='NOINDEX';
$errorMSG=$l_accessDenied; $correctErr='';
$title=$l_forbidden;
echo load_header();
echo ParseTpl(makeUp('main_warning'));
display_footer();
exit;
}

/* Extra pages navigation */
$pageNavExtra='';

if(!isset($disableNavDisplay) and $totalForumsTopics>0){

if($sortBy==0){
if(isset($mod_rewrite) and $mod_rewrite) {
$urlp=$main_url;
$urlType='Extranavmr';
}
else {
$urlp="{$main_url}/{$indexphp}";
$urlType='Extranav';
}
}
else{
$urlp="{$main_url}/{$indexphp}sortBy=1";
$urlType='Gen';
}

$pageNavExtra=pageNav($page,$totalForumsTopics,$urlp,$viewlastdiscussions,FALSE,$urlType);
}
if($pageNavExtra!='') $mbpn=$brtag;
$topic=0; $forum=0;
?>