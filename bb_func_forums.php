<?php
/*
This file is part of miniBB. miniBB is free discussion forums/message board software, provided with no warranties.
Check COPYING file for more details.
Copyright (C) 2017 Paul Puzyrev. www.minibb.com
Latest File Update: 2017-Dec-08
*/
if (!defined('INCLUDED776')) die ('Fatal error.');

if(!isset($where)) { if($topic==0) $where=1; else $where=0; }
if($action=='vthread' and isset($topicData[5]) and $topicData[5]<=$viewmaxreplys) $searchBox='';
elseif($action!='search') $searchBox=ParseTpl(makeUp('search_box'));

$listForums='';

if($row=db_simpleSelect(0,$Tf,'forum_id, forum_name, 
forum_group, forum_icon, forum_desc, topics_count','','','','forum_order')){

//$st: 1 - dont show included forum, 0 - show all (select included)
$forumsList='';
$forumsArray=array();
//$keyAr=0;

$i=0;
$optGroups=array();
$currOg=0;

$tpl=makeUp('main_forums_list');

if(isset($is_mobile) and $is_mobile) {
$tpl=preg_replace("#<!--searchBox-->(.+?)<!--/searchBox-->#is", '', $tpl);
}

if($action=='vforum' or $action=='vtopic') $onforumchange=' onchange="javascript:this.form.submit();"';

do {

$forumsArray[$row[0]]=array($row[1], $row[3], $row[4], $row[5]);

if ($row[2]!='') { $currOg++; $optGroups[$currOg][0]=$row[2]; }

if($user_id!=1 and isset($clForums) and in_array($row[0],$clForums) and isset($clForumsUsers[$row[0]]) and !in_array($user_id,$clForumsUsers[$row[0]])) $show=FALSE; else $show=TRUE;

if($show){

$sel='';

$forumItem='';

if (isset($st) and $st==1) {
if($row[0]!=$frm) $forumItem="<option value=\"{$row[0]}\">{$row[1]}</option>\n";
}
else {
if ($row[0]==$frm) $sel='selected="selected" ';
$forumItem="<option {$sel}value=\"{$row[0]}\">{$row[1]}</option>\n";
}

if($forumItem!='') $optGroups[$currOg][$row[0]]=$forumItem;

$i++;
}

}
while($row=db_simpleSelect(1));
unset($result);unset($countRes);

for($a=0;$a<=$currOg;$a++){
if(isset($optGroups[$a]) and sizeof($optGroups[$a])>=1){
$fins=''; foreach($optGroups[$a] as $k=>$v) if($k!=0) $fins.=$v;
if(isset($optGroups[$a][0]) and $fins!='') $listForums.="<optgroup label=\"".strip_tags($optGroups[$a][0])."\">{$fins}</optgroup>";
else $listForums.=$fins;
}
}

if ($i>1) $forumsList=ParseTpl($tpl);
}

?>