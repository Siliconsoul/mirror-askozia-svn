#!/usr/local/bin/php
<?php 
/*
	$Id$
	part of AskoziaPBX (http://askozia.com/pbx)
	
	Copyright (C) 2007-2008 IKT <http://itison-ikt.de>.
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

$pgtitle = array(gettext("Accounts"), gettext("Providers"));
$pghelp = gettext("Provider Accounts allow you to configure routes to and from external sources (i.e. VoIP service providers or attached ISDN / Analog interface hardware). Click an account type below to get started.");
$pglegend = array("add", "enabled", "disabled", "edit", "delete");

/* delete */
if ($_GET['action'] == "delete") {
	if(!($msg = pbx_delete_provider($_GET['id']))) {
		$successful_action = true;
	} else {
		$savemsg = $msg;	
	}
}

/* disable */
if ($_GET['action'] == "disable") {
	if(!($msg = pbx_disable_provider($_GET['id']))) {
		$successful_action = true;
	} else {
		$savemsg = $msg;	
	}
}

/* enable */
if ($_GET['action'] == "enable") {
	if(!($msg = pbx_enable_provider($_GET['id']))) {
		$successful_action = true;
	} else {
		$savemsg = $msg;	
	}
}

/* handle successful action */
if ($successful_action) {
	write_config();
	$pieces = explode("-", $_GET['id']);
	$provider_type = strtolower($pieces[0]);
	switch ($provider_type) {
		case "analog":
			touch($d_analogconfdirty_path);
			break;
		case "external":
			touch($d_extensionsconfdirty_path);
			break;
		case "iax":
			touch($d_iaxconfdirty_path);
			break;
		case "sip":
			touch($d_sipconfdirty_path);
			break;
		case "isdn":
			touch($d_isdnconfdirty_path);	
			break;
	}
	header("Location: accounts_providers.php");
	exit;	
}

/* dirty sip config? */
if (file_exists($d_sipconfdirty_path)) {
	$retval = 0;
	if (!file_exists($d_sysrebootreqd_path)) {
		config_lock();
		$retval |= sip_conf_generate();
		$retval |= extensions_conf_generate();
		config_unlock();
		
		$retval |= sip_reload();
		$retval |= extensions_reload();
	}
	
	$savemsg = get_std_save_message($retval);
	if ($retval == 0) {
		unlink($d_sipconfdirty_path);
	}
}

/* dirty iax config? */
if (file_exists($d_iaxconfdirty_path)) {
	$retval = 0;
	if (!file_exists($d_sysrebootreqd_path)) {
		config_lock();
		$retval |= iax_conf_generate();
		$retval |= extensions_conf_generate();
		config_unlock();
		
		$retval |= iax_reload();
		$retval |= extensions_reload();
	}

	$savemsg = get_std_save_message($retval);
	if ($retval == 0) {
		unlink($d_iaxconfdirty_path);
	}
}

/* dirty isdn config? */
if (file_exists($d_isdnconfdirty_path)) {
	$retval = 0;
	if (!file_exists($d_sysrebootreqd_path)) {
		config_lock();
		$retval |= isdn_conf_generate();
		$retval |= extensions_conf_generate();
		config_unlock();
		
		$retval |= isdn_reload();
		$retval |= extensions_reload();
	}

	$savemsg = get_std_save_message($retval);
	if ($retval == 0) {
		unlink($d_isdnconfdirty_path);
	}
}

/* dirty analog config? */
if (file_exists($d_analogconfdirty_path)) {
	$retval = 0;
	if (!file_exists($d_sysrebootreqd_path)) {
		config_lock();
		$retval |= analog_zapata_conf_generate();
		$retval |= extensions_conf_generate();
		config_unlock();
		
		$retval |= analog_reload();
		$retval |= extensions_reload();
	}

	$savemsg = get_std_save_message($retval);
	if ($retval == 0) {
		unlink($d_analogconfdirty_path);
	}
}
?>

