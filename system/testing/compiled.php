$x.='
		<table border=\'1\'>
			<thead>
				<tr>
					';
if (is_array($data['0'])) {
foreach ($data['0'] as $k0 => $v0) {
	$x.=' <th>';
	$x.=($k0 == null) ? '&nbsp;' : $k0;
	$x.='</th>';

	}
	}
$x.='
				</tr>
			</thead>
			<tbody>
			';
if (is_array($data)) {
foreach ($data as $k0 => $v0) {
	$x.=' 
				<tr>
					';
	if (is_array($v0)) {
		foreach ($v0 as $k1 => $v1) {
		$x.='  <td>';
		$x.=($v1 == null) ? '&nbsp;' : $v1;
		$x.='</td> ';

		}
		}
$x.='
				</tr>
			';

	}
	}
$x.='
			</tbody>
		</table>';
