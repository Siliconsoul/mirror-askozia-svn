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

$dev = $_GET['dev'];
if (isset($_POST['dev']))
	$dev = $_POST['dev'];

$pgtitle = array("Interfaces", "Storage", "Edit Disk");
require("guiconfig.inc");

$recognized_disk = storage_get_recognized_disk($dev);
$configured_disk = storage_get_configured_disk($dev);

/* merge recognized and configured info on a different level */
/* references needed to modify disk, or finally move over to functions for updates */

/* pull current config into pconfig */
$pconfig['name'] = $recognized_disk['name'];
$pconfig['bytes'] = $recognized_disk['bytes'];


if ($_POST) {

	unset($input_errors);
	$pconfig = $_POST;
	
	if (!$input_errors) {
		/*
		$n = count($a_isdninterfaces);
		if (isset($configured_units[$unit])) {
			for ($i = 0; $i < $n; $i++) {
				if ($a_isdninterfaces[$i]['unit'] == $unit) {
					$a_isdninterfaces[$i]['name'] = $_POST['name'];
					$a_isdninterfaces[$i]['mode'] = $_POST['mode'];
					$a_isdninterfaces[$i]['echosquelch'] = $_POST['echosquelch'];
					$a_isdninterfaces[$i]['echocancel'] = $_POST['echocancel'];
					$a_isdninterfaces[$i]['pcmmaster'] = $_POST['pcmmaster'];
					$a_isdninterfaces[$i]['nopwrsave'] = $_POST['nopwrsave'];
					$a_isdninterfaces[$i]['pollmode'] = $_POST['pollmode'];
				}
			}

		} else {
			$a_isdninterfaces[$n]['unit'] = $unit;
			$a_isdninterfaces[$n]['name'] = $_POST['name'];
			$a_isdninterfaces[$n]['mode'] = $_POST['mode'];
			$a_isdninterfaces[$n]['echosquelch'] = $_POST['echosquelch'];
			$a_isdninterfaces[$n]['echocancel'] = $_POST['echocancel'];
			$a_isdninterfaces[$n]['pcmmaster'] = $_POST['pcmmaster'];
			$a_isdninterfaces[$n]['nopwrsave'] = $_POST['nopwrsave'];
			$a_isdninterfaces[$n]['pollmode'] = $_POST['pollmode'];
		}


		touch($d_isdnconfdirty_path);

		write_config();
		*/
		header("Location: interfaces_storage.php");
		exit;
	}
}
?>
<?php include("fbegin.inc"); ?>
<?php if ($input_errors) print_input_errors($input_errors); ?>
<form action="interfaces_storage_disk_edit.php" method="post" name="iform" id="iform">
<table width="100%" border="0" cellpadding="6" cellspacing="0">
	<tr> 
		<td valign="top" class="vncellreq">Name</td>
		<td class="vtable">
			<input name="name" type="text" class="formfld" id="name" size="40" value="<?=htmlspecialchars($pconfig['name']);?>"> 
			<br><span class="vexpl">descriptive name</span>
		</td>
	</tr>
	<tr> 
		<td valign="top" class="vncell">Mode</td>
		<td class="vtable">
			<select name="mode" class="formfld" id="mode">
			<? foreach ($isdn_dchannel_modes as $mode => $friendly) : ?>
			<option value="<?=$mode;?>" <?
			if ($mode == $pconfig['mode'])
				echo "selected"; ?>
			><?=$friendly;?></option>
			<? endforeach; ?>
			</select>
			<br><span class="vexpl">Interface Operation Mode</span>
		</td>
	</tr>
	<tr> 
		<td valign="top" class="vncell">Echo Canceller</td>
		<td class="vtable">
			<input name="echocancel" id="echocancel" type="checkbox" value="yes" <? if ($pconfig['echocancel']) echo "checked"; ?>>
			Enable echo cancellation.
		</td>
	</tr>
	<tr> 
		<td valign="top" class="vncell">Echo Squelch</td>
		<td class="vtable">
			<input name="echosquelch" id="echosquelch" type="checkbox" value="yes" <? if ($pconfig['echosquelch']) echo "checked"; ?>>
			Enable echo squelch. (primitive echo suppression)
		</td>
	</tr>
	<tr> 
		<td valign="top" class="vncell">PCM Timing Master</td>
		<td class="vtable">
			<input name="pcmmaster" id="pcmmaster" type="checkbox" value="yes" <? if ($pconfig['pcmmaster']) echo "checked"; ?>>
			This card provides the timing source (needed if this is the only card).
		</td>
	</tr>
	<tr> 
		<td valign="top" class="vncell">Disable Power Save</td>
		<td class="vtable">
			<input name="nopwrsave" id="nopwrsave" type="checkbox" value="yes" <? if ($pconfig['nopwrsave']) echo "checked"; ?>>
			Disable power save mode.
		</td>
	</tr>
	<tr> 
		<td valign="top" class="vncell">Enable Polling Mode</td>
		<td class="vtable">
			<input name="pollmode" id="pollmode" type="checkbox" value="yes" <? if ($pconfig['pollmode']) echo "checked"; ?>>
			Enable polling mode.
		</td>
	</tr>
	<tr> 
		<td valign="top">&nbsp;</td>
		<td>
			<input name="Submit" type="submit" class="formbtn" value="Save">
			<input name="unit" type="hidden" value="<?=$unit;?>"> 
		</td>
	</tr>
</table>
</form>
<?php include("fend.inc"); ?>
