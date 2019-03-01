<?php
/*
This file is part of miniBB. miniBB is free discussion forums/message board software, supplied with no warranties.
Check COPYING file for more details.
Copyright (C) 2014-2018 Paul Puzyrev. www.minibb.com
Latest File Update: 2018-Nov-01
*/
$mysqlink = mysqli_connect($DBhost, $DBusr, $DBpwd) or die ('<b>Database/configuration error.</b>');
mysqli_select_db($mysqlink, $DBname) or die ('<b>Database/configuration error (DB is missing).</b>');
if(isset($mysql_set_charset)) mysqli_set_charset($mysqlink, $mysql_set_charset);
if(isset($mysql_set_names)) {
if(isset($mysql_set_names[1])) mysqli_query($mysqlink, "set names {$mysql_set_names[0]} collate {$mysql_set_names[1]}");
else mysqli_query($mysqlink, "set names {$mysql_set_names[0]}");
}

function makeLim($page,$numRows,$viewMax){
$page=pageChk($page,$numRows,$viewMax);
if(intval($numRows/$viewMax)!=0&&$numRows>0){
if ($page>0) return (($page-(PAGE1_OFFSET+1))*$viewMax).','.$viewMax;
else return $viewMax;
}
else return '';
}

function getClForums($closedForums,$more,$prefix,$field,$syntax,$condition){
$xtr=$more.' (';
if($prefix!='') $prefix=$prefix.'.';
$siz=sizeof($closedForums);
foreach($closedForums as $c) {
$xtr.=' '.$prefix.$field.$condition.$c;
$siz--;
if ($siz!=0) $xtr.=' '.$syntax;
}
return $xtr.') ';
}

function db_simpleSelect($sus,$table='',$fields='',$uniF='',$uniC='',$uniV='',$orderby='',$limit='',$uniF2='',$uniC2='',$uniV2='',$and2=true,$groupBy=''){
if(!$sus){
$where='';
if($uniF!='') $where=' WHERE '.$uniF.$uniC."'".$uniV."'";
if($uniF2!='') {
$q=(substr_count($uniV2,'.')>0?'':"'");
$a=($and2?'AND':'WHERE');
$where.=' '.$a.' '.$uniF2.$uniC2.$q.$uniV2.$q;
}
if($limit!='') $limit='limit '.$limit;
if($orderby!='') $orderby='order by '.$orderby;
if($groupBy!='') $groupBy='group by '.$groupBy;
$xtr=(!isset($GLOBALS['xtr'])?'':$GLOBALS['xtr']);
$sql='SELECT '.$fields.' FROM '.$table.$where.' '.$xtr.' '.$groupBy.' '.$orderby.' '.$limit;
//if($sus==0 and function_exists('parseSql')) $sql=parseSql($sql);
//echo "!-- ".$sql." --><br />";
$result=mysqli_query($GLOBALS['mysqlink'], $sql);
if($result) {
$GLOBALS['countRes']=mysqli_num_rows($result);
$GLOBALS['result']=$result;
}
}
if(($sus==1||(isset($result)&&$result))&&isset($GLOBALS['countRes'])&&$GLOBALS['countRes']>0)  return mysqli_fetch_row($GLOBALS['result']);
else return FALSE;
}

function insertArray($insertArray,$tabh){
$into=''; $values='';
foreach($insertArray as $ia) {
$iia=$GLOBALS[$ia];
$into.=$ia.',';
$values.=($iia=='now()'?$iia.',':"'".$iia."',");
}
$into=substr($into,0,strlen($into)-1);
$values=substr($values,0,strlen($values)-1);
//echo 'insert into '.$tabh.' ('.$into.') values ('.$values.')';
$res=mysqli_query($GLOBALS['mysqlink'], 'insert into '.$tabh.' ('.$into.') values ('.$values.')') or die('<p>'.'insert into '.$tabh.' ('.$into.') values ('.$values.')'.$GLOBALS['brtag'].mysqli_error($GLOBALS['mysqlink']).'. Try another name or value.');
$GLOBALS['insres']=mysqli_insert_id($GLOBALS['mysqlink']);
return mysqli_errno($GLOBALS['mysqlink']);
}

