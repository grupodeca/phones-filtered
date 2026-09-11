<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Bootstrap 5.3.2 CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

	<!-- jQuery 3.7.1 -->
	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

	<title>Líneas telefónicas sin reportar - v1.6</title>

	<style>
		.range-block { display: block; }
	</style>
</head>

<body>
<div class="container mt-4">

<?php
// =====================================
// phones-filtered.php — versión 1.6
// Compatible con PHP 5.4.16
// =====================================

date_default_timezone_set('America/Monterrey');

$PY_DB1_server = mysqli_connect("34.27.218.198","root","mysqldeca","pwd5_server");
if (mysqli_connect_errno()) {
	echo "<div class='alert alert-danger'>Error conectando a MySQL: " . mysqli_connect_error() . "</div>";
	exit;
}

$extra = (array_key_exists("extra", $_GET) ? filter_var($_GET["extra"], FILTER_VALIDATE_BOOLEAN) : false);

if (array_key_exists("ids", $_GET)) {
	$ids      = explode(",", $_GET["ids"]);
	$ids_clean = array();
	foreach ($ids as $i) {
		$ids_clean[] = "'" . mysqli_real_escape_string($PY_DB1_server, $i) . "'";
	}
	$ids = " AND idGPS IN (" . implode(",", $ids_clean) . ")";
} else {
	$ids = "";
}

$minutos = array(
	10  => 60,
	61  => 120,
	121 => 180,
	181 => 240,
	241 => 300,
	301 => 360,
	361 => 1440
);

echo "<h3 class='mb-3'>" . date('Y-m-d H:i:s T') . "</h3>";

$offset = date('Z') / 3600;

echo "<div class='alert alert-primary'>Helpdesk Telcel puede realizar <strong>RESETEO</strong> de línea, borra la sesión de datos ya sea 2G, 3G, 4G o 5G.</div>";
echo "<div class='alert alert-primary'>Helpdesk Telcel puede realizar <strong>BORRADO DE REGISTRO</strong>, desconecta la línea de la red móvil de Telcel.</div>";
echo "<div class='alert alert-primary'>PDPs = sesiones activas en el APN DECA.ITELCEL.COM.</div>";

// =====================================
// EXPORTACIÓN TXT
// =====================================

if (isset($_GET['export'])) {

	$tipo = $_GET['export'];

	if ($tipo == "telcel") {
		$filename = "phone-filtered-telcel.txt";
		$where_number = "phoneNumber LIKE '844%'";
	} else {
		$filename = "phone-filtered-internacional.txt";
		$where_number = "phoneNumber LIKE '+%'";
	}

	$fh = fopen($filename, "w");
	// Forzar archivo UTF-8 sin BOM
	fprintf($fh, "\xEF\xBB\xBF");   // BOM UTF-8 opcional y recomendado
	fwrite($fh, date("Y-m-d H:i:s T") . "\n\n");

	foreach ($minutos as $from => $to) {

		$sql_tipo_count = "
			SELECT COUNT(*) AS c
			FROM pwd5_server.gps_info i
			WHERE isValid = 1
			AND $where_number
			AND reportDate < ADDDATE(UTC_TIMESTAMP(), INTERVAL -$from MINUTE)
			AND reportDate > ADDDATE(UTC_TIMESTAMP(), INTERVAL -$to MINUTE)
			AND phoneNumber IS NOT NULL
			AND phoneNumber != 0
			AND i.idgpstype NOT IN (59,60,82,78,105,106)
			$ids;
		";

		$rA = mysqli_query($PY_DB1_server, $sql_tipo_count);
		$X = intval(mysqli_fetch_array($rA)['c']);

		$header = "-- Líneas de $from min (" . round(($from-1)/60,2) .
				  " hr) a $to min (" . round($to/60,2) . " hr) sin reportar ($X)\n";

		fwrite($fh, $header);

		$sql_export = "
			SELECT phoneNumber
			FROM pwd5_server.gps_info i
			WHERE isValid = 1
			AND $where_number
			AND reportDate < ADDDATE(UTC_TIMESTAMP(), INTERVAL -$from MINUTE)
			AND reportDate > ADDDATE(UTC_TIMESTAMP(), INTERVAL -$to MINUTE)
			AND phoneNumber IS NOT NULL
			AND phoneNumber != 0
			AND i.idgpstype NOT IN (59,60,82,78,105,106)
			$ids
			ORDER BY reportDate DESC;
		";

		$r2 = mysqli_query($PY_DB1_server, $sql_export);

		while ($row = mysqli_fetch_array($r2)) {
			fwrite($fh, $row['phoneNumber'] . "\n");
		}

		fwrite($fh, "\n");
	}

	fclose($fh);

	echo "<div class='alert alert-success'>Archivo <strong>$filename</strong> generado correctamente.<br>
			<a href='$filename' target='_blank'>Abrir $filename</a></div>";
}

