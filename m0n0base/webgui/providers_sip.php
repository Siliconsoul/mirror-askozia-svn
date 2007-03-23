#!/usr/local/bin/php
<?php 
/*
	$Id$
	part of AskoziaPBX (http://askozia.com/pbx)
	
	Copyright (C) 2007 IKT <http://itison-ikt.de>.
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

$pgtitle = array("Providers", "SIP");
require("guiconfig.inc");

if (!is_array($config['providers']['sipprovider']))
	$config['providers']['sipprovider'] = array();

asterisk_sip_sort_providers();
$a_sipproviders = &$config['providers']['sipprovider'];

if ($_POST) {

	$pconfig = $_POST;

	if ($_POST['apply']) {
		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			// not quite sure what to do here...
			config_lock();
			$retval |= asterisk_sip_conf_generate();
			$retval |= asterisk_sip_reload();
			$retval |= asterisk_extensions_conf_generate();
			$retval |= asterisk_extensions_reload();
			config_unlock();
		}
		$savemsg = get_std_save_message($retval);
		if ($retval == 0) {
			if (file_exists($d_sipconfdirty_path))
				unlink($d_sipconfdirty_path);
		}
	}
}

if ($_GET['act'] == "del") {
	if ($a_sipproviders[$_GET['id']]) {
		unset($a_sipproviders[$_GET['id']]);
		write_config();
		touch($d_sipconfdirty_path);
		header("Location: providers_sip.php");
		exit;
	}
}

?>

<?php include("fbegin.inc"); ?>
<form action="providers_sip.php" method="post">
<?php if ($input_errors) print_input_errors($input_errors); ?>
<?php if (file_exists($d_sipconfdirty_path)): ?><p>
<?php print_info_box_np("The SIP providers list has been changed.<br>You must apply the changes in order for them to take effect.");?><br>
<input name="apply" type="submit" class="formbtn" id="apply" value="Apply changes"></p>
<?php endif; ?>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="15%" class="listhdrr">Prefix</td>
		<td width="35%" class="listhdrr">Username</td>
		<td width="40%" class="listhdr">Proxy</td>
		<td width="10%" class="list"></td>
	</tr>

	<?php $i = 0; foreach ($a_sipproviders as $sp): ?>
	<tr>
		<td class="listlr">
			<?=htmlspecialchars($sp['prefix']);?>
		</td>
		<td class="listr">
			<?=htmlspecialchars($sp['username']);?>
		</td>
		<td class="listbg">
			<?=htmlspecialchars($sp['host']);?>&nbsp;
		</td>
		<td valign="middle" nowrap class="list"> <a href="providers_sip_edit.php?id=<?=$i;?>"><img src="e.gif" title="edit SIP phone" width="17" height="17" border="0"></a>
           &nbsp;<a href="providers_sip.php?act=del&id=<?=$i;?>" onclick="return confirm('Do you really want to delete this SIP provider?"><img src="x.gif" title="delete SIP provider" width="17" height="17" border="0"></a></td>
	</tr>
	<?php $i++; endforeach; ?>

	<tr> 
		<td class="list" colspan="3"></td>
		<td class="list"> <a href="providers_sip_edit.php"><img src="plus.gif" title="add SIP provider" width="17" height="17" border="0"></a></td>
	</tr>
</table>
</form>
<?php include("fend.inc"); ?>