function updateArray($updateArray,$tabh,$uniq,$uniqVal){
$into='';
foreach($updateArray as $ia) {
$iia=$GLOBALS[$ia];
if($iia=='now()' or $iia=='NULL') $into.=$ia.'='.$iia.', ';
else $into.="{$ia}='{$iia}', ";
}
$into=substr($into,0,strlen($into)-2);
$unupdate=($uniq!=''?' where '.$uniq.'='."'".$uniqVal."'":'');

$res=mysqli_query($GLOBALS['mysqlink'], 'update '.$tabh.' set '.$into.' '.$unupdate) or die('update '.$tabh.' set '.$into.' '.$unupdate.$GLOBALS['brtag'].'<p>'.mysqli_error($GLOBALS['mysqlink']).'. Please, try another name or value.');
return mysqli_affected_rows($GLOBALS['mysqlink']);
}

function db_delete($table,$uniF='',$uniC='',$uniV='',$uniF2='',$uniC2='',$uniV2=''){
$where=($uniF!=''?'where '.$uniF.$uniC.$uniV:'');
if($uniF2!='') {
$where.=' AND '.$uniF2.$uniC2.$uniV2;
}
$sql='DELETE FROM '.$table.' '.$where;
$result=mysqli_query($GLOBALS['mysqlink'], $sql);
if($result) return mysqli_affected_rows($GLOBALS['mysqlink']);
else return FALSE;
}

function db_ipCheck($thisIp,$thisIpMask,$user_id){
$res=mysqli_query($GLOBALS['mysqlink'], "select id, banreason from {$GLOBALS['Tb']} where banip='{$thisIp}' or banip='{$thisIpMask[0]}' or banip='{$thisIpMask[1]}' or banip='{$thisIpMask[2]}' or banip='{$user_id}'");
if($res and mysqli_num_rows($res)>0 and $row=mysqli_fetch_row($res)) return ((strlen($row[1])==0)?TRUE:$row[1]); else return FALSE;
}


function db_inactiveUsers($sus,$what='',$sortSql='asc'){
/*Admin - users users that posted nothing in the live table or archived tables - different requests */
if(!$sus) {
if(strlen($GLOBALS['makeLim'])>0) $GLOBALS['makeLim']='LIMIT '.$GLOBALS['makeLim'];
if(!isset($GLOBALS['archives'])) $result=mysqli_query($GLOBALS['mysqlink'], 'select '.$what.' from '.$GLOBALS['Tu'].' LEFT JOIN '.$GLOBALS['Tp'].' ON '.$GLOBALS['Tu'].'.'.$GLOBALS['dbUserId'].'='.$GLOBALS['Tp'].'.poster_id where '.$GLOBALS['Tp'].'.poster_id IS NULL order by '.$GLOBALS['Tu'].'.'.$GLOBALS['dbUserId'].' '.$sortSql.' '.$GLOBALS['makeLim']);
else $result=mysqli_query($GLOBALS['mysqlink'], "select {$what} from {$GLOBALS['Tu']} where num_posts=0 and num_topics=0 order by {$GLOBALS['dbUserId']} {$sortSql} {$GLOBALS['makeLim']}");
if($result) {
$GLOBALS['countRes']=mysqli_num_rows($result);
$GLOBALS['result']=$result;
}
}
if(isset($GLOBALS['countRes']) and $GLOBALS['countRes']>0) return mysqli_fetch_row($GLOBALS['result']);
else return FALSE;
}

function db_deadUsers($sus,$less,$sortSql='asc'){
/*Admin-dead users*/
if(!$sus){
$GLOBALS['makeLim']=(isset($GLOBALS['makeLim'])?'LIMIT '.$GLOBALS['makeLim']:'');
$sql="select {$GLOBALS['Tu']}.{$GLOBALS['dbUserId']}, {$GLOBALS['Tu']}.{$GLOBALS['dbUserSheme']['username'][1]}, {$GLOBALS['Tu']}.{$GLOBALS['dbUserDate']}, {$GLOBALS['Tu']}.{$GLOBALS['dbUserSheme']['user_password'][1]}, {$GLOBALS['Tu']}.{$GLOBALS['dbUserSheme']['user_email'][1]}, max({$GLOBALS['Tp']}.post_time) as m, {$GLOBALS['Tu']}.{$GLOBALS['dbUserSheme']['num_posts'][1]} from {$GLOBALS['Tu']}, {$GLOBALS['Tp']} where {$GLOBALS['Tu']}.{$GLOBALS['dbUserId']}={$GLOBALS['Tp']}.poster_id group by {$GLOBALS['Tp']}.poster_id having m<'{$less}' order by {$GLOBALS['Tu']}.{$GLOBALS['dbUserId']} {$sortSql} {$GLOBALS['makeLim']}";
$result=mysqli_query($GLOBALS['mysqlink'], $sql);
if($result){
$GLOBALS['countRes']=mysqli_num_rows($result);
$GLOBALS['result']=$result;
}
}
if(isset($GLOBALS['countRes']) and $GLOBALS['countRes']>0) return mysqli_fetch_row($GLOBALS['result']);
else return FALSE;
}

