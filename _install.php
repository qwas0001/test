<?php
/*
This file is part of miniBB. miniBB is free discussion forums/message board software, without any warranty.
Check COPYING file for more details.
Copyright (C) 2017 Paul Puzyrev. www.minibb.com
Latest File Update: 2017-Aug-02
*/
define ('INCLUDED776',1);

if(isset($_GET['analysis'])) $anal=TRUE; else $anal=FALSE;

include ('./setup_options.php');

if(!$anal){
include ($pathToFiles.'bb_cookie.php');
include ($pathToFiles."setup_$DB.php");
if(!isset($GLOBALS['indexphp'])) $indexphp='index.php?'; else $indexphp=$GLOBALS['indexphp'];
$step=(isset($_GET['step'])?$_GET['step']:'');

$namesArray=array('minibbtable_forums','minibbtable_posts','minibbtable_topics','minibbtable_users','minibbtable_send_mails','minibbtable_banned',"\r\n","\n");
$replNamesArray=array($Tf,$Tp,$Tt,$Tu,$Ts,$Tb,'','');

if($DB=='mysql' or $DB=='mysqli') $DBins='mysql';

}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head><title>miniBB installation</title>
<link href="<?php echo $main_url; ?>/css/<?php echo $skin; ?>.css" type="text/css" rel="STYLESHEET" />
<?php echo $fontsCSS; ?>
<style type="text/css">
P{
margin-bottom:12pt;
}
</style>
</head>
<body class="gbody">
<table class="forums" style="width:50%"><tr><td class="caption5">
<?php

if (!$anal and !file_exists("./_install_{$DBins}.sql")) {
echo "<p>Installation file is missing. Please, check your directory for _install_{$DBins}.sql file!</p>";
}

