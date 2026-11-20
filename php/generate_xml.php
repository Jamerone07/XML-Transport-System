<?php
/**
 * Export MySQL trips to transport.xml (DTD + XSD references) and refresh HTML report via XSLT.
 */
declare(strict_types=1);

require_once __DIR__ . '/connect.php';

$messages = [];
$errors = [];

try {
    $res = db()->query('SELECT route_name, driver_name, departure_time, arrival_time, capacity, available_seats, shuttle_status
                        FROM transport_trips ORDER BY id ASC');
    if ($res === false) {
        throw new RuntimeException('Query failed.');
    }
    $rows = $res->fetch_all(MYSQLI_ASSOC);
} catch (Throwable $e) {
    $rows = [];
    $errors[] = 'Could not read database: ' . $e->getMessage();
}

if ($errors === []) {
    $impl = new DOMImplementation();
    $doctype = $impl->createDocumentType('transport', null, 'transport.dtd');
    $dom = $impl->createDocument('', 'transport', $doctype);
    $dom->encoding = 'UTF-8';
    $dom->formatOutput = true;
    $root = $dom->documentElement;
    if ($root === null) {
        $errors[] = 'Could not create XML root.';
    } else {
        $root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $root->setAttribute('xsi:noNamespaceSchemaLocation', 'transport.xsd');

        foreach ($rows as $r) {
            $trip = $dom->createElement('trip');
            $trip->appendChild($dom->createElement('route', (string) $r['route_name']));
            $trip->appendChild($dom->createElement('driver', (string) $r['driver_name']));
            $sched = $dom->createElement('schedule');
            $dep = substr((string) $r['departure_time'], 0, 5);
            $arr = substr((string) $r['arrival_time'], 0, 5);
            $sched->appendChild($dom->createElement('departure', $dep));
            $sched->appendChild($dom->createElement('arrival', $arr));
            $trip->appendChild($sched);
            $trip->appendChild($dom->createElement('capacity', (string) (int) $r['capacity']));
            $trip->appendChild($dom->createElement('availableSeats', (string) (int) $r['available_seats']));
            $trip->appendChild($dom->createElement('status', (string) $r['shuttle_status']));
            $root->appendChild($trip);
        }

        $xmlPath = SCSMS_XML_DIR . DIRECTORY_SEPARATOR . 'transport.xml';
        if (@$dom->save($xmlPath) === false) {
            $errors[] = 'Failed to write transport.xml (check folder permissions).';
        } else {
            $messages[] = 'transport.xml written (' . count($rows) . ' trips).';
        }
    }
}

// XSLT → static HTML dashboard
if ($errors === []) {
    $xmlPath = SCSMS_XML_DIR . DIRECTORY_SEPARATOR . 'transport.xml';
    $xslPath = SCSMS_XML_DIR . DIRECTORY_SEPARATOR . 'transport.xsl';
    $outHtml = SCSMS_HTML_DIR . DIRECTORY_SEPARATOR . 'transport_report.html';

    if (!is_readable($xslPath)) {
        $errors[] = 'transport.xsl is missing or not readable.';
    } else {
        $xsl = new DOMDocument();
        $xsl->load($xslPath);
        $proc = new XSLTProcessor();
        $proc->importStylesheet($xsl);
        $xml = new DOMDocument();
        $xml->load($xmlPath);
        $html = $proc->transformToXml($xml);
        if ($html === false || $html === null) {
            $errors[] = 'XSLT transformation failed.';
        } elseif (@file_put_contents($outHtml, $html) === false) {
            $errors[] = 'Could not write html/transport_report.html.';
        } else {
            $messages[] = 'transport_report.html generated via XSLT.';
        }
    }
}

render_header('Generate XML', 'xmlgen');
?>

<section class="page-head">
  <h1>Generate XML &amp; report</h1>
  <p class="lede">Pulls live rows from MySQL, writes <code>xml/transport.xml</code> (with DTD/XSD hooks), then builds <code>html/transport_report.html</code> using XSLT.</p>
</section>

<?php foreach ($messages as $m): ?>
  <div class="alert alert-success"><?php echo h($m); ?></div>
<?php endforeach; ?>

<?php foreach ($errors as $m): ?>
  <div class="alert alert-error"><?php echo h($m); ?></div>
<?php endforeach; ?>

<p><a class="btn btn-primary" href="generate_xml.php">Run again</a>
   <a class="btn btn-ghost" href="../html/transport_report.html" target="_blank" rel="noopener">Open HTML report</a>
   <a class="btn btn-ghost" href="validate_xml.php">Validate XML</a></p>

<?php render_footer(); ?>
