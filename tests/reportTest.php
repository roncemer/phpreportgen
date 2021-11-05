<?php
require_once(dirname(__DIR__).'/vendor/autoload.php');

use Roncemer\PHPReportGen\Report;
use Roncemer\PHPReportGen\ReportColumn;
use Roncemer\PHPReportGen\ReportLevel;
use Roncemer\PHPReportGen\ReportOutputter;

$outputFormats = ['html', 'csv', 'tsv', 'xls', 'pdf'];

if ($argc != 2) {
    fprintf(STDERR, "Please specify output format: %s\n", implode(', ', $outputFormats));
    exit(1);
}

if (!in_array($argv[1], $outputFormats)) {
    fprintf(STDERR, "Invalid output format(%s); please specify one of the following: %s\n", $argv[1], implode(', ', $outputFormats));
    exit(2);
}

$outputFormat = $argv[1];

// --------------------------------
// Test Data
// --------------------------------

class Client {
	public $clientId;
	public $clientName;

	public function __construct($clientId, $clientName) {
		$this->clientId = $clientId;
		$this->clientName = $clientName;
	}
}

$clients = array(
	new Client(1, 'ABC Company'),
	new Client(2, 'George Johnson'),
	new Client(3, 'John Doe'),
	new Client(4, 'Kitty Litter'),
	new Client(5, 'Rose Flowers'),
);

class Product {
	public $productId;
	public $productDescription;
	public $unitPrice;

	public function __construct($productId, $productDescription, $unitPrice) {
		$this->productId = $productId;
		$this->productDescription = $productDescription;
		$this->unitPrice = $unitPrice;
	}
}

$products = array(
	new Product(1, 'Back Massager', 14.95),
	new Product(2, 'Home Theater System', 257.89),
	new Product(3, 'Picture Frame', 8.67),
	new Product(4, 'Teddy Bear', 14.39),
	new Product(5, 'King James Bible', 24.98),
	new Product(6, 'Pet Carrier', 39.97),
);

class ReportRow {
	public $clientId;
	public $clientName;
	public $saleDate;
	public $productId;
	public $productDescription;
	public $unitPrice;
	public $quantity;

	public function __construct($clientId, $saleDate, $productId, $quantity) {
		global $clients, $products;

		$this->clientId = $clientId;
		foreach ($clients as &$client) {
			if ($client->clientId == $clientId) {
				$this->clientName = $client->clientName;
				break;
			}
		}
		$this->saleDate = $saleDate;
		$this->productId = $productId;
		foreach ($products as &$product) {
			if ($product->productId == $productId) {
				$this->productDescription = $product->productDescription;
				$this->unitPrice = $product->unitPrice;
				break;
			}
		}
		$this->quantity = $quantity;
	}
}

$rows = array(
	new ReportRow(1, '2011-01-12', 2, 3),
	new ReportRow(1, '2011-01-12', 3, 1),
	new ReportRow(1, '2011-01-14', 3, 3),

	new ReportRow(2, '2011-01-12', 1, 4),
	new ReportRow(2, '2011-01-12', 2, 1),
	new ReportRow(2, '2011-01-13', 6, 1),
	new ReportRow(2, '2011-01-14', 3, 2),

	new ReportRow(3, '2011-01-15', 1, 1),
	new ReportRow(3, '2011-01-15', 4, 3),
	new ReportRow(3, '2011-01-15', 5, 1),
	new ReportRow(3, '2011-01-18', 6, 1),

	new ReportRow(4, '2011-01-15', 2, 2),
	new ReportRow(4, '2011-01-15', 6, 2),

	new ReportRow(5, '2011-01-15', 2, 4),
	new ReportRow(5, '2011-01-15', 3, 1),
	new ReportRow(5, '2011-01-15', 6, 7),
);

// -------------------------------------------
// Column Caluclation and Formatting Callbacks
// -------------------------------------------

function calcTotalPrice($row, $report, $column, $level) {
	if ($level !== null) return isset($row->totalPrice) ? $row->totalPrice : 0.00;
	return round($row->quantity*$row->unitPrice, 2);
}

function formatDollars($row, $report, $column, $level, $value) {
	return '$'.number_format($value, 2);
}

