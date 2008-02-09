#!/usr/local/bin/php
<?php
/*
	services_nfs.php
	part of FreeNAS (http://www.freenas.org)
	Copyright (C) 2005-2008 Olivier Cochard-Labbe <olivier@freenas.org>.
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

$pgtitle = array(gettext("Services"),gettext("NFS"));

if(!is_array($config['nfsd']['share']))
	$config['nfsd']['share'] = array();

array_sort_key($config['nfsd']['share'], "path");

$a_share = &$config['nfsd']['share'];

$pconfig['enable'] = isset($config['nfsd']['enable']);

if ($_POST) {
	$pconfig = $_POST;

	$config['nfsd']['enable'] = $_POST['enable'] ? true : false;

	write_config();

	$retval = 0;
	if (!file_exists($d_sysrebootreqd_path)) {
		config_lock();
		$retval |= rc_update_service("rpcbind");    // !!! Do not
		$retval |= rc_update_service("mountd");     // !!! change
		$retval |= rc_update_service("nfsd");       // !!! this
		$retval |= rc_update_service("nfslocking"); // !!! order
		$retval |= rc_update_service("mdnsresponder");
		config_unlock();
	}

	$savemsg = get_std_save_message($retval);

	if (0 == $retval) {
		if (file_exists($d_nfsconfdirty_path))
			unlink($d_nfsconfdirty_path);
	}
}

if ("del" === $_GET['act']) {
	if ($a_share[$_GET['id']]) {
		unset($a_share[$_GET['id']]);

		write_config();
		touch($d_nfsconfdirty_path);

		header("Location: services_nfs.php");
		exit;
	}
}
?>
<?php include("fbegin.inc");?>
<form action="services_nfs.php" method="post" name="iform" id="iform">
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	  <tr>
	    <td class="tabcont">
				<?php if ($savemsg) print_info_box($savemsg);?>
				<?php if (file_exists($d_nfsconfdirty_path)):?><p>
				<?php print_info_box_np(gettext("The NFS export list has been changed.<br>You must apply the changes in order for them to take effect."));?><br>
				<input name="apply" type="submit" class="formbtn" id="apply" value="<?=gettext("Apply changes");?>"></p>
				<?php endif;?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<tr>
						<td colspan="2" valign="top" class="optsect_t">
							<table border="0" cellspacing="0" cellpadding="0" width="100%">
								<tr>
									<td class="optsect_s"><strong><?=gettext("NFS Server"); ?></strong></td>
									<td align="right" class="optsect_s">
										<input name="enable" type="checkbox" value="yes" <?php if ($pconfig['enable']) echo "checked"; ?> onClick="enable_change(false)"> <strong><?=gettext("Enable") ;?></strong>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gettext("Exports");?></td>
						<td width="78%" class="vtable">
					    <table width="100%" border="0" cellpadding="0" cellspacing="0">
					      <tr>
					        <td width="30%" class="listhdrr"><?=gettext("Path");?></td>
					        <td width="30%" class="listhdrr"><?=gettext("Network");?></td>
					        <td width="30%" class="listhdrr"><?=gettext("Comment");?></td>
					        <td width="10%" class="list"></td>
					      </tr>
							  <?php $i = 0; foreach ($a_share as $sharev):?>
					      <tr>
					        <td class="listr"><?=htmlspecialchars($sharev['path']);?>&nbsp;</td>
					        <td class="listr"><?=htmlspecialchars($sharev['network']);?>&nbsp;</td>
					        <td class="listr"><?=htmlspecialchars($sharev['comment']);?>&nbsp;</td>
					        <td valign="middle" nowrap class="list">
					          <a href="services_nfs_share_edit.php?id=<?=$i;?>"><img src="e.gif" title="<?=gettext("Edit share");?>" width="17" height="17" border="0"></a>
					          <a href="services_nfs.php?act=del&id=<?=$i;?>" onclick="return confirm('<?=gettext("Do you really want to delete this share?");?>')"><img src="x.gif" title="<?=gettext("Delete share");?>" width="17" height="17" border="0"></a>
					        </td>
					      </tr>
					      <?php $i++; endforeach;?>
					      <tr>
					        <td class="list" colspan="3"></td>
					        <td class="list"><a href="services_nfs_share_edit.php"><img src="plus.gif" title="<?=gettext("Add share");?>" width="17" height="17" border="0"></a></td>
					      </tr>
					    </table>
					  </td>
					</tr>
					<tr>
						<td width="22%" valign="top">&nbsp;</td>
						<td width="78%">
							<input name="Submit" type="submit" class="formbtn" value="<?=gettext("Save and Restart");?>" onClick="enable_change(true)">
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</form>
<?php include("fend.inc");?>