<? include("fbegin.inc"); ?>
<script type="text/javascript" charset="utf-8">

	<?=javascript_account_statuses("functions");?>

	jQuery(document).ready(function(){
	
		<?=javascript_account_statuses("ready");?>
	
	});

</script>
<form action="accounts_providers.php" method="post">
<? if ($savemsg) display_info_box($savemsg); ?>
<? $status_info = pbx_get_peer_statuses(); ?>

<table border="0" cellspacing="0" cellpadding="6" width="100%">
	<tr>
		<td class="listhdradd"><img src="add.png">&nbsp;&nbsp;&nbsp;<?
		if (!isset($config['system']['webgui']['hidesip'])) {
			?><a href="providers_sip_edit.php"><?=gettext("SIP");?></a><img src="bullet_add.png">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?
		}
		if (!isset($config['system']['webgui']['hideiax'])) {
			?><a href="providers_iax_edit.php"><?=gettext("IAX");?></a><img src="bullet_add.png">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?
		}
		if (!isset($config['system']['webgui']['hideisdn'])) {
			?><a href="providers_isdn_edit.php"><?=gettext("ISDN");?></a><img src="bullet_add.png">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?
		}
		if (!isset($config['system']['webgui']['hideanalog'])) {
			?><a href="providers_analog_edit.php"><?=gettext("Analog");?></a><img src="bullet_add.png"><?
		}
		?></td>
	</tr>
	<tr> 
		<td class="list" height="12">&nbsp;</td>
	</tr>
</table>

<? if ($sip_providers = sip_get_providers()) : ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="5%" class="list"></td>
		<td colspan="5" class="listtopiclight"><?=gettext("SIP");?></td>
	</tr>
	<tr>
		<td width="5%" class="list"></td>
		<td width="25%" class="listhdrr"><?=gettext("Name");?></td>
		<td width="20%" class="listhdrr"><?=gettext("Pattern(s)");?></td>
		<td width="20%" class="listhdrr"><?=gettext("Username");?></td>
		<td width="20%" class="listhdr"><?=gettext("Host");?></td>
		<td width="10%" class="list"></td>
	</tr>

	<? $i = 0; foreach ($sip_providers as $p): ?>
	<tr>
		<td valign="middle" nowrap class="list"><?
		if (isset($p['disabled'])) {
			?><a href="?action=enable&id=<?=$p['uniqid'];?>"><img src="disabled.png" title="<?=gettext("click to enable provider");?>" border="0"></a><?
		} else {
			?><a href="?action=disable&id=<?=$p['uniqid'];?>" onclick="return confirm('<?=gettext("Do you really want to disable this provider?");?>')"><img src="enabled.png" title="<?=gettext("click to disable provider");?>" border="0"></a><?
		}
		?></td>
		<td class="listbgl"><?
		if (!isset($p['disabled'])) {
			echo display_peer_status_icon($status_info[$p['uniqid']], $p['uniqid']);
			echo htmlspecialchars($p['name']);
		} else {
			?><span class="gray"><?=htmlspecialchars($p['name']);?></span><?
		}
		?></td>
		<td class="listr"><?=@implode("<br>", $p['dialpattern']);?>&nbsp;</td>
		<td class="listr"><?=htmlspecialchars($p['username']);?></td>
		<td class="listr"><?=htmlspecialchars($p['host']);?>&nbsp;</td>
		<td valign="middle" nowrap class="list"><a href="providers_sip_edit.php?id=<?=$i;?>"><img src="edit.png" title="<?=gettext("edit provider");?>" border="0"></a>
			<a href="?action=delete&id=<?=$p['uniqid'];?>" onclick="return confirm('<?=gettext("Do you really want to delete this provider?");?>')"><img src="delete.png" title="<?=gettext("delete provider");?>" border="0"></a></td>
	</tr>
	<? $i++; endforeach; ?>

	<tr> 
		<td class="list" colspan="6" height="12">&nbsp;</td>
	</tr>