// --------------------------------
// Report Column Definitions
// --------------------------------

$reportColumns = array(
	new ReportColumn(array(
		'name'=>'clientId',
		'heading'=>'Client Id',
		'align'=>'right',
		'format'=>'number',
		'decimalPlaces'=>0,
		'relativeWidth'=>10,
	)),
	new ReportColumn(array(
		'name'=>'clientName',
		'heading'=>'Client Name',
		'align'=>'left',
		'format'=>'string',
		'relativeWidth'=>30,
	)),
	new ReportColumn(array(
		'name'=>'saleDate',
		'heading'=>'Sale Date',
		'align'=>'left',
		'format'=>'string',
		'relativeWidth'=>10,
	)),
	new ReportColumn(array(
		'name'=>'productId',
		'heading'=>'Product Id',
		'align'=>'right',
		'format'=>'number',
		'decimalPlaces'=>0,
		'relativeWidth'=>10,
	)),
	new ReportColumn(array(
		'name'=>'productDescription',
		'heading'=>'Product Description',
		'align'=>'left',
		'format'=>'string',
		'relativeWidth'=>30,
	)),
	new ReportColumn(array(
		'name'=>'quantity',
		'heading'=>'Quantity',
		'align'=>'right',
		'format'=>'number',
		'decimalPlaces'=>0,
		'relativeWidth'=>10,
		'outputTotalsAtLevels'=>array('grand', 'client', 'saleDate'),
	)),
	new ReportColumn(array(
		'name'=>'unitPrice',
		'heading'=>'Unit Price',
		'align'=>'right',
		'format'=>'number',
		'decimalPlaces'=>2,
		'relativeWidth'=>10,
		'formatCallback'=>'formatDollars',
	)),
	new ReportColumn(array(
		'name'=>'totalPrice',
		'heading'=>'Total Price',
		'align'=>'right',
		'format'=>'number',
		'decimalPlaces'=>2,
		'relativeWidth'=>10,
		'outputTotalsAtLevels'=>array('grand', 'client', 'saleDate'),
		'valueCalcCallback'=>'calcTotalPrice',
		'formatCallback'=>'formatDollars',
	)),
);

// --------------------------------
// Report Level Definitions
// --------------------------------

$reportLevels = array(
	// Comment out this level to disable grand totals.
	new ReportLevel(array(
		'name'=>'grand',
		'uniqueIdColumnNames'=>array(),
		'totalsDescription'=>'Grand Totals',
		'totalsDescriptionLeftColumnName'=>'saleDate',
		'totalsDescriptionColumnSpan'=>2
	)),
	new ReportLevel(array(
		'name'=>'client',
		'uniqueIdColumnNames'=>array('clientId', 'clientName'),
		'totalsDescription'=>'Client Totals',
		'totalsDescriptionLeftColumnName'=>'saleDate',
		'totalsDescriptionColumnSpan'=>2,
		// Change this setting to true to re-output the report headings for every new client.
		'reOutputHeadingAfterEachLevelTotal'=>false
	)),
	new ReportLevel(array(
		'name'=>'saleDate',
		'uniqueIdColumnNames'=>array('saleDate'),
		'totalsDescription'=>'Date Totals',
		'totalsDescriptionLeftColumnName'=>'saleDate',
		'totalsDescriptionColumnSpan'=>2
	)),
);

// --------------------------------
// Report Output Definitions
// --------------------------------

$outputter = new ReportOutputter();
$outputter->setOutputFormat($outputFormat);
$report = new Report($reportColumns, $reportLevels, $outputter, 'Client Purchases by Date');
$report->outputCompleteHTMLDocument = true;

$fp = false;

switch ($outputter->outputFormat) {
case 'html':
case 'csv':
case 'tsv':
    $fp = fopen(__DIR__.'/output.'.$outputter->outputFormat, 'w');
    $outputter->setOutputStream($fp);
    break;
case 'xls':
case 'pdf':
    $outputter->setWorkbookFilename(__DIR__.'/output.'.$outputter->outputFormat);
    break;
}

foreach ($rows as &$row) {
	$report->outputRow($row);
}
$report->finish();
$outputter->finish();

if ($fp !== false) {
    fclose($fp);
}
