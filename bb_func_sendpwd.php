<?php
/*
This file is part of miniBB. miniBB is free discussion forums/message board software, supplied with no warranties. 
Check COPYING file for more details.
Copyright (C) 2015, 2018 Paul Puzyrev. www.minibb.com
Latest File Update: 2018-Nov-19
*/
if (!defined('INCLUDED776')) die ('Fatal error.');

if (!isset($genEmailDisable) or $genEmailDisable!=1){

$newPasswd=''; $confirmCode='';

$email=(isset($_POST['email'])?strtolower_unicode(operate_string($_POST['email'])):'');

if ($email==$admin_email) $email='';

if ($step!=1) {
$title.=$l_sub_pass;
echo load_header(); echo ParseTpl(makeUp('tools_send_password')); return;
}
else {

if (!($updId=db_simpleSelect(0,$Tu,"{$dbUserId}, {$dbUserSheme['language'][1]}, {$dbUserSheme['username'][1]},{$dbUserAct}", $caseComp.'('.$dbUserSheme['user_email'][1].')', '=', $email))) {

}
else {
if($updId[3]==0){
$title.=$l_accessDenied;
$errorMSG=$l_accessDenied;
$correctErr='';
echo load_header(); echo ParseTpl(makeUp('main_warning')); return;
}
else{

$ulang=$updId[1];
$loginName=$updId[2];

${$dbUserNp}=substr(preg_replace("#[^0-9A-Za-z]+#", "A", writeUserPwd(uniqid(rand()))),0,8); $newPasswd=${$dbUserNp};
${$dbUserNk}=substr(md5(uniqid(rand())),0,32); $confirmCode=${$dbUserNk};

$updArr=array($dbUserNp,$dbUserNk);
$fs=updateArray($updArr,$Tu,$dbUserId,$updId[0]);

if ($fs>0) {
if($emailusers==2 and file_exists($pathToFiles.'templates/email_user_password_'.$ulang.'.txt')) $langS=$ulang; else $langS=$langOrig;
$msg=ParseTpl(makeUp('email_user_password_'.$langS));
$sub=explode('SUBJECT>>', $msg); $sub=explode('<<', $sub[1]); $msg=trim($sub[1]); $sub=$sub[0];
sendMail($email, $sub, $msg, $reply_to_email, $reply_to_email);
$title.=$l_emailSent;
$errorMSG=$l_emailSent;
$correctErr='';
echo load_header(); echo ParseTpl(makeUp('main_warning')); return;
}
else {
$title.=$l_itseemserror;
$errorMSG=$l_itseemserror;
$correctErr='';
echo load_header(); echo ParseTpl(makeUp('main_warning')); return;
}

}

}

$title.=$l_sub_pass;
$errorMSG=$l_emailSent;
$correctErr='';
echo load_header(); echo ParseTpl(makeUp('main_warning')); return;

}

}
else {
$title.=$l_accessDenied;
$errorMSG=$l_accessDenied;
$correctErr='';
echo load_header(); echo ParseTpl(makeUp('main_warning')); return;
}
?>