function db_calcAmount($tbName,$tbKey,$tbVal,$setName,$setField,$tbKey2=''){
/* Function to get amount of values from table $tbName by criteria $tbKey=$tbVal; then update table's $setName field $setField by this amount */
if($res=mysqli_query($GLOBALS['mysqlink'], 'select count(*) from '.$tbName.' where '.$tbKey.'='.$tbVal) and mysqli_num_rows($res)>0 and $row=mysqli_fetch_row($res)) $amount=$row[0];
else $amount=0;
if($tbKey2=='') $tbKey2=$tbKey;
mysqli_query($GLOBALS['mysqlink'], 'update '.$setName.' set '.$setField.'='."'".$amount."'".' where '.$tbKey2.'='.$tbVal);
return $amount;
}

function db_calcTotalUserAmount($user_id, $tbUsers, $tbTopics, $tbPosts, $tbArcStats, $archive=''){
/* Calculating amount of user posts, taking into attention all possible archives, both live and archived topics/posts */
if($res=mysqli_query($GLOBALS['mysqlink'], "select count(*) from {$tbTopics} where topic_poster={$user_id}") and mysqli_num_rows($res)>0 and $row=mysqli_fetch_row($res)) $num_live_topics=$row[0];
else $num_live_topics=0;

if($res=mysqli_query($GLOBALS['mysqlink'], "select count(*) from {$tbPosts} where poster_id={$user_id}") and mysqli_num_rows($res)>0 and $row=mysqli_fetch_row($res)) $num_live_posts=$row[0];
else $num_live_posts=0;

$num_archived_topics=0;
$num_archived_posts=0;
$currentArchive=FALSE;
$arcSql='';

if($archive!=''){
mysqli_query($GLOBALS['mysqlink'], "update {$tbArcStats} set num_topics='{$num_live_topics}', num_posts='{$num_live_posts}' where user_id='{$user_id}' and archive_id='{$archive}'");

if($res=mysqli_query($GLOBALS['mysqlink'], "select sum(num_topics), sum(num_posts) from {$tbArcStats} where user_id={$user_id} and archive_id!='{$archive}'") and mysqli_num_rows($res)>0 and $row=mysqli_fetch_row($res)) { $otherArchivedTopics=$row[0]; $otherArchivedPosts=$row[1]; } else { $otherArchivedTopics=0; $otherArchivedPosts=0; }

$num_archived_topics=$num_live_topics+$otherArchivedTopics;
$num_archived_posts=$num_live_posts+$otherArchivedPosts;
$currentArchive=TRUE;

if($res=mysqli_query($GLOBALS['mysqlink'], "select {$GLOBALS['dbUserSheme']['num_live_topics'][1]}, {$GLOBALS['dbUserSheme']['num_live_posts'][1]} from {$tbUsers} where {$GLOBALS['dbUserId']}={$user_id}") and mysqli_num_rows($res)>0 and $row=mysqli_fetch_row($res)) {
$num_live_topics=$row[0];
$num_live_posts=$row[1];
}
}

if(isset($GLOBALS['archives']) or defined('ARCHIVING_PROCESS')){
if(!$currentArchive and $res=mysqli_query($GLOBALS['mysqlink'], "select sum(num_topics), sum(num_posts) from {$tbArcStats} where user_id={$user_id}") and mysqli_num_rows($res)>0 and $row=mysqli_fetch_row($res)) {
$num_archived_topics=$row[0];
$num_archived_posts=$row[1];
}

if($archive=='') $arcSql=", {$GLOBALS['dbUserSheme']['num_live_topics'][1]}='{$num_live_topics}', {$GLOBALS['dbUserSheme']['num_live_posts'][1]}='{$num_live_posts}' ";
}

$num_topics=$num_live_topics+$num_archived_topics;
$num_posts=$num_live_posts+$num_archived_posts;

mysqli_query($GLOBALS['mysqlink'], "update {$tbUsers} set {$GLOBALS['dbUserSheme']['num_topics'][1]}='{$num_topics}', {$GLOBALS['dbUserSheme']['num_posts'][1]}='{$num_posts}' {$arcSql} where {$GLOBALS['dbUserId']}='{$user_id}'");

/* Users having less than allowHyperlinksProfile posts made, will have not be allowed to keep the hyperlink in the profile under the user_website field. Doesn't affect admins. */
if($user_id!=1 and !checkModerator($GLOBALS['mods'], $user_id) and $num_live_posts<$GLOBALS['allowHyperlinksProfile'] and isset($GLOBALS['dbUserSheme']['user_website'])){
mysqli_query($GLOBALS['mysqlink'], "update {$tbUsers} set {$GLOBALS['dbUserSheme']['user_website'][1]} = '' where {$GLOBALS['dbUserId']} = {$user_id}");
}

return array($num_live_topics, $num_live_posts, $num_archived_topics, $num_archived_posts);
}

