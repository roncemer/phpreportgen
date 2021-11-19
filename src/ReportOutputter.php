<?php
namespace Roncemer\PHPReportGen;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ReportOutputter
{
    // This array can be read externally.  It is an array of objects, each of which describes
    // an available output format and contains the following attributes:
    //     format: The output format.  This value should be passed to the setOutputFormat()
    //         function if this output format is desired.
    //     mimeType: The MIME type (Content-Type header) for this output format.
    //     extension: The file extension (filetype) for this output format, including the dot.
    //     description: The human-readable description for this output format.
    public static $OUTPUT_FORMATS;

    // This array can be read externally.  It is an array of objects, each of which describes
    // an available PDF page format and contains the following attributes:
    //     format: The page format.  This value should be assigned to the $pdfPageFormat
    //         attribute if this page format is desired.
    //     description: The human-readable description for this page format.
    public static $PDF_PAGE_FORMATS;

    // This array can be read externally.  It is an array of objects, each of which describes
    // an available PDF page orientation and contains the following attributes:
    //     orientation: The page orientation.  This value should be assigned to the
    //         $pdfPageOrientation attribute if this page orientation is desired.
    //     description: The human-readable description for this page orientation.
    public static $PDF_PAGE_ORIENTATIONS;

    private static $CSV_ENCODE_FROM = array('"', "\r", "\n");
    private static $CSV_ENCODE_TO = array('""', '', '');
    private static $TSV_ENCODE_FROM = array("\t", "\r", "\n");
    private static $TSV_ENCODE_TO = array('', '', '');

    protected $workbookFilename = null;
    protected $workbookUseTempFileForOutput = true;
    public $workbook = null;
    public $worksheet = null;
    public $worksheetRowNum = 1;

    protected $pdfFilename = null;
    protected $pdfUseTempFileForOutput = true;
    protected $pdfFilePointer = false;
    public $pdf = null;

    // Page format for PDF document.
    // Only used for PDF output format.
    // One of 'a3', 'a4', 'a5', 'letter', or 'legal'.
    // Must be equal to the 'format' attribute of one of the $PDF_PAGE_FORMATS entries.
    // Defaults to 'letter'.
    public $pdfPageFormat = 'letter';
    // Page orientation for PDF document.
    // Only used for PDF output format.
    // Must be equal to the 'orientation' attribute of one of the $PDF_PAGE_ORIENTATIONS entries.
    // Defaults to 'P'.
    public $pdfPageOrientation = 'P';

    public $pageNumber = 0;

    // The output format.  Must be equal to the 'format' attribute of
    // one of the output formats listed in ReportOutputter::$OUTPUT_FORMATS.
    public $outputFormat = 'html';
    // The output stream, if writing to a stream.
    public $outputStream = false;
    // The output data, if not writing to a stream.
    public $output = '';

    // Given an output format name, return the element in the $OUTPUT_FORMATS
    // array whose 'format' attribute matches the specified format name.
    // Returns null if an invalid format name was specified.
    public static function getOutputFormatForFormatName($formatName) {
        foreach (ReportOutputter::$OUTPUT_FORMATS as $of) {
            if ($of->format == $formatName) return $of;
        }
        return null;
    }

    // Set the output format.
    // This must be called before the first call to outputRow() within a report.
    // Do not change the output format when a report is in progress.  Instead, wait
    // until after finish() has been called.
    // Parameters:
    // $outputFormat: The format attribute of one of the ReportOutputter::$OUTPUT_FORMATS output formats.
    public function setOutputFormat($outputFormat) {
        if (ReportOutputter::getOutputFormatForFormatName($outputFormat) === null) {
            $outputFormat = ReportOutputter::$OUTPUT_FORMATS[0]->format;
        }
        $this->outputFormat = $outputFormat;
    }

    // Set the output stream, if outputting to a stream.
    // If outputting to a stream, this must be called before the first call to outputRow()
    // within a report.
    // Do not change the output stream when a report is in progress.  Instead, wait
    // until after finish() has been called.
    // The output stream is IGNORED when outputting XLS or PDF format.
    // Use setWorkbookFilename() or setPDFFilename() instead.
    // Parameters:
    // $outputStream: An output stream, as returned by fopen(), or false or null to simply
    //     accumulate the output into $this->output.
    public function setOutputStream($outputStream) {
        $this->outputStream = $outputStream;
    }