</table>
<? endif; ?>

<? if ($iax_providers = iax_get_providers()) : ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="5%" class="list"></td>
		<td colspan="5" class="listtopiclight"><?=gettext("IAX");?></td>
	</tr>
	<tr>
		<td width="5%" class="list"></td>
		<td width="25%" class="listhdrr"><?=gettext("Name");?></td>
		<td width="20%" class="listhdrr"><?=gettext("Pattern(s)");?></td>
		<td width="20%" class="listhdrr"><?=gettext("Username");?></td>
		<td width="20%" class="listhdr"><?=gettext("Host");?></td>
		<td width="10%" class="list"></td>
	</tr>

	<? $i = 0; foreach ($iax_providers as $p): ?>
	<tr>
		<td valign="middle" nowrap class="list"><?
		if (isset($p['disabled'])) {
			?><a href="?action=enable&id=<?=$p['uniqid'];?>"><img src="disabled.png" title="<?=gettext("click to enable provider");?>" border="0"></a><?
		} else {
			?><a href="?action=disable&id=<?=$p['uniqid'];?>" onclick="return confirm('<?=gettext("Do you really want to disable this provider?");?>')"><img src="enabled.png" title="<?=gettext("click to disable provider");?>" border="0"></a><?
		}
		?></td>
		<td class="listbgl"><?
		if (!isset($p['disabled'])) {
			echo display_peer_status_icon($status_info[$p['uniqid']], $p['uniqid']);
			echo htmlspecialchars($p['name']);
		} else {
			?><span class="gray"><?=htmlspecialchars($p['name']);?></span><?
		}
		?></td>
		<td class="listr"><?=@implode("<br>", $p['dialpattern']);?>&nbsp;</td>
		<td class="listr"><?=htmlspecialchars($p['username']);?></td>
		<td class="listr"><?=htmlspecialchars($p['host']);?>&nbsp;</td>
		<td valign="middle" nowrap class="list"><a href="providers_iax_edit.php?id=<?=$i;?>"><img src="edit.png" title="<?=gettext("edit provider");?>" border="0"></a>
			<a href="?action=delete&id=<?=$p['uniqid'];?>" onclick="return confirm('<?=gettext("Do you really want to delete this provider?");?>')"><img src="delete.png" title="<?=gettext("delete provider");?>" border="0"></a></td>
	</tr>
	<? $i++; endforeach; ?>

	<tr> 
		<td class="list" colspan="6" height="12">&nbsp;</td>
	</tr>
</table>
<? endif; ?>

