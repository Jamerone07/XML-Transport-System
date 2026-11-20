<?php
/**
 * Demonstrate XPath 1.0 queries against transport.xml (five classroom examples).
 */
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$xmlPath = SCSMS_XML_DIR . DIRECTORY_SEPARATOR . 'transport.xml';

$queries = [
    [
        'title' => 'All trips marked Available',
        'xpath' => "//trip[status='Available']",
        'comment' => 'Selects every trip whose status child equals the literal Available.',
    ],
    [
        'title' => 'Trips with capacity greater than 20',
        'xpath' => '//trip[number(capacity) > 20]',
        'comment' => 'number() coerces capacity text to a number for numeric comparison in XPath 1.0.',
    ],
    [
        'title' => 'Trips handled by Juan Dela Cruz',
        'xpath' => "//trip[driver='Juan Dela Cruz']",
        'comment' => 'Filters by exact driver name (string equality).',
    ],
    [
        'title' => 'Morning schedules (departure before 12:00)',
        'xpath' => "//trip[schedule/departure < '12:00']",
        'comment' => 'HH:MM strings compare lexicographically, which matches chronological order for same-day 24h times.',
    ],
    [
        'title' => 'Full trips',
        'xpath' => "//trip[status='Full']",
        'comment' => 'Operational filter for shuttles at passenger capacity.',
    ],
];

$results = [];
if (!is_readable($xmlPath)) {
    $results = [['error' => 'transport.xml not found. Run generate_xml.php first.']];
} else {
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->load($xmlPath);
    $xp = new DOMXPath($dom);
    foreach ($queries as $block) {
        $nodes = @$xp->query($block['xpath']);
        $list = [];
        if ($nodes !== false) {
            foreach ($nodes as $n) {
                if ($n instanceof DOMElement) {
                    $list[] = $dom->saveXML($n);
                }
            }
        }
        $results[] = [
            'title' => $block['title'],
            'xpath' => $block['xpath'],
            'comment' => $block['comment'],
            'items' => $list,
            'count' => ($nodes instanceof DOMNodeList) ? $nodes->length : 0,
        ];
    }
}

render_header('XPath Queries', 'xpath');
?>

<section class="page-head">
  <h1>XPath laboratory</h1>
  <p class="lede">Five XPath expressions over <code>xml/transport.xml</code>. Results render as raw XML snippets per matching <code>trip</code>.</p>
</section>

<?php if (isset($results[0]['error'])): ?>
  <div class="alert alert-error"><?php echo h($results[0]['error']); ?></div>
<?php else: ?>
  <?php foreach ($results as $data): ?>
    <section class="xpath-block card-panel">
      <h2><?php echo h($data['title']); ?></h2>
      <p class="mono small"><?php echo h($data['xpath']); ?></p>
      <p class="muted"><?php echo h($data['comment']); ?></p>
      <p><strong><?php echo h((string) $data['count']); ?></strong> match(es)</p>
      <?php if ($data['items'] === []): ?>
        <p class="empty">No nodes matched.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="data-table">
            <thead><tr><th>#</th><th>Trip XML fragment</th></tr></thead>
            <tbody>
              <?php $i = 1; foreach ($data['items'] as $frag): ?>
                <tr>
                  <td><?php echo h((string) $i++); ?></td>
                  <td><pre class="xml-frag"><?php echo h($frag); ?></pre></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
  <?php endforeach; ?>
<?php endif; ?>

<?php render_footer(); ?>
