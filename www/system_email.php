#!/usr/local/bin/php
<?php
/*
	system_email.php
	Copyright © 2006-2008 Volker Theile (votdev@gmx.de)
	All rights reserved.

	part of FreeNAS (http://www.freenas.org)
	Copyright (C) 2005-2008 Olivier Cochard <olivier@freenas.org>.
	All rights reserved.

	Based on m0n0wall (http://m0n0.ch/wall)
	Copyright (C) 2003-2006 Manuel Kasper <mk@neon1.net>.
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright notice,
	   this list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
	INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
	AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
	AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
	SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
	INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
	CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
	POSSIBILITY OF SUCH DAMAGE.
*/
require("guiconfig.inc");

$pgtitle = array(gettext("System"),gettext("Advanced"),gettext("Email"));

if (!is_array($config['system']['email'])) {
	$config['system']['email'] = array();
}

$pconfig['server'] = $config['system']['email']['server'];
$pconfig['port'] = $config['system']['email']['port'];
$pconfig['auth'] = isset($config['system']['email']['auth']);
$pconfig['security'] = $config['system']['email']['security'];
$pconfig['username'] = $config['system']['email']['username'];
$pconfig['password'] = base64_decode($config['system']['email']['password']);
$pconfig['passwordconf'] = $pconfig['password'];
$pconfig['from'] = $config['system']['email']['from'];

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	$reqdfields = array();
	$reqdfieldsn = array();
	$reqdfieldst = array();

	if ($_POST['auth']) {
		$reqdfields = array_merge($reqdfields, array("username", "password"));
		$reqdfieldsn = array_merge($reqdfieldsn, array(gettext("Username"), gettext("Password")));
		$reqdfieldst = array_merge($reqdfieldst, array("string","string"));
	}

	do_input_validation($_POST, $reqdfields, $reqdfieldsn, &$input_errors);
	do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, &$input_errors);

	// Check for a password mismatch.
	if ($_POST['auth'] && ($_POST['password'] !== $_POST['passwordconf'])) {
		$input_errors[] = gettext("The passwords do not match.");
	}

	if (!$input_errors) {
		$config['system']['email']['server'] = $_POST['server'];
		$config['system']['email']['port'] = $_POST['port'];
		$config['system']['email']['auth'] = $_POST['auth'] ? true : false;
		$config['system']['email']['security'] = $_POST['security'];
		$config['system']['email']['username'] = $_POST['username'];
		$config['system']['email']['password'] = base64_encode($_POST['password']);
		$config['system']['email']['from'] = $_POST['from'];

		write_config();

		$retval = 0;
		$savemsg = get_std_save_message($retval);
	}
}
?>
<?php include("fbegin.inc");?>
<script language="JavaScript">
<!--
function auth_change() {
	switch(document.iform.auth.checked) {
		case false:
      showElementById('username_tr','hide');
  		showElementById('password_tr','hide');
      break;

    case true:
      showElementById('username_tr','show');
  		showElementById('password_tr','show');
      break;
	}
}
//-->
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
    <td class="tabnavtbl">
      <ul id="tabnav">
      	<li class="tabinact"><a href="system_advanced.php"><?=gettext("Advanced");?></a></li>
      	<li class="tabact"><a href="system_email.php" title="<?=gettext("Reload page");?>"><?=gettext("Email");?></a></li>
      	<li class="tabinact"><a href="system_proxy.php"><?=gettext("Proxy");?></a></li>
      	<li class="tabinact"><a href="system_swap.php"><?=gettext("Swap");?></a></li>
      	<li class="tabinact"><a href="system_rc.php"><?=gettext("Command scripts");?></a></li>
        <li class="tabinact"><a href="system_cron.php"><?=gettext("Cron");?></a></li>
        <li class="tabinact"><a href="system_rcconf.php"><?=gettext("rc.conf");?></a></li>
        <li class="tabinact"><a href="system_sysctl.php"><?=gettext("sysctl.conf");?></a></li>
      </ul>
    </td>
  </tr>
  <tr>
    <td class="tabcont">
    	<form action="system_email.php" method="post" name="iform" id="iform">
				<?php if ($input_errors) print_input_errors($input_errors);?>
				<?php if ($savemsg) print_info_box($savemsg);?>
			  <table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php html_inputbox("server", gettext("Outgoing mail server"), $pconfig['server'], gettext("Outgoing SMTP mail server address, e.g. smtp.mycorp.com."), true, 40);?>
					<?php html_inputbox("port", gettext("Port"), $pconfig['port'], gettext("The default SMTP mail server port, e.g. 25 or 587."), true, 10);?>
					<?php html_combobox("security", gettext("Security"), $pconfig['security'], array("none" => "None", "ssl" => "SSL", "tls" => "TLS"), gettext(""), true);?>
					<?php html_checkbox("auth", gettext("Authentication"), $pconfig['auth'] ? true : false, gettext("Enable SMTP authentication."), gettext(""), false, "auth_change()");?>
					<?php html_inputbox("username", gettext("Username"), $pconfig['username'], gettext(""), true, 40);?>
					<?php html_passwordbox("password", "passwordconf", gettext("Password"), $pconfig['password'], $pconfig['passwordconf'], gettext(""), true);?>
					<?php html_inputbox("from", gettext("From email"), $pconfig['from'], gettext("Your own email address."), true, 40);?>
			    <tr>
			      <td width="22%" valign="top">&nbsp;</td>
			      <td width="78%">
			        <input name="Submit" type="submit" class="formbtn" value="<?=gettext("Save");?>" onClick="enable_change(true)">
			      </td>
			    </tr>
			  </table>
			</form>
		</td>
  </tr>
</table>
<script language="JavaScript">
<!--
auth_change();
//-->
</script>
<?php include("fend.inc");?>
