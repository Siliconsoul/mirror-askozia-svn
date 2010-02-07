#!/usr/bin/php
<?php 
/*
	$Id$
	part of AskoziaPBX (http://askozia.com/pbx)

	Copyright (C) 2010 IKT <http://itison-ikt.de>.
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright notice,
	   this list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
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
$pgtitle = array(gettext("Ports"), gettext("Edit Redfone Gateway"));

if ($_POST['connect']) {
	// 1. generate redfone.conf
	redfone_generate_confs();
	// 2. query
	$query = redfone_fonulator_query();
	if ($query) {
		
	}

} else if ($_POST) {
	unset($input_errors);

	$gw = redfone_verify_gateway(&$_POST, &$input_errors);
	if (!$input_errors) {
		redfone_save_gateway($gw);
		header("Location: ports_redfone.php");
		exit;
	}
}


$colspan = 1;
$carryovers[] = "uniqid";


$uniqid = $_GET['uniqid'];
if (isset($_POST['uniqid'])) {
	$uniqid = $_POST['uniqid'];
}

$initialconnect = false;
if ($_POST) {
	$form = $_POST;
} else if ($uniqid) {
	$form = redfone_get_gateway($uniqid);
	$initialconnect = true;
} else {
	$form = redfone_generate_default_gateway();
}

$networkinterfaces = network_get_interfaces();
$spantypes = array("E1", "T1");
$framingtypes = array("cas", "ccs", "sf", "esf");
$encodingtypes = array("ami", "b8zs", "hdb3");

include("fbegin.inc");
d_start("ports_redfone_edit.php");

if (count($networkinterfaces) < 2) {

	?><tr>
		<td><strong><?=gettext("An unused network interface is needed to communicate with the gateway!");?></strong></td>
	</tr>
</table>
</form><?

} else {

	// General
	d_header(gettext("General Settings"));
	?><tr>
		<td width="20%" valign="top" class="vncellreq"><?=gettext("Name");?></td>
		<td width="80%" class="vtable">
			<input name="gwname" type="text" class="formfld" id="gwname" size="40" value="<?=htmlspecialchars($form['gwname']);?>">
			<br><span class="vexpl"><?=gettext("Enter a descriptive name for this gateway.");?></span>
		</td>
	</tr>
	<tr>
		<td valign="top" class="vncell"><?=gettext("Local Interface");?></td>
		<td class="vtable">
			<select name="localif" class="formfld" id="localif"><?
			foreach ($networkinterfaces as $ifname => $ifinfo) {
				if ($ifname == $config['interfaces']['lan']['if']) {
					continue;
				}
				?><option value="<?=$ifname;?>" <? if ($ifname == $form['localif']) echo "selected";?>> <?
				echo htmlspecialchars($ifname . " (" . $ifinfo['mac'] . ")"); ?></option><?
			}
			?></select>
			<br><span class="vexpl"><?=spanify(gettext("Select which ethernet port on this system the gateway is connected to."));?></span>
		</td>
	</tr>
	<tr>
		<td valign="top" class="vncell"><?=gettext("Remote Interface");?></td>
		<td class="vtable">
			<select name="remoteif" class="formfld" id="remoteif">
				<option value="1" <? if ($form['remoteif'] == 1) echo "selected";?>>FB1</option>
				<option value="2" <? if ($form['remoteif'] == 1) echo "selected";?>>FB2</option>
			</select>
			<br><span class="vexpl"><?=spanify(gettext("Select which ethernet port on the gateway this system is connected to."));?></span>
		</td>
	</tr><?
	d_field(gettext("Remote MAC"), "mac", 20,
		gettext("The MAC address of the foneBRIDGE2 network interface."), "required");
	d_field(gettext("Remote IP"), "ip", 20,
			gettext("The IP address of the foneBRIDGE2 network interface."), "required");

	if (!$initialconnect) {
		?><tr>
			<td valign="top">&nbsp;</td>
			<td colspan="<?=$colspan;?>">
				<input name="connect" id="connect" type="submit" class="formbtn" value="<?=gettext("Connect");?>...">
			</td>
		</tr>
		</table>
		</form><?

	} else {
		d_label(gettext("Span Count"), "spancount");
		d_label(gettext("Firmware Version"), "firmwareversion");
		d_label(gettext("Hardware Version"), "hardwareversion");
		d_spacer();

		for ($i = 1; $i < $form['spancount'] + 1; $i++) {
			d_header(gettext("Span") . " #" . $i);
			?><tr>
				<td width="20%" valign="top" class="vncellreq"><?=gettext("Name");?></td>
				<td width="80%" class="vtable">
					<input name="span<?=$i;?>name" type="text" class="formfld" id="span<?=$i;?>name" size="40" value="<?=htmlspecialchars($form['span' . $i . 'name']);?>">
					<br><span class="vexpl"><?=gettext("Enter a descriptive name for this span.");?></span>
				</td>
			</tr>
			<tr>
				<td width="20%" valign="top" class="vncell"><?=gettext("Type");?></td>
				<td width="80%" class="vtable">
					<select name="span<?=$i;?>type" class="formfld" id="span<?=$i;?>type"><?
						foreach ($spantypes as $spantype) {
							?><option value="<?=$spantype;?>" <? if ($spantype == $form['span' . $i . 'type']) echo "selected";?>><?=$spantype;?></option><?
						}
					?></select>
				</td>
			</tr>
			<tr>
				<td width="20%" valign="top" class="vncell"><?=gettext("Framing");?></td>
				<td width="80%" class="vtable">
					<select name="span<?=$i;?>framing" class="formfld" id="span<?=$i;?>framing"><?
						foreach ($framingtypes as $framingtype) {
							?><option value="<?=$framingtype;?>" <? if ($framingtype == $form['span' . $i . 'framing']) echo "selected";?>><?=$framingtype;?></option><?
						}
					?></select>
				</td>
			</tr>
			<tr>
				<td width="20%" valign="top" class="vncell"><?=gettext("Encoding");?></td>
				<td width="80%" class="vtable">
					<select name="span<?=$i;?>encoding" class="formfld" id="span<?=$i;?>encoding"><?
						foreach ($encodingtypes as $encodingtype) {
							?><option value="<?=$encodingtype;?>" <? if ($encodingtype == $form['span' . $i . 'encoding']) echo "selected";?>><?=$encodingtype;?></option><?
						}
					?></select>
				</td>
			</tr>
			<tr>
				<td width="20%" valign="top" class="vncell"><?=gettext("Options");?></td>
				<td width="80%" class="vtable">
					<input name="span<?=$i;?>slaveormaster" type="radio" value="master" <?
						if ($form['span' . $i . 'slaveormaster'] == "master") echo "checked"; ?>><?=gettext("Master");?>
					&nbsp;&nbsp;
					<input name="span<?=$i;?>slaveormaster" type="radio" value="slave" <?
						if ($form['span' . $i . 'slaveormaster'] == "slave") echo "checked"; ?>><?=gettext("Slave");?>
					<br>
					<input name="span<?=$i;?>needscrc4" type="checkbox" id="span<?=$i;?>needscrc4" value="yes" <?
						if (isset($form['span' . $i . 'needscrc4'])) echo "checked"; ?>> <?=gettext("Span needs CRC4");?>
				</td>
			</tr><?
			d_spacer();
		}
		d_submit();
	}
}

include("fend.inc");
