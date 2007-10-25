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

require_once("functions.inc");

$needs_scriptaculous = true;

$pgtitle = array("Providers", "Analog", "Edit Line");
require("guiconfig.inc");

if (!is_array($config['analog']['provider']))
	$config['analog']['provider'] = array();

analog_sort_providers();
$a_analogproviders = &$config['analog']['provider'];

$a_analogphones = analog_get_phones();


$id = $_GET['id'];
if (isset($_POST['id']))
	$id = $_POST['id'];

/* pull current config into pconfig */
if (isset($id) && $a_analogproviders[$id]) {
	$pconfig['name'] = $a_analogproviders[$id]['name'];
	$pconfig['interface'] = $a_analogproviders[$id]['interface'];
	$pconfig['number'] = $a_analogproviders[$id]['number'];
	$pconfig['language'] = $a_analogproviders[$id]['language'];
	$pconfig['dialpattern'] = $a_analogproviders[$id]['dialpattern'];
	$pconfig['incomingextension'] = $a_analogproviders[$id]['incomingextension'];
	$pconfig['override'] = $a_analogproviders[$id]['override'];
}

if ($_POST) {

	unset($input_errors);
	$_POST['dialpattern'] = split_and_clean_patterns($_POST['dialpattern']);
	$pconfig = $_POST;

	/* input validation */
	$reqdfields = explode(" ", "name");
	$reqdfieldsn = explode(",", "Name");
	
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, &$input_errors);

	// pattern validation
	if (isset($id)) {
		$current_provider_id = $a_analogproviders[$id]['uniqid'];
	}
	if (is_array($_POST['dialpattern'])) {
		foreach($_POST['dialpattern'] as $p) {
			if (asterisk_dialpattern_exists($p, &$return_provider_name, $current_provider_id)) {
				$input_errors[] = "The dial-pattern \"$p\" already exists for \"$return_provider_name\".";
			}
			if (!asterisk_is_valid_dialpattern($p, &$internal_error)) {
				$input_errors[] = "The dial-pattern \"$p\" is invalid. $internal_error";
			}
		}
	}
	

	if (!$input_errors) {
		$ap = array();		
		$ap['name'] = $_POST['name'];
		$ap['interface'] = $_POST['interface'];
		$ap['number'] = $_POST['number'];
		$ap['language'] = $_POST['language'];
		
		$ap['dialpattern'] = $_POST['dialpattern'];
		$ap['incomingextension'] = $_POST['incomingextension'];
		$ap['override'] = $_POST['override'];
		
		if (isset($id) && $a_analogproviders[$id]) {
			$ap['uniqid'] = $a_analogproviders[$id]['uniqid'];
			$a_analogproviders[$id] = $ap;
		 } else {
			$ap['uniqid'] = "ANALOG-PROVIDER-" . uniqid(rand());
			$a_analogproviders[] = $ap;
		}
		
		touch($d_analogconfdirty_path);
		
		write_config();
		
		header("Location: providers_analog.php");
		exit;
	}
}
?>
<?php include("fbegin.inc"); ?>
<?php if ($input_errors) print_input_errors($input_errors); ?>
	<form action="providers_analog_edit.php" method="post" name="iform" id="iform">
		<table width="100%" border="0" cellpadding="6" cellspacing="0">
			<tr> 
				<td width="20%" valign="top" class="vncellreq">Name</td>
				<td width="80%" colspan="1" class="vtable">
					<input name="name" type="text" class="formfld" id="name" size="40" value="<?=htmlspecialchars($pconfig['name']);?>"> 
					<br><span class="vexpl">Descriptive name of this provider.</span>
				</td>
			</tr>
			<tr> 
				<td width="20%" valign="top" class="vncellreq">Number</td>
				<td width="80%" colspan="1" class="vtable">
					<input name="number" type="text" class="formfld" id="number" size="40" value="<?=htmlspecialchars($pconfig['number']);?>"> 
					<br><span class="vexpl">Telephone number assigned to this line.</span>
				</td>
			</tr>
			<? display_provider_dialpattern_editor($pconfig['dialpattern'], 1); ?>
			<tr> 
				<td valign="top" class="vncell">Analog Interface</td>
				<td class="vtable">
					<select name="interface" class="formfld" id="interface"><?

					$ab_interfaces = analog_get_ab_interfaces();
					foreach ($ab_interfaces as $interface) {
						if ($interface['type'] != "fxo") {
							continue;
						}
						?><option value="<?=$interface['unit'];?>" <?
						if ($interface['unit'] == $pconfig['interface'])
							echo "selected"; ?>
						><?=$interface['name'];?></option><?
					}

					?></select>
				</td>
			</tr>
			<? display_channel_language_selector($pconfig['language'], 1); ?>
			<? display_incoming_extension_selector($pconfig['incomingextension'], 1); ?>
			<? display_incoming_callerid_override_options($pconfig['override'], 1); ?>
			<tr> 
				<td valign="top">&nbsp;</td>
				<td>
					<input name="Submit" type="submit" class="formbtn" value="Save">
					<?php if (isset($id) && $a_analogproviders[$id]): ?>
					<input name="id" type="hidden" value="<?=$id;?>"> 
					<?php endif; ?>
				</td>
			</tr>
		</table>
	</form>
<?php include("fend.inc"); ?>
