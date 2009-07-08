#!/usr/bin/php
<?php 
/*
	$Id$
	originally part of m0n0wall (http://m0n0.ch/wall)
	continued modifications as part of AskoziaPBX (http://askozia.com/pbx)
	
	Copyright (C) 2003-2006 Manuel Kasper <mk@neon1.net>.
	Copyright (C) 2007-2009 IKT <http://itison-ikt.de>.
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

$pgtitle = array(gettext("Diagnostics"), gettext("Logs"));

if ($_POST['clear']) {
	// LINUX TODO : clearing logs
	exec("/sbin/logread -i -s 262144 /var/log/system.log");
	header("Location: diag_logs.php");
	exit;
}

$packages = packages_get_packages();

$nentries = $config['syslog']['nentries'];
if (!$nentries) {
	$nentries = 100;
}

if (isset($packages['logging']['active'])) {
	$source = "package";
	$logpath = $packages['logging']['datapath'] . "/system/system.log";
}
else {
	$source = "internal";
	$logpath = "/var/log/system.log";
}

//---------------pagination/filter logic start----------------------------

if(isset($config['syslog']['reverse'])) {
	$sort = true;
}

if($_GET['filter']) {
	$filter = $_GET['filter'];
}

$pages = display_calculate_pages($filter, $logpath, $source, $nentries);

if(!$pages) {
	$message = gettext("No matches found.");
}

$current_page = display_calculate_current_page($pages, $sort);
$command = display_get_command($current_page, $nentries, $source, $filter, $logpath, $sort);
$print_pageselector = display_page_selector($current_page, $pages, 12, $filter);

//---------------pagination/filter logic end----------------------------

exec($command, $logarr);

include("fbegin.inc");

?><script type="text/JavaScript">
<!--
	<?=javascript_filter_textbox("functions");?>

	jQuery(document).ready(function(){

		<?=javascript_filter_textbox("ready");?>

	});

//-->
</script>
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
					<td colspan="2" class="listtopic">
					<form action="diag_logs.php" method="get" id="filtering" class="display">
						<div class="align_right">
							<label for="filter" style="display: none;"> <?=gettext("filter");?></label>
							<input name="filter" id="filter" type="text" width="20" class="filterbox" value="<?=$filter;?>">
							<?
							if(!$filter)
								echo "<input type='image' src='set_filter.png' name='set' class='verticalalign'>";
							else
								echo "<a href='?'><img class='verticalalign' src='remove_filter.png' name='erase'></a>";
							?>
						</div>
					</form>
					<div class="padding_top"><?=gettext("System log entries");?></div>
					</td>
				</tr><?

				foreach ($logarr as $logent) {
					$logent = preg_split("/\s+/", $logent, 6);
					?><tr valign="top">
						<td class="listlr" nowrap><?=htmlspecialchars(join(" ", array_slice($logent, 0, 3)));?></td>
						<td class="listr"><?=htmlspecialchars($logent[4] . " " . $logent[5]);?></td>
					</tr><?
				}

				?><tr>
					<? 
					if($message)
						echo "<td class='filter_info_message' colspan='2' height='12'>$message</td>";
					else
						echo "<td class='list' colspan='2' height='12'>&nbsp;</td>";
					?>
				</tr>
			</table><?

			echo $print_pageselector;

		if ($source == "internal") {
			?><br>
			<form action="diag_logs.php" method="post">
				<input name="clear" type="submit" class="formbtn" value="<?=gettext("Clear Log");?>">
			</form>
			<?
		}

		?></td>
	</tr>
</table>
<?php include("fend.inc"); ?>
