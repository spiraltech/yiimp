<?php

include('functions.php');

$this->pageTitle = "Devices";

$devices = array();
$in_db = dbolist("SELECT DISTINCT device, vendorid FROM benchmarks ORDER BY device, vendorid");
foreach ($in_db as $row) {
	// todo: chip column in db
	$vendorid = $row['vendorid'];
	$words = explode(' ', $row['device']);
	$chip = array_pop($words);
	if (!is_numeric($chip)) {
		if (substr($vendorid,0,4) == '10de')
			$chip = array_pop($words);
		else
			$chip = array_pop($words).' '.$chip;
	}
	$devices[$vendorid] = $chip;
}

$chip = 'all';

$options = '<option value="all">Show all</option>';
foreach($devices as $a => $count) {
	if($a == $chip)
		$options .= '<option value="'.$a.'" selected="selected">'.$a.'</option>';
	else
		$options .= '<option value="'.$a.'">'.$a.'</option>';
}

echo <<<end
<div align="right" style="margin-bottom: 2px; margin-right: 0px;">
<input class="search" type="search" data-column="all" style="width: 140px;" placeholder="Search..." />
</div>

<style type="text/css">
tr.ssrow.filtered { display: none; }
td.tick { font-weight: bolder; }
span.generic { color: gray; }
.page .footer { width: auto; };
</style>

<p style="margin-top: -20px; margin-bottom: 4px; line-height: 22px; font-weight: bolder;">
Devices in database
</p>
end;

$algos_columns = '';
$algos = dbocolumn("SELECT DISTINCT algo FROM benchmarks ORDER BY algo LIMIT 30");
foreach ($algos as $algo) {
	$algos_columns .= '<th>'.$algo.'</th>';
}

JavascriptFile("/yaamp/ui/js/jquery.metadata.js");
JavascriptFile("/yaamp/ui/js/jquery.tablesorter.widgets.js");

showTableSorter('maintable', "{
	tableClass: 'dataGrid',
	widgets: ['zebra','filter'],
	textExtraction: {
	//	4: function(node, table, n) { return $(node).attr('data'); }
	},
	widgetOptions: {
		filter_external: '.search',
		filter_columnFilters: false,
		filter_childRows : true,
		filter_ignoreCase: true
	}
}");

echo <<<END
<thead>
<tr>
<th data-sorter="text" width="70">Chip</th>
<th data-sorter="text" width="220">Device</th>
<th data-sorter="text" width="70">Vendor ID</th>
{$algos_columns}
</tr>
</thead><tbody>
END;

foreach ($in_db as $row) {
	echo '<tr class="ssrow">';

	$vendorid = $row['vendorid'];

	echo '<td>'.arraySafeVal($devices, $vendorid, '-').'</td>';
	echo '<td>'.$row['device'].getProductIdSuffix($row).'</td>';

	if (substr($vendorid,0,4) == '10de')
		echo '<td><span class="generic" title="nVidia product id">'.$vendorid.'</i></td>';
	else
		echo '<td>'.$vendorid.'</td>';

	$records = dbocolumn("SELECT algo FROM benchmarks WHERE vendorid=:vid ", array(':vid'=>$vendorid));
	foreach ($algos as $algo) {
		$tick = '&nbsp;';
		if (in_array($algo, $records)) {
			$tick = CHtml::link('✓','/bench?algo='.$algo);
		}
		echo '<td class="tick">'.$tick.'</td>';
	}

	echo '</tr>';
}

echo '</tbody></table><br/>';

echo '<a href="/site/benchmarks">Learn how to submit your results</a>';
echo '<br/><br/>';