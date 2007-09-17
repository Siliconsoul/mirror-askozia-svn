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

$unit = $_GET['unit'];
if (isset($_POST['unit']))
	$unit = $_POST['unit'];
	
$type = $_GET['type'];
if (isset($_POST['type']))
	$type = $_POST['type'];

$pgtitle = array("Interfaces", "Edit Analog ". strtoupper($type) ." Interface #$unit");
require("guiconfig.inc");


if (!is_array($config['interfaces']['ab-unit']))
	$config['interfaces']['ab-unit'] = array();

analog_sort_ab_interfaces();
$a_abinterfaces = &$config['interfaces']['ab-unit'];

$configured_units = array();
foreach ($a_abinterfaces as $interface) {
	$configured_units[$interface['unit']]['name'] = $interface['name'];
	$configured_units[$interface['unit']]['type'] = $interface['type'];
}

$recognized_units = analog_get_recognized_ab_unit_numbers();
if (!count($recognized_units)) {
	$n = 0;
} else {
	$n = max(array_keys($recognized_units));
	$n = ($n == 0) ? 1 : $n;
}
$merged_units = array();
for ($i = 0; $i <= $n; $i++) {
	if (!isset($recognized_units[$i])) {
		continue;
	}
	if (isset($configured_units[$i])) {
		$merged_units[$i]['unit'] = $i;
		$merged_units[$i]['name'] = $configured_units[$i]['name'];
		$merged_units[$i]['type'] = $configured_units[$i]['type'];
	} else {
		$merged_units[$i]['unit'] = $i;
		$merged_units[$i]['name'] = "(unconfigured)";
		$merged_units[$i]['type'] = $recognized_units[$i];
	}
}

/* pull current config into pconfig */
$pconfig['unit'] = $merged_units[$unit]['unit'];
$pconfig['name'] = $merged_units[$unit]['name'];
$pconfig['type'] = $merged_units[$unit]['type'];


if ($_POST) {

	unset($input_errors);
	$pconfig = $_POST;
	
	if (!$input_errors) {
		
		$n = count($a_abinterfaces);
		if (isset($configured_units[$unit])) {
			for ($i = 0; $i < $n; $i++) {
				if ($a_abinterfaces[$i]['unit'] == $unit) {
					$a_abinterfaces[$i]['name'] = $_POST['name'];
					$a_abinterfaces[$i]['type'] = $_POST['type'];
				}
			}

		} else {
			$a_abinterfaces[$n]['unit'] = $unit;
			$a_abinterfaces[$n]['name'] = $_POST['name'];
			$a_abinterfaces[$n]['type'] = $_POST['type'];
		}


		touch($d_analogconfdirty_path);

		write_config();

		header("Location: interfaces_analog.php");
		exit;
	}
}
?>
<?php include("fbegin.inc"); ?>
<?php if ($input_errors) print_input_errors($input_errors); ?>
<form action="interfaces_analog_edit.php" method="post" name="iform" id="iform">
<table width="100%" border="0" cellpadding="6" cellspacing="0">
	<tr> 
		<td width="20%" valign="top" class="vncellreq">Name</td>
		<td width="80%" class="vtable">
			<input name="name" type="text" class="formfld" id="name" size="40" value="<?=htmlspecialchars($pconfig['name']);?>"> 
			<br><span class="vexpl">descriptive name</span>
		</td>
	</tr><? /*
	<tr> 
		<td valign="top" class="vncell">Echo Canceller</td>
		<td class="vtable">
			<input name="echocancel" id="echocancel" type="checkbox" value="yes" <? if ($pconfig['echocancel']) echo "checked"; ?>>
			Enable echo cancellation.
		</td>
	</tr>
	
	*/ ?>
	<tr> 
		<td valign="top">&nbsp;</td>
		<td>
			<input name="Submit" type="submit" class="formbtn" value="Save">
			<input name="unit" type="hidden" value="<?=$unit;?>">
			<input name="type" type="hidden" value="<?=$type;?>">
		</td>
	</tr>
	<tr> 
		<td valign="top">&nbsp;</td>
		<td>
			<span class="vexpl"><span class="red"><strong>Warning:<br>
			</strong></span>clicking &quot;Save&quot; will drop all current
			calls.</span>
		</td>
	</tr>
</table>
</form>
<?php include("fend.inc"); ?>
