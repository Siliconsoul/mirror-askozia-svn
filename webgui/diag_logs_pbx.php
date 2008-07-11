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

$pgtitle = array(gettext("Diagnostics"), gettext("Logs"));
require("guiconfig.inc");

if ($_POST['clear']) {
	exec("/usr/sbin/clog -i -s 262144 /var/log/pbx.log");
	header("Location: diag_logs_pbx.php");
	exit;
}

$packages = packages_get_packages();

$nentries = $config['syslog']['nentries'];
if (!$nentries) {
	$nentries = 100;
}

if (isset($packages['logging']['active'])) {
	$source = "package";
	$logpath = $packages['logging']['datapath'] . "/system/pbx.log";
}
else {
	$source = "internal";
	$logpath = "/var/log/pbx.log";	
}
//-------------pagination logic start----------------------
//XXX This section could me modified into a function, diag_logs.php uses the same code

	$tmp = exec("/usr/bin/wc -l $logpath");
	$lines = preg_split("/\s+/", $tmp, -1, PREG_SPLIT_NO_EMPTY);
	$pages = ceil($lines[0]/$nentries);

	if($_GET['page']) 
		$current_page = $_GET['page'];
	else 
		$current_page = $pages;

	if($current_page == 0 || $current_page == 1) 
		$start = 1;
	else 
		$start = (($nentries*($current_page-1))+1);

	$stop = (($start+$nentries)-1);

	if($source == "internal") {
		$command = "/usr/sbin/clog $logpath | /usr/bin/sed '$start,$stop!d'";
	}
	else {
		$command = "/usr/bin/sed '$start,$stop!d' $logpath";
	}

	$print_pageselector = display_page_selector($current_page, $pages, 12, "diag_logs_pbx.php", "?page=");
 
//---------------pagination logic end----------------------------

exec($command, $logarr);

?>
<?php include("fbegin.inc"); ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav"><?

			$tabs = array(gettext('System') => 'diag_logs.php',
					gettext('PBX') => 'diag_logs_pbx.php',
					gettext('Calls') => 'diag_logs_calls.php',
					gettext('Settings') => 'diag_logs_settings.php');
			dynamic_tab_menu($tabs);

			?></ul>
		</td>
	</tr>
	<tr> 
		<td class="tabcont">

			<?			
			echo $print_pageselector;
			?>

			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr> 
					<td colspan="2" class="listtopic"><?=gettext("Asterisk log entries");?></td>
				</tr><?

			foreach ($logarr as $logent) {
				$logent = preg_split("/\s+/", $logent, 7);
				?><tr valign="top">
					<td class="listlr" nowrap><?=htmlspecialchars(join(" ", array_slice($logent, 0, 3)));?></td>
					<td class="listr"><?=str_replace("^M", "<br>", htmlspecialchars($logent[6]));?></td>
				</tr><?
			}

				?><tr> 
					<td class="list" colspan="2" height="12">&nbsp;</td>
				</tr>
			</table><?

			echo $print_pageselector;

		if ($source == "internal") {
			?><br>
			<form action="diag_logs_pbx.php" method="post">
				<input name="clear" type="submit" class="formbtn" value="<?=gettext("Clear Log");?>">
			</form><?
		}

		?></td>
	</tr>
</table>
<?php include("fend.inc"); ?>
