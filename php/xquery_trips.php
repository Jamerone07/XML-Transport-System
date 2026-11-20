<?php
/**
 * Self-study: XQuery-style "trips per route" aggregation.
 * Native XQuery is not bundled with PHP; this page documents sample XQuery and
 * reproduces the same insight using DOM + XPath counts.
 */
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$xmlPath = SCSMS_XML_DIR . DIRECTORY_SEPARATOR . 'transport.xml';

$xquerySample = <<<'XQ'
(: Example XQuery (run in BaseX, eXist-db, or XMLSpy) :)
for $r in distinct-values(//trip/route)
let $c := count(//trip[route = $r])
order by $r
return <routeSummary name="{$r}" trips="{$c}" />
XQ;

$rows = [];
$error = null;

if (!is_readable($xmlPath)) {
    $error = 'transport.xml not found. Run generate_xml.php first.';
} else {
    $dom = new DOMDocument();
    $dom->load($xmlPath);
    $xp = new DOMXPath($dom);
    $routeNodes = $xp->query('//trip/route');
    $counts = [];
    if ($routeNodes !== false) {
        foreach ($routeNodes as $rn) {
            $name = trim($rn->textContent);
            if ($name === '') {
                continue;
            }
            $counts[$name] = ($counts[$name] ?? 0) + 1;
        }
    }
    ksort($counts, SORT_NATURAL | SORT_FLAG_CASE);
    foreach ($counts as $route => $cnt) {
        $rows[] = ['route' => $route, 'trips' => $cnt];
    }
}

render_header('XQuery (self-study)', 'xquery');
?>

<section class="page-head">
  <h1>Trips per route (XQuery study)</h1>
  <p class="lede">Compare textbook XQuery with a PHP + XPath tally over the same XML file.</p>
</section>

<div class="card-panel">
  <h2>Sample XQuery</h2>
  <pre class="code-block"><?php echo h($xquerySample); ?></pre>
</div>

<?php if ($error !== null): ?>
  <div class="alert alert-error"><?php echo h($error); ?></div>
<?php else: ?>
  <div class="table-responsive card-panel">
    <table class="data-table">
      <thead>
        <tr><th>Route</th><th>Trip count</th></tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?php echo h($r['route']); ?></td>
            <td><?php echo h((string) $r['trips']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php render_footer(); ?>