function db_calcTotalStats($prefix, $tbTopics, $tbPosts, $tbArcStats){

if($res=mysqli_query($GLOBALS['mysqlink'], "select count(*) from {$tbTopics}") and mysqli_num_rows($res)>0 and $row=mysqli_fetch_row($res)) $GLOBALS['num_topics']=$row[0];
else $GLOBALS['num_topics']=0;

if($res=mysqli_query($GLOBALS['mysqlink'], "select count(*) from {$tbPosts}") and mysqli_num_rows($res)>0 and $row=mysqli_fetch_row($res)) $GLOBALS['num_posts']=$row[0];
else $GLOBALS['num_posts']=0;

$GLOBALS['archive_id']=$prefix;

if($res=mysqli_query($GLOBALS['mysqlink'], "select archive_id from {$tbArcStats} where archive_id='{$prefix}'") and mysqli_num_rows($res)>0){
updateArray(array('num_posts', 'num_topics'), $tbArcStats, 'archive_id', $prefix);
}
else{
insertArray(array('archive_id', 'num_posts', 'num_topics'), $tbArcStats);
}

}

function db_searchSelect($sus, $table='', $fields='', $sqlstr='', $makeLim='', $orderBy=''){
if(!$sus){
$sql='SELECT '.$fields.' FROM '.$table.' WHERE '.$sqlstr.' ';
if($orderBy!='') $sql.="ORDER BY $orderBy ";
if($makeLim!='') $sql.='LIMIT '.$makeLim;
//echo "!-- ".$sql." --><br />";
$result=mysqli_query($GLOBALS['mysqlink'], $sql);
if($result) {
$GLOBALS['countRes']=mysqli_num_rows($result);
$GLOBALS['result']=$result;
}
}
if( ($sus==1 OR isset($result) ) AND isset($GLOBALS['countRes']) AND $GLOBALS['countRes']>0) return mysqli_fetch_row($GLOBALS['result']);
else return FALSE;
}

function db_genPhrase($phrase,$where,$searchType){
if($where==0) $field='post_text';
elseif($where==1) $field='topic_title';
$phrase=str_replace('$', '&#036;', $phrase);

$sql=' (';

if($searchType==0 or $searchType==3){
$words=explode(' ',$phrase);
$sz=sizeof($words);
$gen='';

if($searchType==0){
foreach($words as $w) {
$w=trim(str_replace('%','',$w));
if($w!='' and strlen_unicode($w)>2) $gen.="{$w}% ";
}
$gen=trim($gen);
$sql=" ($field like '% {$gen}' or $field like '%>{$gen}' or $field like '{$gen}' or $field like '%;{$gen}' or $field like '".substr($gen,0,strlen($gen)-1).".') ";
}

elseif($searchType==3){

if($sz==1){
$sql=" ($field like '%{$words[0]}%') ";
}
elseif($sz==2){
$sql=" ($field like '%{$words[0]}%{$words[1]}%'";
if(!defined('NO_SWAP_SEARCH')) $sql.=" or $field like '%{$words[1]}%{$words[0]}%'";
$sql.=') ';
}
elseif($sz==3){
$sql=" ($field like '%{$words[0]}%{$words[1]}%{$words[2]}%'";

if(!defined('NO_SWAP_SEARCH')) $sql.=" or $field like '%{$words[0]}%{$words[2]}%{$words[1]}%' or $field like '%{$words[1]}%{$words[0]}%{$words[2]}%' or $field like '%{$words[1]}%{$words[2]}%{$words[0]}%' or $field like '%{$words[2]}%{$words[1]}%{$words[0]}%' or $field like '%{$words[2]}%{$words[0]}%{$words[1]}%'";
$sql.=') ';
}

}

}
else $sql=" ($field like '%{$phrase}%') ";

//echo $sql;

return $sql;
}

?>