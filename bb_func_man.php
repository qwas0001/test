<?php
/*
This file is part of miniBB. miniBB is free discussion forums/message board software, supplied with no warranties.
Check COPYING file for more details.
Copyright (C) 2017-2018 Paul Puzyrev. www.minibb.com
Latest File Update: 2018-11-27
*/
if (!defined('INCLUDED776')) die ('Fatal error.');

$tpl=makeUp('faq');

$tplTmp=explode('{$manual}', $tpl);

$title.=$l_menu[4];
if(!defined('DISABLE_MANUAL_STYLE')){
$l_meta.=<<<out

<style type="text/css">
P, PRE, LI, UL, OL{
font-size:16px;
line-height:26px;
}

P{
color: #000000;
text-indent:13px;
}

P.special{
background-color:#E2E2E2;
margin-left:15px;
text-indent:0px;
padding:4px;
}

SMALL{
font-size:14px;
color:#707070;
}

H1, H2{
font-weight:bold;
color: #775454;
margin-top:13px;
margin-bottom:13px;
text-shadow:#FFE25B 1px 1px 1px;
}

H1{
font-size:22px;
}

H2{
font-size:19px;
}

UL, OL, LI{
color:#000000;
text-decoration: none;
margin-top: 0px;
margin-bottom: 0px;
margin-right: 0px;
margin-left: 5px;
list-style: circle;
padding-bottom:3px;
}

A:link, A:active, A:visited, A:hover{
color:#775454;
}

A:hover{
text-decoration:none;
}

A:hover{
background-color:#F8EFE0;
}

/*
.manhyph {
overflow-wrap: break-word;
word-wrap: break-word;
-ms-word-break: break-all;
word-break: normal;
}
*/

</style>
out;
}
echo load_header();
echo $tplTmp[0];
if(file_exists($pathToFiles.'templates/manual_'.$lang.'.html')) include($pathToFiles.'templates/manual_'.$lang.'.html');
elseif(file_exists($pathToFiles.'templates/manual_eng.html')) include($pathToFiles.'templates/manual_eng.html');
echo $tplTmp[1];

?>