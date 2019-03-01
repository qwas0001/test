<?php
/*
This file is part of miniBB. miniBB is free discussion forums/message board software, without any warranty. 
Check COPYING file for more details.
Copyright (C) 2013-2018 Paul Puzyrev. www.minibb.com
Latest File Update: 2018-Nov-05
*/
if (!defined('INCLUDED776')) die ('Fatal error.');

$warning=''; $editable='';
$actionName='register';

$pluginsFile=$pathToFiles.'bb_plugins_user.php';
$moDBValue=array();

foreach($dbUserSheme as $key=>$val) if(isset(${$val[2]})) unset(${$val[2]});
if(isset($passwd2)) unset($passwd2);

if ($user_id==0 or $user_id==1){

include($pathToFiles.'bb_func_inslng.php');
if(isset($_POST['user_viewemail'])) $user_viewemail=(int)$_POST['user_viewemail']+0;
$showemailDown=makeValuedDropDown(array(0=>$l_no,1=>$l_yes),'user_viewemail');
if(isset($_POST['user_sorttopics'])) $user_sorttopics=(int)$_POST['user_sorttopics']+0;
$sorttopicsDown=makeValuedDropDown(array(0=>$l_newAnswers,1=>$l_newTopics),'user_sorttopics');
if(!isset($_POST['language'])) $language=$lang; else $language=operate_string($_POST['language']);
$languageDown=makeValuedDropDown($glang,'language');

if(isset($user_usr) and $step==0) $login=$user_usr;
$userTitle=$l_newUserRegister;

if($step==1) {

//insert Admin account if it's not present
if(!db_simpleSelect(0,$Tu,$dbUserId,$dbUserId,'=',1)){

$insArr=array($dbUserId, $dbUserSheme['username'][1], $dbUserSheme['user_password'][1], $dbUserSheme['user_email'][1], $dbUserDate);

foreach($insArr as $k){
if(isset($$k)) ${'tmp_'.$k}=$$k;
}

${$dbUserId}=1;
${$dbUserSheme['username'][1]}=$admin_usr;
${$dbUserSheme['user_password'][1]}=writeUserPwd($admin_pwd);
${$dbUserSheme['user_email'][1]}=$admin_email;
${$dbUserDate}=date('Y-m-d H:i:s');

insertArray($insArr, $Tu);

foreach($insArr as $k){
if(isset(${'tmp_'.$k})) $$k=${'tmp_'.$k};
}

}//admin acc.

if(isset($closeRegister) and $closeRegister==1) {
$_POST['passwd']=substr(preg_replace("#[^0-9A-Za-z]+#", "A", writeUserPwd(uniqid(rand()))),0,8);
$_POST['passwd2']=$_POST['passwd'];
}

require($pathToFiles.'bb_func_usrdat.php');

$userErrorMsg='';

if (db_simpleSelect(0,$Tu,$dbUserId,$dbUserSheme['username'][1],'=',${$dbUserSheme['username'][2]}) or db_simpleSelect(0,$Tu,$dbUserId,$caseComp."({$dbUserSheme['username'][1]})",'=',strtolower_unicode(${$dbUserSheme['username'][2]})) or ${$dbUserSheme['username'][2]}==$admin_usr or strtolower_unicode(${$dbUserSheme['username'][2]})==strtolower_unicode($admin_usr)) {
$userErrorMsg=$l_errorUserExists;
}
elseif (db_simpleSelect(0,$Tu,$dbUserId,$caseComp."({$dbUserSheme['user_email'][1]})",'=',strtolower(${$dbUserSheme['user_email'][2]})) or strtolower(${$dbUserSheme['user_email'][2]})==strtolower($admin_email) or (isset($reply_to_email) and strtolower(${$dbUserSheme['user_email'][2]})==strtolower($reply_to_email) ) ) {
$userErrorMsg=$l_errorEmailExists;
}

if($userErrorMsg==''){

$act='reg';
require($pathToFiles.'bb_func_checkusr.php');

if(file_exists($pluginsFile)) include($pluginsFile);

if ($correct==0) {

$addFieldsGen=array('user_icq','user_website','user_occ','user_from','user_interest');

${$dbUserDate}=date('Y-m-d H:i:s');
${$dbUserSheme['user_password'][1]}=writeUserPwd(${$dbUserSheme['user_password'][1]});
if(isset($registerInactiveUsers) and $registerInactiveUsers) ${$dbUserAct}=0; else ${$dbUserAct}=1;

$insa=array($dbUserSheme['username'][1], $dbUserDate, $dbUserSheme['user_password'][1], $dbUserSheme['user_email'][1], $dbUserSheme['user_viewemail'][1], $dbUserSheme['user_sorttopics'][1], $dbUserSheme['language'][1], $dbUserAct);

foreach($addFieldsGen as $k) if(isset($dbUserSheme[$k])) $insa[]=$dbUserSheme[$k][1];
foreach($dbUserSheme as $k=>$v) if(strstr($k,'user_custom') and isset($_POST[$v[2]]) and $_POST[$v[2]]!='') $insa[]=$v[1];

//plugins...
foreach($moDBValue as $mk=>$mv){
if(in_array($mk, $insa) and isset($$mk)) $$mk=$mv;
}

$inss=insertArray($insa,$Tu);

if ($inss==0) {

if (($emailusers>0 OR (isset($closeRegister) and $closeRegister==1)) and $genEmailDisable!=1){

if($emailusers==2 and $lng=${$dbUserSheme['language'][2]} and file_exists($pathToFiles.'templates/email_user_register_'.$lng.'.txt')) {} else $lng=$langOrig;

$emailMsg=ParseTpl(makeUp('email_user_register_'.$lng));
$sub=explode('SUBJECT>>', $emailMsg); $sub=explode('<<', $sub[1]); $emailMsg=trim($sub[1]); $sub=$sub[0];
sendMail(${$dbUserSheme['user_email'][2]}, $sub, $emailMsg, $reply_to_email, $reply_to_email);
}

if ($emailadmin==1 and $genEmailDisable!=1) {
$emailMsg=operate_string(ParseTpl(makeUp('email_admin_userregister_'.$langOrig)), TRUE);
$sub=explode('SUBJECT>>', $emailMsg); $sub=explode('<<', $sub[1]); $emailMsg=trim($sub[1]); $sub=$sub[0];
sendMail($admin_email, $sub, $emailMsg, $reply_to_email, $reply_to_email);
}

/* Auto Sign-in */
if(!isset($closeRegister) or $closeRegister==0){
deleteMyCookie();
setMyCookie($username, $passwd, $cookieexptime);
setCSRFCheckCookie();
}

$title.=$l_userRegistered;
$errorMSG=$l_thankYouReg;
$correctErr=$l_goToLogin;
$tpl=makeUp('main_warning');
}

else {
$title.=$l_itseemserror;
$errorMSG=$l_itseemserror;
$correctErr=$backErrorLink;
$tpl=makeUp('main_warning');
}


}
else {
$action='registernew';
if(file_exists($pluginsFile)) include($pluginsFile);
if (!isset($l_userErrors[$correct])) $l_userErrors[$correct]=$l_undefined;
$warning=$l_userErrors[$correct];
$title=$l_errorUserData;
$tpl=makeUp('user_dataform');
$tpl=preg_replace(array("#<!--WEBSITE-->(.*)<!--/WEBSITE-->#is", "#<!--INITMSG-->(.*?)<!--/INITMSG-->#is"), '', $tpl);
if(isset($closeRegister) and $closeRegister==1) $tpl=preg_replace("#<!--PASSWORD-->(.*)<!--/PASSWORD-->#is",'',$tpl);
}

}
else {
$action='registernew';
if(file_exists($pluginsFile)) include($pluginsFile);
$title=$l_errorUserData;
$warning=$userErrorMsg;
$tpl=makeUp('user_dataform');
$tpl=preg_replace(array("#<!--WEBSITE-->(.*)<!--/WEBSITE-->#is", "#<!--INITMSG-->(.*?)<!--/INITMSG-->#is"), '', $tpl);
if(isset($closeRegister) and $closeRegister==1) $tpl=preg_replace("#<!--PASSWORD-->(.*)<!--/PASSWORD-->#is",'',$tpl);
}

echo load_header(); echo ParseTpl($tpl); return;

}//step==1
else{
//step==0
if($user_id==1) $login='';
if(file_exists($pluginsFile)) include($pluginsFile);
$title=$l_newUserRegister;
$tpl=makeUp('user_dataform');
$tpl=preg_replace("#<!--WEBSITE-->(.*)<!--/WEBSITE-->#is",'',$tpl);
if(isset($closeRegister) and $closeRegister==1) $tpl=preg_replace("#<!--PASSWORD-->(.*)<!--/PASSWORD-->#is",'',$tpl);
echo load_header(); echo ParseTpl($tpl); return;
}

}
else {
$title=$l_userRegistered;
$errorMSG=$l_userRegistered;
$correctErr=$backErrorLink;
echo load_header(); echo ParseTpl(makeUp('main_warning')); return;
}
?>