    // Set the workbook filename, for outputting XLS format to a file.
    // If outputting to a workbook, this must be called before the first call to outputRow()
    // within a report.
    // Do not change the output file when a report is in progress.  Instead, wait
    // until after finish() has been called.
    // The workbook file is ONLY USED when outputting XLS format.
    // If a non-empty workbook filename is set, the workbook will be output to the specified file
    // and the $output variable will NOT be populated with the raw XLS data.
    // Parameters:
    // $workbookFilename: The filename of the XLS file to which the output will be written.
    public function setWorkbookFilename($workbookFilename) {
        $this->workbookFilename = $workbookFilename;
        $this->workbookUseTempFileForOutput = ($workbookFilename == '') ? true : false;
    }

    // Set the PDF filename, for outputting PDF format to a file.
    // If outputting in PDF format, this must be called before the first call to outputRow()
    // within a report.
    // Do not change the output file when a report is in progress.  Instead, wait
    // until after finish() has been called.
    // The PDF file is ONLY USED when outputting PDF format.
    // If a non-empty PDF filename is set, the PDF document will be output to the specified file
    // and the $output variable will NOT be populated with the raw PDF data.
    // Parameters:
    // $pdfFilename: The filename of the PDF file to which the output will be written.
    public function setPDFFilename($pdfFilename) {
        $this->pdfFilename = $pdfFilename;
        $this->pdfUseTempFileForOutput = ($pdfFilename == '') ? true : false;
    }

    private static function parseRGBToArray($rgb) {
        $pieces = explode(',', $rgb);
        $n = count($pieces);
        for ($i = 0; $i < $n; $i++) $pieces[$i] = (double)$pieces[$i];
        while ($n < 3) {
            $pieces[$n] = 0.0;
            $n++;
        }
        if ($n > 3) $pieces = array_slice($pieces, 0, 3);
        return $pieces;
    }

    public function setPDFRGBFillColor($rgb) {
        $pieces = ReportOutputter::parseRGBToArray($rgb);
        $this->pdf->SetFillColor($pieces[0]*255.0, $pieces[1]*255.0, $pieces[2]*255.0);
    }

    public function setPDFRGBDrawColor($rgb) {
        $pieces = ReportOutputter::parseRGBToArray($rgb);
        $this->pdf->SetDrawColor($pieces[0]*255.0, $pieces[1]*255.0, $pieces[2]*255.0);
    }

    public function setPDFRGBTextColor($rgb) {
        $pieces = ReportOutputter::parseRGBToArray($rgb);
        $this->pdf->SetTextColor($pieces[0]*255.0, $pieces[1]*255.0, $pieces[2]*255.0);
    }

    public function getPDFPageWidth() {
        return $this->pdf->GetPageWidth() - ($this->pdf->getLMargin() + $this->pdf->getRMargin());
    }

    public function getPDFLineSpacing() {
        return (int)($this->pdf->getFontSize()*1.25);
    }

    public function reset() {
        $this->outputStream = false;
        $this->output = '';

        if (($this->workbookFilename !== null) && ($this->workbookUseTempFileForOutput)) {
            @unlink($this->workbookFilename);
        }
        $this->workbookFilename = null;
        $this->workbookUseTempFileForOutput = true;
        $this->workbook = null;
        $this->worksheet = null;
        $this->worksheetRowNum = 1;

        if (($this->pdfFilename !== null) && ($this->pdfUseTempFileForOutput)) {
            @unlink($this->pdfFilename);
        }
        $this->pdfFilename = null;
        $this->pdfUseTempFileForOutput = true;
        $this->pdf = null;

        $this->pageNumber = 0;
    }

    public function createWorkbook() {
        if (($this->workbook === null) &&
            (($this->outputFormat == 'xls') || ($this->outputFormat == 'xlsx') || ($this->outputFormat == 'ods'))) {
            if ($this->workbookUseTempFileForOutput) {
                $this->workbookFilename = tempnam('/tmp', 'rpt');
            }
            $this->workbook = new Spreadsheet();
            $this->workbook->setActiveSheetIndex(0);
            $this->worksheet = $this->workbook->getActiveSheet();
        }
    }

