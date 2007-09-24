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

$pgtitle = array("Interfaces", "ISDN");
require("guiconfig.inc");


if (!is_array($config['interfaces']['isdn-unit']))
	$config['interfaces']['isdn-unit'] = array();

isdn_sort_interfaces();
$a_isdninterfaces = &$config['interfaces']['isdn-unit'];

$configured_units = array();
foreach ($a_isdninterfaces as $interface) {
	$configured_units[$interface['unit']]['name'] = $interface['name'];
	$configured_units[$interface['unit']]['mode'] = $interface['mode'];
	$configured_units[$interface['unit']]['echosquelch'] = $interface['echosquelch'];
	$configured_units[$interface['unit']]['echocancel'] = $interface['echocancel'];
	$configured_units[$interface['unit']]['pcmmaster'] = $interface['pcmmaster'];
	$configured_units[$interface['unit']]['nopwrsave'] = $interface['nopwrsave'];
	$configured_units[$interface['unit']]['pollmode'] = $interface['pollmode'];
}

$recognized_units = isdn_get_recognized_unit_numbers();
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
		$merged_units[$i]['mode'] = $configured_units[$i]['mode'];
		$merged_units[$i]['echosquelch'] = $configured_units[$i]['echosquelch'];
		$merged_units[$i]['echocancel'] = $configured_units[$i]['echocancel'];
		$merged_units[$i]['pcmmaster'] = $configured_units[$i]['pcmmaster'];
		$merged_units[$i]['nopwrsave'] = $configured_units[$i]['nopwrsave'];
		$merged_units[$i]['pollmode'] = $configured_units[$i]['pollmode'];
	} else {
		$merged_units[$i]['unit'] = $i;
		$merged_units[$i]['name'] = "(unconfigured)";
	}
}


if (file_exists($d_isdnconfdirty_path)) {
	$retval = 0;
	if (!file_exists($d_sysrebootreqd_path)) {
		config_lock();
		$retval |= isdn_configure();
		$retval |= isdn_conf_generate();
		config_unlock();
		
		$retval |= isdn_reload();
	}
	
	$savemsg = get_std_save_message($retval);
	if ($retval == 0) {
		unlink($d_isdnconfdirty_path);
	}
}

?>

<?php include("fbegin.inc"); ?>
<form action="interfaces_isdn.php" method="post">
<?php if ($savemsg) print_info_box($savemsg); ?>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav"><?

			$tabs = array(
				'Network'	=> 'interfaces_network.php',
				//'Wireless'	=> 'interfaces_wireless.php',
				'ISDN'		=> 'interfaces_isdn.php',
				'Analog'	=> 'interfaces_analog.php',
				//'Storage'	=> 'interfaces_storage.php'
			);
			dynamic_tab_menu($tabs);
			
			?></ul>
		</td>
	</tr>
	<tr>
		<td class="tabcont">
			<table width="100%" border="0" cellpadding="6" cellspacing="0"><?

			if (!count($recognized_units)) {
				
				?><tr> 
					<td><strong>No compatible ISDN interfaces detected.</strong>
					<br>
					<br>If an ISDN interface is present but was not detected, please send <a href="/exec_raw.php?cmd=pciconf%20-lv;echo;dmesg">this output</a> to <a href="mailto:michael@askozia.com">michael@askozia.com</a>.
					<br>
					<br>If this is a USB ISDN device, send <a href="/exec_raw.php?cmd=udesc_dump">this output</a> as well.</td>
				</tr><?
	
			} else {

				?><tr>
					<td width="10%" class="listhdrr">Unit</td>
					<td width="30%" class="listhdrr">Name</td>		
					<td width="55%" class="listhdr">Mode</td>
					<td width="5%" class="list"></td>
				</tr><?	
    			
				foreach ($merged_units as $mu) {
    			
				?><tr>
					<td class="listlr"><?=htmlspecialchars($mu['unit']);?></td>
					<td class="listbg"><?=htmlspecialchars($mu['name']);?>&nbsp;</td>
					<td class="listr"><?=htmlspecialchars($isdn_dchannel_modes[$mu['mode']]);?>&nbsp;</td>
					<td valign="middle" nowrap class="list">
						<a href="interfaces_isdn_edit.php?unit=<?=$mu['unit'];?>"><img src="e.gif" title="edit ISDN interface" width="17" height="17" border="0"></a>
					</td>
				</tr><?
				
				}
			}

			?></table>
		</td>
	</tr>
</table>
</form>
<?php include("fend.inc"); ?>