?>

<!-- ============================
	  FILTROS + BOTONES EXPORTAR
	 ============================ -->

<div class="card p-3 mb-4 border-primary">

	<h5 class="mb-3">Filtros de tiempo sin reportar</h5>

	<div class="form-check mb-2">
		<input class="form-check-input filter-all" type="checkbox" id="filter_all" checked>
		<label class="form-check-label" for="filter_all">Mostrar TODOS los rangos</label>
	</div>

	<hr>

	<div class="row mb-3">
		<?php
		foreach ($minutos as $from => $to) {
			$label = "Líneas de $from min (" . round(($from-1)/60,2) .
					" hr) a $to min (" . round($to/60,2) . " hr)";
			$range_id = "range_{$from}_{$to}";
			echo "
			<div class='col-md-6'>
				<div class='form-check'>
					<input class='form-check-input filter-range' type='checkbox'
						data-from='$from' data-to='$to' id='$range_id'>
					<label class='form-check-label' for='$range_id'>$label</label>
				</div>
			</div>";
		}
		?>
	</div>

	<hr>

	<!-- BOTONES EXPORTACIÓN (YA NO ABREN nueva ventana) -->
	<div class="row">
		<div class="col-md-6 mb-2">
			<a href="?export=telcel" class="btn btn-success w-100">
				Exportar Telcel
			</a>
		</div>
		<div class="col-md-6 mb-2">
			<a href="?export=intl" class="btn btn-warning w-100">
				Exportar Internacional
			</a>
		</div>
	</div>

</div>

<?php
// ======================================
// BLOQUES DE VISUALIZACIÓN
// ======================================