    public function createPDF() {
        if ( ($this->pdf === null) && ($this->outputFormat == 'pdf') ) {
            if ($this->pdfUseTempFileForOutput) {
                $this->pdfFilename = tempnam('/tmp', 'rpt');
            }
            $this->pdf = new FPDFCustom(
                $this->pdfPageOrientation,
                'pt',
                $this->pdfPageFormat
            );
            $this->pdf->SetAutoPageBreak(false);
        }
        $this->pdfFilePointer = fopen($this->pdfFilename, 'wb');
    }

    public function outputText($text) {
        if ($text != '') {
            if ($this->outputStream !== false) {
                fwrite($this->outputStream, $text);
            } else {
                $this->output .= $text;
            }
        }
    }

    public function finish() {
        switch ($this->outputFormat) {
        case 'xls':
        case 'xlsx':
        case 'ods':
            $writerClass = 'PhpOffice\\PhpSpreadsheet\Writer\\'.ucfirst($this->outputFormat);
            $writer = new $writerClass($this->workbook);
            $writer->save($this->workbookFilename);
            if ($this->workbookUseTempFileForOutput) {
                $this->output = file_get_contents($this->workbookFilename);
                @unlink($this->workbookFilename);
            }
            break;
        case 'pdf':
            if ($this->pdfFilePointer !== false) {
                if ($this->pdf !== null) $this->pdf->Close();
                if ($this->pdfFilePointer !== false) {
                    if ($this->pdf !== null) {
                        $data = $this->pdf->Output('S');
                        $this->pdf->resetBuffer();
                        if ($data != '') {
                            fwrite($this->pdfFilePointer, $data, strlen($data));
                        }
                    }
                    fclose($this->pdfFilePointer);
                    $this->pdfFilePointer = false;
                }
            }
            if ($this->pdfUseTempFileForOutput) {
                $this->output = file_get_contents($this->pdfFilename);
                @unlink($this->pdfFilename);
            }
            break;
        }
    }

    public static function encodeCSV($text) {
        $escapedText =
            '"'.
            str_replace(
                ReportOutputter::$CSV_ENCODE_FROM,
                ReportOutputter::$CSV_ENCODE_TO,
                $text
            ).
            '"';
        return $escapedText;
    }

    public static function encodeTSV($text) {
        $escapedText = str_replace(
            ReportOutputter::$TSV_ENCODE_FROM,
            ReportOutputter::$TSV_ENCODE_TO,
            $text
        );
        return $escapedText;
    }
}
ReportOutputter::$OUTPUT_FORMATS = array(
    (object)array('format'=>'html', 'mimeType'=>'application/octet-stream', 'extension'=>'.html', 'description'=>'HTML'),
    (object)array('format'=>'csv', 'mimeType'=>'text/csv', 'extension'=>'.csv', 'description'=>'Comma-Separated Values'),
    (object)array('format'=>'tsv', 'mimeType'=>'text/tab-separated-values', 'extension'=>'.tsv', 'description'=>'Tab-Separated Values'),
    (object)array('format'=>'xls', 'mimeType'=>'application/vnd.ms-excel', 'extension'=>'.xls', 'description'=>'XLS Spreadsheet'),
    (object)array('format'=>'xlsx', 'mimeType'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'extension'=>'.xlsx', 'description'=>'XLSX Spreadsheet'),
    (object)array('format'=>'ods', 'mimeType'=>'application/vnd.oasis.opendocument.spreadsheet', 'extension'=>'.ods', 'description'=>'ODS Spreadsheet'),
    (object)array('format'=>'pdf', 'mimeType'=>'application/pdf', 'extension'=>'.pdf', 'description'=>'Portable Document Format (PDF)'),
);

ReportOutputter::$PDF_PAGE_FORMATS = array(
    (object)array('format'=>'a3', 'description'=>'A3'),
    (object)array('format'=>'a4', 'description'=>'A4'),
    (object)array('format'=>'a5', 'description'=>'A5'),
    (object)array('format'=>'letter', 'description'=>'Letter'),
    (object)array('format'=>'legal', 'description'=>'Legal'),
);

ReportOutputter::$PDF_PAGE_ORIENTATIONS = array(
    (object)array('orientation'=>'P', 'description'=>'Portrait'),
    (object)array('orientation'=>'L', 'description'=>'Landscape'),
);
