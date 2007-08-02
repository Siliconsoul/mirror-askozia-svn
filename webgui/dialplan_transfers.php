#!/usr/local/bin/php
<?php 
/*
	$Id: services_voicemail.php 145 2007-07-05 14:40:16Z michael.iedema $
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

$pgtitle = array("Dialplan", "Transfers");
require("guiconfig.inc");

$parkingconfig = &$config['dialplan']['callparking'];
$featuremapconfig = &$config['dialplan']['featuremap'];

$pconfig['parkext'] = isset($parkingconfig['parkext']) ? $parkingconfig['parkext'] : "700";
$pconfig['parkposstart'] = isset($parkingconfig['parkposstart']) ? $parkingconfig['parkposstart'] : "701";
$pconfig['parkposend'] = isset($parkingconfig['parkposend']) ? $parkingconfig['parkposend'] : "720";
$pconfig['parktime'] = isset($parkingconfig['parktime']) ? $parkingconfig['parktime'] : "30";

$pconfig['attendedtransfer'] = isset($featuremapconfig['attendedtransfer']) ? $featuremapconfig['attendedtransfer'] : "**";
$pconfig['blindtransfer'] = isset($featuremapconfig['blindtransfer']) ? $featuremapconfig['blindtransfer'] : "##";
//$pconfig['disconnect'] = isset($featuremapconfig['disconnect']) ? $featuremapconfig['disconnect'] : "*0";


if ($_POST) {

	unset($input_errors);
	$pconfig = $_POST;

	/* input validation */
	$reqdfields = explode(" ", "parkext parkposstart parkposend parktime");
	$reqdfieldsn = explode(",", "Parking Extension,Parking Start Position,Parking Stop Position,Park Time");
	
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, &$input_errors);


	if ($_POST['parkposstart'] && !is_numericint($_POST['parkposstart'])) {
		$input_errors[] = "A valid parking start position must be specified.";
	}
	if ($_POST['parkposend'] && !is_numericint($_POST['parkposend'])) {
		$input_errors[] = "A valid parking stop position must be specified.";
	}
	if (!$input_errors && ($_POST['parkposend'] <= $_POST['parkposstart'])) {
		$input_errors[] = "The end parking position must be larger than the start position.";
	}
	if ($_POST['parkext'] && !is_numericint($_POST['parkext'])) {
		$input_errors[] = "A valid parking extension must be specified.";
	}
	if ($_POST['parktime'] && !is_numericint($_POST['parktime'])) {
		$input_errors[] = "A valid park time must be specified.";
	}

	if (!$input_errors) {
		$parkingconfig['parkext'] = ($_POST['parkext'] != "700") ? $_POST['parkext'] : false ;
		$parkingconfig['parkposstart'] = ($_POST['parkposstart'] != "701") ? $_POST['parkposstart'] : false ;
		$parkingconfig['parkposend'] = ($_POST['parkposend'] != "720") ? $_POST['parkposend'] : false ;
		$parkingconfig['parktime'] = ($_POST['parktime'] != "30") ? $_POST['parktime'] : false ;
		
		$featuremapconfig['attendedtransfer'] = ($_POST['attendedtransfer'] != "**") ? $_POST['attendedtransfer'] : false;
		$featuremapconfig['blindtransfer'] = ($_POST['blindtransfer'] != "##") ? $_POST['blindtransfer'] : false;
		//$featuremapconfig['disconnect'] = ($_POST['disconnect'] !=  "*0") ? $_POST['disconnect'] : false;

		write_config();
		touch($d_featuresconfdirty_path);
		header("Location: dialplan_transfers.php");
		exit;
	}
}

if (file_exists($d_featuresconfdirty_path)) {
	$retval = 0;
	if (!file_exists($d_sysrebootreqd_path)) {
		config_lock();
		$retval |= dialplan_features_conf_generate();
		config_unlock();
		
		$retval |= dialplan_features_reload();
		$savemsg = get_std_save_message($retval);
		if ($retval == 0) {
			unlink($d_featuresconfdirty_path);
		}
	}
}


?>
<?php include("fbegin.inc"); ?>
<?php if ($input_errors) print_input_errors($input_errors); ?>
<?php if ($savemsg) print_info_box($savemsg); ?>
<form action="dialplan_transfers.php" method="post" name="iform" id="iform">
	<table width="100%" border="0" cellpadding="6" cellspacing="0">
		<tr>
			<td colspan="2" class="listtopic">Hot Keys</td>
		</tr>
		<tr>
			<td width="20%" valign="top" class="vncellreq">Attended Transfer</td>
			<td width="80%" class="vtable">
				<input name="attendedtransfer" type="text" class="formfld" id="attendedtransfer" size="10" value="<?=htmlspecialchars($pconfig['attendedtransfer']);?>">
				<br><span class="vexpl">This key combination activates an attended transfer.</span>
			</td>
		</tr>
		<tr>
			<td valign="top" class="vncellreq">Blind Transfer</td>
			<td class="vtable">
				<input name="blindtransfer" type="text" class="formfld" id="blindtransfer" size="10" value="<?=htmlspecialchars($pconfig['blindtransfer']);?>">
				<br><span class="vexpl">This key combination activates a blind transfer.</span>
			</td>
		</tr><? /*
		<tr>
			<td valign="top" class="vncellreq">Disconnect</td>
			<td class="vtable">
				<input name="disconnect" type="text" class="formfld" id="disconnect" size="10" value="<?=htmlspecialchars($pconfig['disconnect']);?>">
				<br><span class="vexpl">This key combination disconnects a phone from a call.</span>
			</td>
		</tr>
		*/?><tr> 
			<td colspan="2" class="list" height="12">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2" class="listtopic">Call Parking</td>
		</tr>
		<tr>
			<td width="20%" valign="top" class="vncellreq">Park Extension</td>
			<td width="80%" class="vtable">
				<input name="parkext" type="text" class="formfld" id="parkext" size="20" value="<?=htmlspecialchars($pconfig['parkext']);?>">
				<br><span class="vexpl">Transfer to this extension to park a call.</span>
			</td>
		</tr>
		<tr>
			<td valign="top" class="vncellreq">Parking Range</td>
			<td class="vtable">
				<input name="parkposstart" type="text" class="formfld" id="parkposstart" size="10" value="<?=htmlspecialchars($pconfig['parkposstart']);?>">&nbsp;-&nbsp;<input name="parkposend" type="text" class="formfld" id="parkposend" size="10" value="<?=htmlspecialchars($pconfig['parkposend']);?>">
				<br><span class="vexpl">This range of extensions is where parked calls reside.</span>
			</td>
		</tr>
		<tr>
			<td width="20%" valign="top" class="vncellreq">Park Time</td>
			<td width="80%" class="vtable">
				<input name="parktime" type="text" class="formfld" id="parktime" size="20" value="<?=htmlspecialchars($pconfig['parktime']);?>">
				<br><span class="vexpl">Maximum number of seconds a call can be parked before it is transfered back to the parker.</span>
			</td>
		</tr>
		<tr> 
			<td valign="top">&nbsp;</td>
			<td>
				<input name="Submit" type="submit" class="formbtn" value="Save">
			</td>
		</tr>
	</table>
</form>
<?php include("fend.inc"); ?>