foreach ($minutos as $from => $to) {

	$sql_count = "
		SELECT COUNT(*) AS count
		FROM pwd5_server.gps_info i
		WHERE isValid = 1
		AND reportDate < ADDDATE(UTC_TIMESTAMP(), INTERVAL -$from MINUTE)
		AND reportDate > ADDDATE(UTC_TIMESTAMP(), INTERVAL -$to MINUTE)
		AND reportDate != '0000-00-00 00:00:00'
		AND phoneNumber != 0
		AND phoneNumber IS NOT NULL
		AND i.idgpstype NOT IN (59,60,82,78,105,106)
		$ids;
	";

	$r = mysqli_query($PY_DB1_server, $sql_count);
	$count = intval(mysqli_fetch_array($r)['count']);

	if ($count > 0) {

		$sql_telcel = "
			SELECT COUNT(*) AS c
			FROM pwd5_server.gps_info i
			WHERE isValid = 1
			AND phoneNumber LIKE '844%'
			AND reportDate < ADDDATE(UTC_TIMESTAMP(), INTERVAL -$from MINUTE)
			AND reportDate > ADDDATE(UTC_TIMESTAMP(), INTERVAL -$to MINUTE)
			AND phoneNumber != 0
			AND phoneNumber IS NOT NULL
			AND i.idgpstype NOT IN (59,60,82,78,105,106)
			$ids;
		";

		$TELCEL = intval(mysqli_fetch_array(mysqli_query($PY_DB1_server, $sql_telcel))['c']);

		$sql_intl = "
			SELECT COUNT(*) AS c
			FROM pwd5_server.gps_info i
			WHERE isValid = 1
			AND phoneNumber LIKE '+%'
			AND reportDate < ADDDATE(UTC_TIMESTAMP(), INTERVAL -$from MINUTE)
			AND reportDate > ADDDATE(UTC_TIMESTAMP(), INTERVAL -$to MINUTE)
			AND phoneNumber != 0
			AND phoneNumber IS NOT NULL
			AND i.idgpstype NOT IN (59,60,82,78,105,106)
			$ids;
		";

		$INTL = intval(mysqli_fetch_array(mysqli_query($PY_DB1_server, $sql_intl))['c']);

		$sql = "
			SELECT 
				idGPS,
				phoneNumber,
				ADDDATE(reportDate, INTERVAL $offset HOUR) AS reportDate,
				t.description_es
			FROM pwd5_server.gps_info i
			LEFT JOIN gps_types t ON (i.idGPSType = t.idGPSType)
			WHERE isValid = 1
			AND reportDate < ADDDATE(UTC_TIMESTAMP(), INTERVAL -$from MINUTE)
			AND reportDate > ADDDATE(UTC_TIMESTAMP(), INTERVAL -$to MINUTE)
			AND phoneNumber != 0
			AND phoneNumber IS NOT NULL
			AND reportDate != '0000-00-00 00:00:00'
			AND i.idgpstype NOT IN (59,60,82,78,105,106)
			$ids
			ORDER BY reportDate DESC;
		";

		$r2 = mysqli_query($PY_DB1_server, $sql);
		$block_id = "block_{$from}_{$to}";

		echo "<div class='range-block mb-5' id='$block_id'>";

		echo "<h4>Líneas de $from min (" . (($from - 1)/60) .
			" hr) a $to min (" . ($to/60) . " hr) sin reportar ($count)</h4>";

		echo "<p class='ms-3'>
				<strong>Telcel:</strong> $TELCEL<br>
				<strong>Internacional:</strong> $INTL
			  </p>";

		echo "<table class='table table-striped table-hover'>";
		echo "<thead><tr>";
		if ($extra) echo "<th>ID GPS</th>";
		echo "<th>Teléfono</th>";
		echo "<th>Último reporte</th>";
		if ($extra) echo "<th>Tipo</th>";
		echo "</tr></thead><tbody>";

		while ($row = mysqli_fetch_array($r2)) {

			echo "<tr>";
			if ($extra) echo "<td>".$row['idGPS']."</td>";
			echo "<td>".$row['phoneNumber']."</td>";
			echo "<td>".$row['reportDate']."</td>";
			if ($extra) echo "<td>".$row['description_es']."</td>";
			echo "</tr>";
		}

		echo "</tbody></table></div>";

		mysqli_free_result($r2);
	}
}

mysqli_close($PY_DB1_server);
?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ===============================
// Filtro dinámico
// ===============================

$(document).ready(function() {

	$(".filter-range").prop("checked", false);
	$("#filter_all").prop("checked", true);

	$("#filter_all").on("change", function() {
		if ($(this).is(":checked")) {
			$(".filter-range").prop("checked", false);
			$(".range-block").show();
		}
	});

	$(".filter-range").on("change", function() {

		if ($(this).is(":checked")) {
			$("#filter_all").prop("checked", false);
		}

		var selected = $(".filter-range:checked");

		if (selected.length === 0) {
			$("#filter_all").prop("checked", true);
			$(".range-block").show();
			return;
		}

		$(".range-block").hide();

		selected.each(function() {
			var from = $(this).data("from");
			var to   = $(this).data("to");
			$("#block_" + from + "_" + to).show();
		});
	});
});
</script>

</body>
</html>