else {

function ini_get_bool($a){
$b = ini_get($a);
switch (strtolower($b)){
case 'on':
case 'yes':
case 'true':
return 'assert.active' !== $a;

case 'stdout':
case 'stderr':
return 'display_errors' === $a;

default:
return (bool) (int) $b;
}
}

if($anal) $step='';

switch ($step) {
case 'install':

$errors=0;
$warn='';
$buffer='';

$fd=fopen ($pathToFiles."_install_{$DBins}.sql", 'r');
while (!feof($fd)) {
$buffer.=fgets($fd,1024);
if(substr_count($buffer,';')>0) {
$buffer=str_replace($namesArray,$replNamesArray,$buffer);
preg_match("#CREATE TABLE (.+?) (like|\()#i",$buffer,$arr);
$tName=$arr[1];

if($DB=='mysql') {
mysql_query($buffer);
$er=mysql_error();
}
elseif($DB=='mysqli') {
mysqli_query($mysqlink, $buffer);
$er=mysqli_error($mysqlink);
}

if ($er and strlen($er)>0) {
$errors++;
$warn.="<div>Creating table {$tName} failed... (".$er.")</div>";
}
else $warn.="<div>Table {$tName} successfully created...</div>";
$buffer='';
}
}

if ($errors==0) {
$now=date('Y-m-d H:i:s');

$uSql="INSERT INTO $Tu ({$dbUserId}, {$dbUserSheme['username'][1]}, {$dbUserSheme['user_password'][1]}, {$dbUserSheme['user_email'][1]}, {$dbUserSheme['user_viewemail'][1]}, {$dbUserDate}) values (1, '$admin_usr', '".writeUserPwd($admin_pwd)."', '$admin_email', 0, '{$now}')";

if($DB=='mysql') {
mysql_query($uSql);
$er=mysql_error();
}
elseif($DB=='mysqli') {
mysqli_query($mysqlink, $uSql);
$er=mysqli_error($mysqlink);
}

if (!$er or strlen($er)==0) $warn.="<p>Admin data successfully added...</p>";

$warn.="
<p>All tables successfully created! Now you can:</p>
<ul>
<li>Continue with miniBB options (see setup_options.php file)</li>
<li><a href=\"{$main_url}/{$bb_admin}action=addforum1\" class=\"mnblnk\">Create forums</a></li>
<li><a href=\"{$main_url}/{$bb_admin}\" class=\"mnblnk\">Go to admin panel</a>...</li>
<p>...<a href=\"{$main_url}/{$indexphp}\" class=\"mnblnk\">and use your miniBB right now!</a> :)</p>
</ul>
<p><strong>Don't forget to DELETE the <em>_install.php</em> and <em>_install_{$DBins}.sql</em> files from your miniBB forums folder!</strong></p>
<p>DO IT RIGHT NOW!!!</p>
";
}
else {
$warn.="
<p>&nbsp;</p>
<p>There were problems during setup! Possible reasons:</p>
<ul>
<li>Creating tables is not allowed for your database account;</li>
<li>Login and/or password for the database are specified wrong;</li>
<li>You haven't created the database specified in the setup_options.php file (possibly you need to do it manually using DB console or mySQL management tool like for ex. phpMyAdmin);</li>
<li>Tables are created already and you can directly <a href=\"{$main_url}/{$indexphp}\" class=\"mnblnk\">go to Forums now</a>, or to <a href=\"{$main_url}/{$bb_admin}\" class=\"mnblnk\">Admin Panel</a>.</li>
</ul>
<p>Please, refer to our manual for more questions, check your setup files, or manually create all DB tables.</p>
<p>If forums are installed already, <strong>Don't forget to DELETE the <em>_install.php</em> and <em>_install_{$DBins}.sql</em> files from your miniBB forums folder!</strong></p>
<p>DO IT RIGHT NOW!!!</p>
";
}

echo $warn;
break;

default:

if(ini_get_bool('register_globals')) {
$register_globals_value='ON';
$register_globals_text=<<<out
<span style="color:red"><b>Dangerous</b>. As recommended by PHP authors, this setting is turned to OFF in the default PHP configuration "<b>because it often leads to security bugs</b>". Read <a href="http://php.net/manual/en/security.registerglobals.php" target="_blank" class="mnblnk">http://php.net/manual/en/security.registerglobals.php</a> for further information.</span>
out;
} else {
$register_globals_value='OFF';
$register_globals_text=<<<out
<span style="color:green"><b>Perfect!</b> This setting is configured accordingly to PHP and miniBB authors recommendation.</span>
out;
}

if(ini_get_bool('safe_mode')) {
$safe_mode_value='ON';
$safe_mode_text=<<<out
<span style="color:black"><b>Limited abilities</b>. Despite <b>miniBB runs with no troubles even in Safe Mode</b>, you may experience difficulties in executing additional miniBB files-based extensions like <a href="http://www.minibb.com/private_messaging.html" target="_blank" class="mnblnk">Private Messaging</a> in flat-files mode, <a href="http://www.minibb.com/fileupload.html" target="_blank" class="mnblnk">File Uploadings</a>, <a href="http://www.minibb.com/checker.html" target="_blank" class="mnblnk">Checker</a> and others, because in Safe Mode, PHP scripts are often not allowed to dynamically create subfolders, as it is required by these extensions in order to get better performance. In Safe Mode, some specific PHP functions may be also forbidden to execute.</span>
out;
} else {
$safe_mode_value='OFF';
$safe_mode_text=<<<out
<span style="color:green"><b>Perfect!</b> Your server is running in full mode. You should be able to install and run many additional miniBB fiels-based extensions like <a href="http://www.minibb.com/private_messaging.html" target="_blank" class="mnblnk">Private Messaging</a>, <a href="http://www.minibb.com/fileupload.html" target="_blank" class="mnblnk">File Attachments</a>, <a href="http://www.minibb.com/checker.html" target="_blank" class="mnblnk">Checker</a> and others with no major problems.</span>
out;
}

if(function_exists('gd_info') and $gd=gd_info()){
$gd_value=$gd['GD Version'].' '.(isset($gd['FreeType Support'])?'with Freetype support':'without Freetype support');
$gd_text=<<<out
<span style="color:green"><b>Perfect!</b> If there are no special exceptions, you will be able to install and run <a href="http://www.minibb.com/captcha.html" target="_blank" class="mnblnk">Human Authorization (Captcha) module</a> for your forums, as well as <a href="http://www.minibb.com/fileupload.html" target="_blank" class="mnblnk">Automated Image Galleries extension</a> (which will be able to produce thumbnails). <i>Note: For Captcha module, Freetype support also must be installed</i>.</span>
out;

}
else{
$gd_value='Disabled/Not available.';
$gd_text=<<<out
<span style="color:red"><b>No graphics support</b>. If needed later, you will be unable to install and run <a href="http://www.minibb.com/captcha.html" target="_blank" class="mnblnk">Human Authorization (Captcha) module</a> for your forums, as well as <a href="http://www.minibb.com/fileupload.html" target="_blank" class="mnblnk">Automated Image Galleries extension</a> (which will be unable to produce thumbnails).</span>
out;

}

$phpVer=phpversion();
$fp=substr($phpVer, 0, 1)+0;
if($fp<5) {
$php_text=<<<out
<span style="color:black"><b>PHP is not up-to-date</b>. In most cases miniBB could run with any earlier PHP version, beginning from <b>4.1.0</b>. However if you have a lower version, which is discontinued by PHP programmers team, PHP executables could contain some security holes and bugs which will cause in normal miniBB execution. <a href="http://www.php.net/downloads.php" target="_blank" class="mnblnk">Check php.net</a> for more information regarding currently supported version of PHP.</span>
out;
}
else{
$php_text=<<<out
<span style="color:green"><b>Perfect!</b>. You have an up-to-date PHP version which is recommended for miniBB.</span>
out;
}

if(ini_get_bool('short_open_tag')) {
$short_open_tag_value='ON';
$short_open_tag_text=<<<out
<span style="color:red"><b>Dangerous</b>. Referrencing PHP authors, it's been recommended that you not use the short tag "short cut" and instead to use the full &lt?php and ?&gt; tag combination. Read <a href="http://php.net/manual/en/ini.core.php#ini.short-open-tag" target="_blank" class="mnblnk">http://php.net/manual/en/ini.core.php#ini.short-open-tag</a> for further information.</span>
out;
} else {
$short_open_tag_value='OFF';
$short_open_tag_text=<<<out
<span style="color:green"><b>Perfect!</b> This setting is configured accordingly to PHP and miniBB authors recommendation.</span>
out;
}

if($fd=@fopen('./test.txt', 'w')){
$fw=@fwrite($fd, 'miniBB');
@fclose($fd);
@chmod('./test.txt', 0777);
$fu=@unlink('./test.txt');
}

if($fd and $fw and $fu){
$folder_text=<<<out
<span style="color:red"><b>Vulnerable</b>. In your current forums folder, where are you are going to install the forums, PHP script is able to create, delete and modify files. We recommend to set permissions for generic forums folder as read-only.</span>
out;
}
else {
$folder_text=<<<out
<span style="color:green"><b>Perfect!</b> Our script was not able to create and modify a test file within your upcoming forums folder. That means no other 3rd party script will allowed to modify the system script files.</span>
out;

}

if(function_exists('mb_substr') and function_exists('mb_strlen') and function_exists('mb_strtolower')){
$mb_string_text=<<<out
<span style="color:grey"><b>Opt-out</b>. These functions are missing in the PHP bundle. But that's not critical - miniBB will use its own functions for parsing Unicode texts and strings.</span>
out;
}
else{
$mb_string_text=<<<out
<span style="color:green"><b>Perfect!</b> You have these functions bundled with PHP, that means your forum could proceed faster in working with Unicode strings.</span>
out;
}

if(!$anal){
echo <<<out
<p>Welcome to <b>miniBB&reg; setup</b>!</p>

<p>Before installing, copying or modifying miniBB, please, read the <strong><a href="COPYING" class="mnblnk">License agreement.</a></strong></p>

<p>Make sure you have properly modified the file called "setup_options.php" <strong>first</strong>! Refer to <a href="./templates/manual_eng.html" class="mnblnk">manual</a> if you are having problems.</p>

<p>It takes only 1 step to create all necessary database tables. You must have necessary database user privileges for that.</p>

<p>&nbsp;</p>
<p style="text-align:center"><a href="_install.php?step=install" class="mnblnk"><b><u>CONTINUE SETUP</u></b></a>&nbsp;&gt;&gt;&gt;</p>
<p>&nbsp;</p>

out;
}


echo <<<out
<p>Just for your knowledge, below follows the automated report of <b>the most important settings</b> miniBB could meet on your <b>hosting server</b>. Please study all the conditions determined and take the corresponding action, if necessary. Remember that you will use the free open source software <b>at your own risk</b>, and <b>you must instantly follow all critical updates</b>, always keeping your forums not vulnerable and up-to-date. We're reporting all important updates in our community's <a href="http://www.minibb.com/forums/9_0.html" target="_blank" class="mnblnk">News &amp; Announcements section</a>. You may also watch these news via <a href="http://www.minibb.com/forums/rss2.php" target="_blank" class="mnblnk">RSS feed</a>.</p>

<table style="width:100%;border:1px #000 solid;border-collapse:separate">

<tr style="background-color:#99CCCC;font-weight:bold">
<td style="vertical-align:top;width:30%;text-align:center">Setting's description</td><td style="vertical-align:top;width:70%;text-align:center">What it means</td>
</tr>

<tr>
<td style="vertical-align:top;border-top:1px solid gray"><b>PHP version</b>: {$phpVer}</td><td style="vertical-align:top;border-top:1px solid gray">{$php_text}</td>
</tr>

<tr>
<td style="vertical-align:top;border-top:1px solid gray"><b>register_globals</b>: {$register_globals_value} (php.ini)</td><td style="vertical-align:top;border-top:1px solid gray">{$register_globals_text}</td>
</tr>

<tr>
<td style="vertical-align:top;border-top:1px solid gray"><b>Short Open Tag</b>: {$short_open_tag_value} (php.ini)</td><td style="vertical-align:top;border-top:1px solid gray">{$short_open_tag_text}</td>
</tr>

<tr>
<td style="vertical-align:top;border-top:1px solid gray"><b>Folder's protection</b></td><td style="vertical-align:top;border-top:1px solid gray">{$folder_text}</td>
</tr>

<tr>
<td style="vertical-align:top;border-top:1px solid gray"><b>GD library/Freetype</b>: {$gd_value}</td><td style="vertical-align:top;border-top:1px solid gray">{$gd_text}</td>
</tr>

<tr>
<td style="vertical-align:top;border-top:1px solid gray"><b>Safe Mode</b>: {$safe_mode_value} (php.ini)</td><td style="vertical-align:top;border-top:1px solid gray">{$safe_mode_text}</td>
</tr>

<tr>
<td style="vertical-align:top;border-top:1px solid gray"><b>Multibyte String Functions</b></td><td style="vertical-align:top;border-top:1px solid gray">{$mb_string_text}</td>
</tr>

</table>

out;
}

}
?>
</td></tr></table>
</body>
</html>