<? if ($isdn_providers = isdn_get_providers()) : ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="5%" class="list"></td>
		<td colspan="5" class="listtopiclight"><?=gettext("ISDN");?></td>
	</tr>
	<tr>
		<td width="5%" class="list"></td>
		<td width="25%" class="listhdrr"><?=gettext("Name");?></td>
		<td width="20%" class="listhdrr"><?=gettext("Pattern(s)");?></td>
		<td width="20%" class="listhdrr"><?=gettext("Number");?></td>
		<td width="20%" class="listhdr"><?=gettext("Interface");?></td>
		<td width="10%" class="list"></td>
	</tr>

	<? $i = 0; foreach ($isdn_providers as $p): ?>
	<? $interface = isdn_get_interface($p['interface']); ?>
	<tr>
		<td valign="middle" nowrap class="list"><?
		if (isset($p['disabled'])) {
			?><a href="?action=enable&id=<?=$p['uniqid'];?>"><img src="disabled.png" title="<?=gettext("click to enable provider");?>" border="0"></a><?
		} else {
			?><a href="?action=disable&id=<?=$p['uniqid'];?>" onclick="return confirm('<?=gettext("Do you really want to disable this provider?");?>')"><img src="enabled.png" title="<?=gettext("click to disable provider");?>" border="0"></a><?
		}
		?></td>
		<td class="listbgl"><?
		if (!isset($p['disabled'])) {
			echo display_peer_status_icon($status_info[$p['uniqid']], $p['uniqid']);
			echo htmlspecialchars($p['name']);
		} else {
			?><span class="gray"><?=htmlspecialchars($p['name']);?></span><?
		}
		?></td>
		<td class="listr"><?=@implode("<br>", $p['dialpattern']);?>&nbsp;</td>
		<td class="listr"><?=htmlspecialchars($p['msn']);?></td>
		<td class="listr"><?=htmlspecialchars($interface['name']);?></td>
		<td valign="middle" nowrap class="list"><a href="providers_isdn_edit.php?id=<?=$i;?>"><img src="edit.png" title="<?=gettext("edit provider");?>" border="0"></a>
			<a href="?action=delete&id=<?=$p['uniqid'];?>" onclick="return confirm('<?=gettext("Do you really want to delete this provider?");?>')"><img src="delete.png" title="<?=gettext("delete provider");?>" border="0"></a></td>
	</tr>
	<? $i++; endforeach; ?>

	<tr> 
		<td class="list" colspan="6" height="12">&nbsp;</td>
	</tr>
</table>
<? endif; ?>

<? if ($analog_providers = analog_get_providers()) : ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="5%" class="list"></td>
		<td colspan="5" class="listtopiclight"><?=gettext("Analog");?></td>
	</tr>
	<tr>
		<td width="5%" class="list"></td>
		<td width="25%" class="listhdrr"><?=gettext("Name");?></td>
		<td width="20%" class="listhdrr"><?=gettext("Pattern(s)");?></td>
		<td width="20%" class="listhdrr"><?=gettext("Number");?></td>
		<td width="20%" class="listhdr"><?=gettext("Interface");?></td>
		<td width="10%" class="list"></td>
	</tr>

	<? $i = 0; foreach ($analog_providers as $p): ?>
	<? $interface = analog_get_ab_interface($p['interface']); ?>
	<tr>
		<td valign="middle" nowrap class="list"><?
		if (isset($p['disabled'])) {
			?><a href="?action=enable&id=<?=$p['uniqid'];?>"><img src="disabled.png" title="<?=gettext("click to enable provider");?>" border="0"></a><?
		} else {
			?><a href="?action=disable&id=<?=$p['uniqid'];?>" onclick="return confirm('<?=gettext("Do you really want to disable this provider?");?>')"><img src="enabled.png" title="<?=gettext("click to disable provider");?>" border="0"></a><?
		}
		?></td>
		<td class="listbgl"><?
		if (!isset($p['disabled'])) {
			echo display_peer_status_icon($status_info[$p['uniqid']], $p['uniqid']);
			echo htmlspecialchars($p['name']);
		} else {
			?><span class="gray"><?=htmlspecialchars($p['name']);?></span><?
		}
		?></td>
		<td class="listr"><?=@implode("<br>", $p['dialpattern']);?>&nbsp;</td>
		<td class="listr"><?=htmlspecialchars($p['number']);?></td>
		<td class="listr"><?=htmlspecialchars($interface['name']);?></td>
		<td valign="middle" nowrap class="list"><a href="providers_analog_edit.php?id=<?=$i;?>"><img src="edit.png" title="<?=gettext("edit provider");?>" border="0"></a>
			<a href="?action=delete&id=<?=$p['uniqid'];?>" onclick="return confirm('<?=gettext("Do you really want to delete this provider?");?>')"><img src="delete.png" title="<?=gettext("delete provider");?>" border="0"></a></td>
	</tr>
	<? $i++; endforeach; ?>

	<tr> 
		<td class="list" colspan="6" height="12">&nbsp;</td>
	</tr>
</table>
<? endif; ?>

</form>
<? include("fend.inc"); ?>
