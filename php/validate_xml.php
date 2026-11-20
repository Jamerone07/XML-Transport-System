<?php
/**
 * Validate transport.xml against DTD and XSD; print libxml diagnostics.
 */
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$xmlPath = SCSMS_XML_DIR . DIRECTORY_SEPARATOR . 'transport.xml';
$xsdPath = SCSMS_XML_DIR . DIRECTORY_SEPARATOR . 'transport.xsd';

$dtdPassed = null;
$xsdPassed = null;
$dtdErrors = [];
$xsdErrors = [];
$notes = [];

if (!is_readable($xmlPath)) {
    $notes[] = 'transport.xml is missing. Run generate_xml.php after importing the database.';
} else {
    libxml_use_internal_errors(true);
    libxml_clear_errors();

    $dom = new DOMDocument();
    // Load with DTD attached for structure validation
    $loaded = $dom->load($xmlPath, LIBXML_DTDLOAD | LIBXML_DTDATTR);
    if (!$loaded) {
        $dtdErrors[] = 'XML could not be parsed.';
        $dtdPassed = false;
    } else {
        $dtdPassed = $dom->validate();
        if (!$dtdPassed) {
            foreach (libxml_get_errors() as $err) {
                $dtdErrors[] = trim($err->message) . ' (line ' . $err->line . ')';
            }
        }
    }

    libxml_clear_errors();
    if (!is_readable($xsdPath)) {
        $xsdPassed = false;
        $xsdErrors[] = 'transport.xsd not found.';
    } else {
        // Reload clean document for schema validation (avoids DTD noise in some builds)
        $dom2 = new DOMDocument();
        $dom2->load($xmlPath, LIBXML_NONET);
        $xsdPassed = $dom2->schemaValidate($xsdPath);
        if (!$xsdPassed) {
            foreach (libxml_get_errors() as $err) {
                $xsdErrors[] = trim($err->message) . ' (line ' . $err->line . ')';
            }
        }
    }

    libxml_clear_errors();
    $notes[] = 'XSD uses XSD 1.1 xs:assert. PHP/libxml may not evaluate every assertion; use a dedicated XML IDE for full 1.1 coverage if results differ.';
}

render_header('Validate XML', 'validate');
?>

<section class="page-head">
  <h1>XML validation</h1>
  <p class="lede">Checks <code>transport.xml</code> against <code>transport.dtd</code> and <code>transport.xsd</code>.</p>
</section>

<?php foreach ($notes as $n): ?>
  <div class="alert alert-info"><?php echo h($n); ?></div>
<?php endforeach; ?>

<section class="grid-2 card-panel">
  <div>
    <h2>DTD validation</h2>
    <?php if ($dtdPassed === null): ?>
      <p class="muted">Skipped</p>
    <?php elseif ($dtdPassed): ?>
      <div class="pill ok">Validation passed</div>
    <?php else: ?>
      <div class="pill bad">Validation failed</div>
      <ul class="error-list">
        <?php foreach ($dtdErrors as $e): ?>
          <li><?php echo h($e); ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
  <div>
    <h2>XSD validation</h2>
    <?php if ($xsdPassed === null): ?>
      <p class="muted">Skipped</p>
    <?php elseif ($xsdPassed): ?>
      <div class="pill ok">Validation passed</div>
    <?php else: ?>
      <div class="pill bad">Validation failed</div>
      <ul class="error-list">
        <?php foreach ($xsdErrors as $e): ?>
          <li><?php echo h($e); ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>

<p><a class="btn btn-primary" href="validate_xml.php">Run again</a>
   <a class="btn btn-ghost" href="generate_xml.php">Regenerate XML</a></p>

<?php render_footer(); ?>
