<?php
namespace Roncemer\PHPReportGen;

use stdClass;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Report
{
    public $columns;
    public $levels;
    public $outputter;
    public $title;

    // Whether to always output unique identifier column values when outputting csv
    // or tsv format. Normally it would be desirable to always output all column
    // values in csv or tsv format, so this defaults to true.
    // Set this to false to enable suppressing of duplicate unique identifier column
    // values in csv or tsv output format.
    public $alwaysOutputUniqueIdColumnsInCSVOrTSVFormat = true;

    // Whether to always output unique identifier column values when outputting html
    // format. Normally it would be desirable to suppress duplicate unique identifier
    // column values in html format, so this defaults to false.
    // Set this to true to disable suppressing of duplicate unique identifier column
    // values in html output format.
    public $alwaysOutputUniqueIdColumnsInHTMLFormat = false;

    // Whether to always output unique identifier column values when outputting pdf
    // format. Normally it would be desirable to suppress duplicate unique identifier
    // column values in pdf format, so this defaults to false.
    // Set this to true to disable suppressing of duplicate unique identifier column
    // values in pdf output format.
    public $alwaysOutputUniqueIdColumnsInPDFFormat = false;

    // Whether to always output unique identifier column values when outputting xls,
    // xlsx or ods (spreadsheet) formats. Normally it would be desirable to suppress
    // duplicate unique identifier column values in spreadsheet output formats, so
    // this defaults to false.
    // Set this to true to disable suppressing of duplicate unique identifier column
    // values in spreadsheet output formats.
    public $alwaysOutputUniqueIdColumnsInXLSFormat = false;

    // Whether to output totals when outputting csv or tsv format.
    // Normally it would not be desirable to include totals lines when outputting
    // in csv or tsv output format, so this defaults to false.
    // Set this to true to enable outputting of totals when outputting in csv or tsv
    // format.
    public $outputTotalsInCSVOrTSVFormat = false;

    protected $headingOutput = false;
    protected $htmlTableOpen = false;
    protected $htmlTableSection = null;
    protected $prevIdValues = array();
    protected $totals = array();

    public $oddRow = false;

    // When outputting HTML, these control the classes and attributes
    // of the various HTML tags which are output.
    public $htmlTableClass = 'reportTable';
    public $htmlTableBorder = '1';
    public $htmlTableCellSpacing = '0';
    public $htmlTableCellPadding = '2';
    public $htmlTHeadClass = '';
    public $htmlTHeadTRClass = '';
    public $htmlTHeadTHClass = '';
    public $htmlTHeadTHNoWrap = 'nowrap';
    public $htmlTHeadTHAlign = 'center';
    public $htmlTBodyClass = '';
    public $htmlTBodyTRClass = '';
    public $htmlTBodyTROddClass = 'odd';
    public $htmlTBodyTREvenClass = 'even';
    public $htmlTBodyTDClass = '';
    public $htmlTBodyTDNoWrap = 'nowrap';
    public $htmlTBodyTotalsTRClass = 'reportTotals';
    public $htmlTBodyTotalsTDClass = '';
    public $htmlTBodyTotalsTDNoWrap = 'nowrap';
    // If this is true, a complete HTML document will be output if the output
    // format is 'html'; otherwise, HTML which is suitable for inclusion within
    // a greater HTML document will be output.  Defaults to true.
    // Ignored if the output format is not 'html'.
    public $outputCompleteHTMLDocument = true;

    // When outputting PDF, these control the appearance.
    // General parameters:
    public $pdfColumnSpacing = 2;                            // space between columns, in points
    // Title parameters:
    public $pdfTitleFontFamily = 'helvetica';
    public $pdfTitleFontStyle = 'B';
    public $pdfTitleFontSize = 12;
    public $pdfTitleTextRGB = '0.0,0.0,0.0';                // comma-separated r,g,b [0.0...1.0]
    // Page Number parameters:
    public $pdfPageNumFontFamily = 'helvetica';
    public $pdfPageNumFontStyle = 'B';
    public $pdfPageNumFontSize = 8;
    public $pdfPageNumTextRGB = '0.0,0.0,0.0';                // comma-separated r,g,b [0.0...1.0]
    public $pdfPageNumWidth = 108;                            // 1.5 inch
    // Heading parameters:
    public $pdfHeadingBackgroundFill = true;                // fill background true/false
    public $pdfHeadingBackgroundRGB = '0.75,0.88,0.94';        // comma-separated r,g,b [0.0...1.0]
    public $pdfHeadingBorder = 'B';                            // 0=none; 1=frame; or any combination
                                                            // of L,R,T,B for left/right/top/bottom
    public $pdfHeadingBorderRGB = '0.5,0.5,0.5';            // comma-separated r,g,b [0.0...1.0]
    public $pdfHeadingFontFamily = 'helvetica';
    public $pdfHeadingFontStyle = 'B';
    public $pdfHeadingFontSize = 8;
    public $pdfHeadingTextRGB = '0.25,0.25,0.25';            // comma-separated r,g,b [0.0...1.0]
    // Detail parameters:
    public $pdfDetailFontFamily = 'helvetica';
    public $pdfDetailFontStyle = '';
    public $pdfDetailFontSize = 8;
    public $pdfDetailTextRGB = '0.25,0.25,0.25';            // comma-separated r,g,b [0.0...1.0]
    public $pdfDetailBackgroundOddRGB = '0.886,0.894,1.0';
    public $pdfDetailBackgroundEvenRGB = '1.0,1.0,1.0';
    // Totals parameters:
    public $pdfTotalsFontFamily = 'helvetica';
    public $pdfTotalsFontStyle = 'B';
    public $pdfTotalsFontSize = 8;
    public $pdfTotalsTextRGB = '0.25,0.25,0.25';            // comma-separated r,g,b [0.0...1.0]

    // Construct a Report instance.
    // Parameters:
    // $columns: An array of ReportColumn instances which describe the columns.
    // $levels: An array of ReportLevel instances which describe the levels.  The first element
    //     is always the top level (the level at which grand totals would be printed).
    // $outputter: A ReportOutputter instance which will handle the output functionality.
    // $title: The title of the report.  Optional.  Only applies to certain output types.
    public function __construct(&$columns, &$levels, &$outputter, $title = '') {
        $this->columns = &$columns;
        $this->levels = &$levels;
        $this->outputter = &$outputter;
        $this->title = $title;
    }

    public function softReset() {
        $this->headingOutput = false;
        $this->htmlTableOpen = false;
        $this->htmlTableSection = null;
        $this->prevIdValues = array();
        $this->totals = array();
        $this->oddRow = false;
    }

    // Reset totals; get ready to start the report again.
    // This function does not change the output format.
    // This function resets the output stream and empties the output.
    public function reset() {
        $this->softReset();

        $this->outputter->reset();
    }

    public function forceNewHeadings() {
        $this->headingOutput = false;
    }

    public function findLevelIdxByName($levelName) {
        for ($i = 0, $n = count($this->levels); $i < $n; $i++) {
            if ($this->levels[$i]->name == $levelName) {
                return $i;
            }
        }
        return -1;
    }

    protected function outputHeading($forceNewPage = false) {
        switch ($this->outputter->outputFormat) {
        case 'xls':
        case 'xlsx':
        case 'ods':
            if ($this->outputter->workbook === null) $this->outputter->createWorkbook();
            break;
        case 'pdf':
            $newPage = false;
            if ($this->outputter->pdf === null) {
                $this->outputter->createPDF();
                $this->outputter->pdf->AddPage();
                $this->outputter->pageNumber++;
                $newPage = true;
            } else if ($forceNewPage) {
                $this->outputter->flushPDF();
                $this->outputter->pdf->AddPage();
                $this->outputter->pageNumber++;
                $newPage = true;
            } else {
                // Calculate height of headings.
                $this->outputter->pdf->SetFont(
                    $this->pdfHeadingFontFamily,
                    $this->pdfHeadingFontStyle,
                    $this->pdfHeadingFontSize
                );
                $hs = $this->outputter->getPDFLineSpacing();
                // If we can't the headings on the current page, begin a new page.
                if (($this->outputter->pdf->GetY() + $hs) > $this->outputter->pdf->getPageBreakTrigger()) {
                    $this->outputter->flushPDF();
                    $this->outputter->pdf->AddPage();
                    $this->outputter->pageNumber++;
                    $newPage = true;
                }
            }

            if ($newPage) {
                // Output title, if present.
                // Calculate larger line spacing of title and page number.
                $ls = 0;
                if ($this->title != '') {
                    $this->outputter->pdf->SetFont(
                        $this->pdfTitleFontFamily,
                        $this->pdfTitleFontStyle,
                        $this->pdfTitleFontSize
                    );
                    $ls = max($ls, $this->outputter->getPDFLineSpacing());
                    $this->outputter->setPDFRGBTextColor($this->pdfTitleTextRGB);
                    $spc = 4;
                    $this->outputter->pdf->Cell(
                        $this->outputter->getPDFPageWidth()-($this->pdfPageNumWidth+$spc),
                        $this->outputter->getPDFLineSpacing(),
                        $this->title,
                        0,
                        0,
                        'L'
                    );
                    $this->outputter->pdf->SetX($this->outputter->pdf->GetX() + $spc);
                    unset($spc);
                } else {
                    $this->outputter->pdf->SetX($this->outputter->pdf->GetX() + $this->outputter->getPDFPageWidth()-$this->pdfPageNumWidth);
                }

                // Output page number.
                // Calculate larger line spacing of title and page number.
                $this->outputter->pdf->SetFont(
                    $this->pdfPageNumFontFamily,
                    $this->pdfPageNumFontStyle,
                    $this->pdfPageNumFontSize
                );
                $ls = max($ls, $this->outputter->getPDFLineSpacing());
                $this->outputter->setPDFRGBTextColor($this->pdfPageNumTextRGB);
                $this->outputter->pdf->Cell(
                    $this->pdfPageNumWidth,
                    $this->outputter->getPDFLineSpacing(),
                    'Page '.$this->outputter->pageNumber,
                    0,
                    0,
                    'R'
                );

                // Move down to the correct position for the column headings.
                $this->outputter->pdf->Ln($ls);
            }    // if ($newPage)

            // Prepare to output column headings.
            $this->outputter->pdf->SetFont(
                $this->pdfHeadingFontFamily,
                $this->pdfHeadingFontStyle,
                $this->pdfHeadingFontSize
            );

            $pdfTotalColSpacing = $this->pdfColumnSpacing*(count($this->columns)-1);

            $totalRelativeWidth = 0.0;
            foreach ($this->columns as &$col) $totalRelativeWidth += $col->relativeWidth;
            unset($col);    // Release reference to last element
            break;
        }

        $rowData = '';
        $sep = '';
        $colNum = -1;

        if ($this->outputter->outputFormat == 'html') {
            $this->ensureHTMLTableSection('thead');
            $rowData .=
                '<tr'.
                (($this->htmlTHeadTRClass != '') ? ' class="'.$this->htmlTHeadTRClass.'"' : '').
                '>';
        }

        foreach ($this->columns as &$col) {
            $colNum++;
            switch ($this->outputter->outputFormat) {
            case 'html':
                $rowData .=
                    '<th'.
                    (($this->htmlTHeadTHClass != '') ? ' class="'.$this->htmlTHeadTHClass.'"' : '').
                    (($this->htmlTHeadTHNoWrap != '') ? ' nowrap="'.$this->htmlTHeadTHNoWrap.'"' : '').
                    (($this->htmlTHeadTHAlign != '') ? ' align="'.$this->htmlTHeadTHAlign.'"' : '').
                    '>'.htmlspecialchars($col->heading).'</th>';
                break;
            case 'csv':
                $rowData .= $sep.ReportOutputter::encodeCSV($col->heading);
                if ($sep == '') $sep = ',';
                break;
            case 'tsv':
                $rowData .= $sep.ReportOutputter::encodeTSV($col->heading);
                if ($sep == '') $sep = "\t";
                break;
            case 'xls':
            case 'xlsx':
            case 'ods':
                $cell = $this->outputter->worksheet->getCellByColumnAndRow($colNum, $this->outputter->worksheetRowNum);
                $cell->setValueExplicit($col->heading, DataType::TYPE_STRING);
                $style = $this->outputter->worksheet->getStyleByColumnAndRow($colNum, $this->outputter->worksheetRowNum);
                $style->getAlignment()->setHorizontal('center');
                $style->getFont()->setBold(true);
                break;
            case 'pdf':
                $colWidth = (int)
                    (((double)$col->relativeWidth/(double)$totalRelativeWidth) *
                     (double)($this->outputter->getPDFPageWidth()-$pdfTotalColSpacing));
                $this->outputter->setPDFRGBDrawColor($this->pdfHeadingBorderRGB);
                $this->outputter->setPDFRGBTextColor($this->pdfHeadingTextRGB);
                if ($this->pdfHeadingBackgroundFill) {
                    $this->outputter->setPDFRGBFillColor($this->pdfHeadingBackgroundRGB);
                    $this->outputter->pdf->Cell(
                        $colWidth,
                        $this->outputter->getPDFLineSpacing(),
                        $col->heading,
                        $this->pdfHeadingBorder,
                        0,
                        'C',
                        1
                    );
                } else {
                    $this->outputter->pdf->Cell(
                        $colWidth,
                        $this->outputter->getPDFLineSpacing(),
                        $col->heading,
                        $this->pdfHeadingBorder,
                        0,
                        'C'
                    );
                }
                $this->outputter->pdf->SetX($this->outputter->pdf->GetX() + $this->pdfColumnSpacing);
                break;
            }
        }
        unset($col);        // Release reference to last element

        switch ($this->outputter->outputFormat) {
        case 'html':
            $rowData .= '</tr>';
            $this->outputter->outputText($rowData);
            break;
        case 'csv':
        case 'tsv':
            $rowData .= "\r\n";
            $this->outputter->outputText($rowData);
            break;
        case 'xls':
        case 'xlsx':
        case 'ods':
            $this->outputter->worksheetRowNum++;
            break;
        case 'pdf':
            $this->outputter->pdf->Ln();
            break;
        }

        $this->headingOutput = true;
    }

    public function outputRow(&$row) {
        $allowSuppression = true;
        $justOutputHeading = false;

        if ($this->outputter->outputFormat == 'pdf') {
            // Be sure the row will fit on this page; if not (or if the PDF document
            // has not been opened yet), go to next page and output heading.
            if ($this->outputter->pdf === null) {
                $this->outputHeading(true);
                $justOutputHeading = true;
                $allowSuppression = false;
            } else {
                $this->outputter->pdf->SetFont(
                    $this->pdfDetailFontFamily,
                    $this->pdfDetailFontStyle,
                    $this->pdfDetailFontSize
                );
                if ((($this->outputter->pdf->GetY() + $this->outputter->getPDFLineSpacing()) > $this->outputter->pdf->getPageBreakTrigger())) {
                    $this->outputHeading(true);
                    $justOutputHeading = true;
                    $allowSuppression = false;
                }
            }
        }

        // If we need a heading, output it now, but don't force a page break.
        if (!$this->headingOutput) {
            $this->outputHeading();
            $justOutputHeading = true;
            $allowSuppression = false;
        }

        $this->oddRow = !$this->oddRow;

        // Create a copy of $row into $crow, calculating any calculated fields.
        $crow = new stdClass();
        foreach ($this->columns as $col) {
            $colName = $col->name;
            // If this is a calculated column, call the function to calculate the value.
            // Otherwise, just get the value.
            if ($col->valueCalcCallback !== null) {
                $colval = call_user_func(
                    $col->valueCalcCallback,
                    $row,
                    $this,
                    $col,
                    null
                );
            } else {
                $colval = isset($row->$colName) ? $row->$colName : '';
            }

            $crow->$colName = $colval;
        }

        $needAnotherHeading = false;

        switch ($this->outputter->outputFormat) {
        case 'csv':
        case 'tsv':
            if ($this->alwaysOutputUniqueIdColumnsInCSVOrTSVFormat) $allowSuppression = false;
            break;
        case 'html':
            if ($this->alwaysOutputUniqueIdColumnsInHTMLFormat) $allowSuppression = false;
            break;
        case 'pdf':
            if ($this->alwaysOutputUniqueIdColumnsInPDFFormat) $allowSuppression = false;
            break;
        case 'xls':
        case 'xlsx':
        case 'ods':
            if ($this->alwaysOutputUniqueIdColumnsInXLSFormat) $allowSuppression = false;
            break;
        }

        $colNamesToSuppress = array();
        $totalsPrintLevel = null;
        foreach ($this->levels as $level) {
            $cns = array();
            foreach ($this->columns as $col) {
                $colName = $col->name;
                if (in_array($colName, $level->uniqueIdColumnNames)) {
                    if ((!isset($this->prevIdValues[$colName])) ||
                        ($crow->$colName != $this->prevIdValues[$colName])) {
                        $totalsPrintLevel = &$level;
                        $allowSuppression = false;
                        if ((!$justOutputHeading) && ($level->reOutputHeadingAfterEachLevelTotal)) {
                            $needAnotherHeading = true;
                        }
                        break;
                    }
                    $cns[] = $colName;
                }
            }
            if ($allowSuppression) {
                foreach ($cns as $colName) {
                    if (!in_array($colName, $colNamesToSuppress)) {
                        $colNamesToSuppress[] = $colName;
                    }
                }
            }
        }

        if ($totalsPrintLevel !== null) {
            for ($levelIdx = count($this->levels)-1; $levelIdx >= 0; $levelIdx--) {
                $this->outputAndResetTotals($levelIdx);
                if ($this->levels[$levelIdx] == $totalsPrintLevel) break;
            }
            if ($needAnotherHeading) {
                // If we need another heading based on heading re-output rules, output it now.
                // NOTE: This should NEVER occur if we just output a heading.
                $this->outputHeading();
                $justOutputHeading = true;
                $allowSuppression = false;
                $this->oddRow = true;
            }
        }

        if ($this->outputter->outputFormat == 'pdf') {
            $this->outputter->pdf->SetFont(
                $this->pdfDetailFontFamily,
                $this->pdfDetailFontStyle,
                $this->pdfDetailFontSize
            );
            $this->outputter->setPDFRGBFillColor(
                $this->oddRow ?
                    $this->pdfDetailBackgroundOddRGB : $this->pdfDetailBackgroundEvenRGB
            );
            $this->outputter->setPDFRGBTextColor($this->pdfDetailTextRGB);

            $pdfTotalColSpacing = $this->pdfColumnSpacing*(count($this->columns)-1);

            $totalRelativeWidth = 0.0;
            foreach ($this->columns as &$col) $totalRelativeWidth += $col->relativeWidth;
            unset($col);    // Release reference to last element
        }

        $sep = '';
        $rowData = '';
        $colNum = -1;

        if ($this->outputter->outputFormat == 'html') {
            $this->ensureHTMLTableSection('tbody');
            $cls = trim(
                $this->htmlTBodyTRClass.' '.
                ($this->oddRow ? $this->htmlTBodyTROddClass : $this->htmlTBodyTREvenClass)
            );
            $rowData .= '<tr'.(($cls != '') ? ' class="'.$cls.'"' : '').'>';
            unset($cls);
        }

        // Output data columns.
        foreach ($this->columns as &$col) {
            $colNum++;
            $colName = $col->name;

            $colval = $crow->$colName;

            if (!in_array($colName, $colNamesToSuppress)) {
                switch ($col->format) {
                case 'number':
                    if ((($colval < 0) && ($col->suppressNegative)) ||
                        (($colval == 0) && ($col->suppressZero)) ||
                        (($colval > 0) && ($col->suppressPositive))) {
                        $dispval = '';
                    } else {
                        if ($col->formatCallback !== null) {
                            $dispval = call_user_func(
                                $col->formatCallback,
                                $row,
                                $this,
                                $col,
                                null,
                                $colval
                            );
                        } else {
                            if (!$col->useThousandsSeparator) {
                                $dispval = number_format((double)$colval, $col->decimalPlaces, '.', '');
                            } else {
                                $dispval = number_format((double)$colval, $col->decimalPlaces);
                            }
                        }
                    }
                    break;
                default:
                    if ($col->formatCallback !== null) {
                        $dispval = call_user_func(
                            $col->formatCallback,
                            $row,
                            $this,
                            $col,
                            null,
                            $colval
                        );
                    } else {
                        $dispval = $colval;
                    }
                    break;
                }
            } else {
                $dispval = '';
            }

            switch ($this->outputter->outputFormat) {
            case 'html':
                $rowData .=
                    '<td'.
                    (($this->htmlTBodyTDClass != '') ? ' class="'.$this->htmlTBodyTDClass.'"' : '').
                    (($this->htmlTBodyTDNoWrap != '') ? ' nowrap="'.$this->htmlTBodyTDNoWrap.'"' : '').
                    ' align="'.$col->align.'">'.
                    ((trim($dispval) != '') ? htmlspecialchars($dispval) : '&nbsp;').
                    '</td>';
                break;
            case 'csv':
                $rowData .= $sep.ReportOutputter::encodeCSV($dispval);
                if ($sep == '') $sep = ',';
                break;
            case 'tsv':
                $rowData .= $sep.ReportOutputter::encodeTSV($dispval);
                if ($sep == '') $sep = "\t";
                break;
            case 'xls':
            case 'xlsx':
            case 'ods':
                $cell = $this->outputter->worksheet->getCellByColumnAndRow($colNum, $this->outputter->worksheetRowNum);
                $cell->setValueExplicit($dispval, DataType::TYPE_STRING);
                $style = $this->outputter->worksheet->getStyleByColumnAndRow($colNum, $this->outputter->worksheetRowNum);
                $style->getAlignment()->setHorizontal($col->align);
                break;
            case 'pdf':
                $colWidth = (int)
                    (((double)$col->relativeWidth/(double)$totalRelativeWidth) *
                     (double)($this->outputter->getPDFPageWidth()-$pdfTotalColSpacing));
                $this->outputter->pdf->Cell(
                    $colWidth,
                    $this->outputter->getPDFLineSpacing(),
                    $dispval,
                    0,
                    0,
                    strtoupper($col->align[0]),
                    1
                );
                $this->outputter->pdf->SetX($this->outputter->pdf->GetX() + $this->pdfColumnSpacing);
                break;
            }

            foreach ($this->levels as $level) {
                if (in_array($colName, $level->uniqueIdColumnNames)) {
                    $this->prevIdValues[$colName] = $colval;
                    break;
                }
            }
        }
        unset($col);        // Release reference to last element

        switch ($this->outputter->outputFormat) {
        case 'html':
            $rowData .= '</tr>';
            $this->outputter->outputText($rowData);
            break;
        case 'csv':
        case 'tsv':
            $rowData .= "\r\n";
            $this->outputter->outputText($rowData);
            break;
        case 'xls':
        case 'xlsx':
        case 'ods':
            $this->outputter->worksheetRowNum++;
            break;
        case 'pdf':
            $this->outputter->pdf->Ln();
            break;
        }

        $this->accumulateTotals($crow);
    }

    // Output a custom row.  This can be useful for things like section headings.
    // Parameters:
    // $customColumns: An array of ReportCustomColumn instances.
    // $columnTexts: An array of pre-formatte strings to go into the custom columns.
    // $appearance: Controls the appearance of the text (mainly the font).  Must be
    // one of 'heading', 'detail' or 'totals'.  Optional.  Defaults to 'detail'.
    public function outputCustomRow(
        $customColumns,
        $columnTexts = array(),
        $appearance = 'detail') {

        switch ($this->outputter->outputFormat) {
        case 'html':
            switch ($appearance) {
            case 'heading':
                $tdTag = 'th';
                $htmlTRClass = $this->htmlTHeadTRClass;
                $htmlTDClass = $this->htmlTHeadTHClass;
                $htmlTDNoWrap = $this->htmlTHeadTHNoWrap;
                break;
            case 'detail':
            default:
                $tdTag = 'td';
                $htmlTRClass = trim($this->htmlTBodyTRClass.' '.$this->htmlTBodyTREvenClass);
                $htmlTDClass = $this->htmlTBodyTDClass;
                $htmlTDNoWrap = $this->htmlTBodyTDNoWrap;
                break;
            case 'totals':
                $tdTag = 'td';
                $htmlTRClass = $this->htmlTBodyTotalsTRClass;
                $htmlTDClass = $this->htmlTBodyTotalsTDClass;
                $htmlTDNoWrap = $this->htmlTBodyTotalsTDNoWrap;
                break;
            }
            break;
        case 'pdf':
            switch ($appearance) {
            case 'heading':
                $pdfFontFamily = $this->pdfHeadingFontFamily;
                $pdfFontStyle = $this->pdfHeadingFontStyle;
                $pdfFontSize = $this->pdfHeadingFontSize;
                $pdfTextRGB = $this->pdfHeadingTextRGB;
                $pdfBackgroundFill = $this->pdfHeadingBackgroundFill;
                $pdfBackgroundRGB = $this->pdfHeadingBackgroundRGB;
                $pdfBorder = $this->pdfHeadingBorder;
                $pdfBorderRGB = $this->pdfHeadingBorderRGB;
                break;
            case 'detail':
            default:
                $pdfFontFamily = $this->pdfDetailFontFamily;
                $pdfFontStyle = $this->pdfDetailFontStyle;
                $pdfFontSize = $this->pdfDetailFontSize;
                $pdfTextRGB = $this->pdfDetailTextRGB;
                $pdfBackgroundFill = false;
                $pdfBackgroundRGB = $this->pdfDetailBackgroundEvenRGB;
                $pdfBorder = 0;
                $pdfBorderRGB = null;
                break;
            case 'totals':
                $pdfFontFamily = $this->pdfTotalsFontFamily;
                $pdfFontStyle = $this->pdfTotalsFontStyle;
                $pdfFontSize = $this->pdfTotalsFontSize;
                $pdfTextRGB = $this->pdfTotalsTextRGB;
                $pdfBackgroundFill = false;
                $pdfBackgroundRGB = null;
                $pdfBorder = 0;
                $pdfBorderRGB = null;
                break;
            }
            break;
        }

        if ($this->outputter->outputFormat == 'pdf') {
            // Be sure the row will fit on this page; if not (or if the PDF document
            // has not been opened yet), go to next page and output heading.
            if ($this->outputter->pdf === null) {
                $this->outputHeading(true);
            } else {
                $this->outputter->pdf->SetFont(
                    $pdfFontFamily,
                    $pdfFontStyle,
                    $pdfFontSize
                );
                if (($this->outputter->pdf->GetY() + $this->outputter->getPDFLineSpacing()) > $this->outputter->pdf->getPageBreakTrigger()) {
                    $this->outputHeading(true);
                }
            }
        }

        // If we need a heading, output it now, but don't force a page break.
        if (!$this->headingOutput) {
            $this->outputHeading();
        }

        $sep = '';
        $rowData = '';
        $colNum = -1;

        if ($this->outputter->outputFormat == 'html') {
            $this->ensureHTMLTableSection(($appearance == 'heading') ? 'thead' : 'tbody');
            $rowData .=
                '<tr'.(($htmlTRClass != '') ? ' class="'.$htmlTRClass.'"' : '').'>';
        }

        if ($this->outputter->outputFormat == 'pdf') {
            $this->outputter->pdf->SetFont($pdfFontFamily, $pdfFontStyle, $pdfFontSize);
            $this->outputter->setPDFRGBTextColor($this->pdfDetailTextRGB);

            $pdfTotalColSpacing = $this->pdfColumnSpacing*(count($this->columns)-1);

            $totalRelativeWidth = 0.0;
            foreach ($this->columns as &$col) $totalRelativeWidth += $col->relativeWidth;
            unset($col);    // Release reference to last element
        }

        // Output columns.
        for ($cci = 0, $ci = 0, $ncc = count($customColumns), $nc = count($this->columns);
            ( ($cci < $ncc) && ($ci < $nc) );
            $cci++, $ci += $columnSpan) {

            $text = isset($columnTexts[$cci]) ? $columnTexts[$cci] : '';
            $align = $customColumns[$cci]->align;
            $columnSpan = $customColumns[$cci]->columnSpan;
            if (($ci+$columnSpan) > $nc) $columnSpan = $nc-$ci;
            if ($columnSpan <= 0) break;

            switch ($this->outputter->outputFormat) {
            case 'html':
                $rowData .=
                    '<'.$tdTag.
                    (($htmlTDClass != '') ? ' class="'.$htmlTDClass.'"' : '').
                    (($htmlTDNoWrap != '') ? ' nowrap="'.$htmlTDNoWrap.'"' : '').
                    ' align="'.$align.'"'.
                    (($columnSpan > 1) ? (' colspan="'.$columnSpan.'"') : '').
                    '>'.
                    ((trim($text) != '') ? htmlspecialchars($text) : '&nbsp;').
                    '</'.$tdTag.'>';
                break;
            case 'csv':
                $rowData .= $sep.ReportOutputter::encodeCSV($text);
                if ($sep == '') $sep = ',';
                for ($cs = 0; $cs < $columnSpan; $cs++) $rowData .= $sep;
                break;
            case 'tsv':
                $rowData .= $sep.ReportOutputter::encodeTSV($text);
                if ($sep == '') $sep = "\t";
                for ($cs = 0; $cs < $columnSpan; $cs++) $rowData .= $sep;
                break;
            case 'xls':
            case 'xlsx':
            case 'ods':
                $cell = $this->outputter->worksheet->getCellByColumnAndRow($ci, $this->outputter->worksheetRowNum);
                $cell->setValueExplicit($text, DataType::TYPE_STRING);
                $style = $this->outputter->worksheet->getStyleByColumnAndRow($ci, $this->outputter->worksheetRowNum);
                $style->getAlignment()->setHorizontal($align);
                if (($appearance == 'heading') || ($appearance == 'totals')) {
                    $style->getFont()->setBold(true);
                }
                if ($columnSpan > 0) {
                    $this->outputter->worksheet->mergeCellsByColumnAndRow(
                        $ci,
                        $this->outputter->worksheetRowNum,
                        $ci+($columnSpan-1),
                        $this->outputter->worksheetRowNum
                    );
                }
                break;
            case 'pdf':
                $colWidth = 0;
                for ($ci2 = $ci, $nci = $ci+$columnSpan; $ci2 < $nci; $ci2++) {
                    $col2 = &$this->columns[$ci2];
                    $colWidth += (int)
                        (((double)$col2->relativeWidth/(double)$totalRelativeWidth) *
                         (double)($this->outputter->getPDFPageWidth()-$pdfTotalColSpacing));
                    if ($ci2 > $ci) $colWidth += $this->pdfColumnSpacing;
                }
                unset($col2);        // Release reference to last element

                $this->outputter->setPDFRGBDrawColor($pdfBorderRGB);
                $this->outputter->setPDFRGBTextColor($pdfTextRGB);
                if ($pdfBackgroundFill) {
                    $this->outputter->setPDFRGBFillColor($pdfBackgroundRGB);
                    $this->outputter->pdf->Cell(
                        $colWidth,
                        $this->outputter->getPDFLineSpacing(),
                        $text,
                        $pdfBorder,
                        0,
                        'C',
                        1
                    );
                } else {
                    $this->outputter->pdf->Cell(
                        $colWidth,
                        $this->outputter->getPDFLineSpacing(),
                        $text,
                        $pdfBorder,
                        0,
                        strtoupper($align[0])
                    );
                }
                $this->outputter->pdf->SetX($this->outputter->pdf->GetX() + $this->pdfColumnSpacing);
                break;
            }
        }

        switch ($this->outputter->outputFormat) {
        case 'html':
            $rowData .= '</tr>';
            $this->outputter->outputText($rowData);
            break;
        case 'csv':
        case 'tsv':
            $rowData .= "\r\n";
            $this->outputter->outputText($rowData);
            break;
        case 'xls':
        case 'xlsx':
        case 'ods':
            $this->outputter->worksheetRowNum++;
            break;
        case 'pdf':
            $this->outputter->pdf->Ln();
            break;
        }

        // Force the next detail row to be odd.
        $this->oddRow = true;
    }

    public function outputAndResetTotals($levelIdx) {
        if ((!$this->outputTotalsInCSVOrTSVFormat) &&
            (($this->outputter->outputFormat == 'csv') ||
             ($this->outputter->outputFormat == 'tsv'))) {
            return;
        }

        $level = &$this->levels[$levelIdx];
        $levelName = $level->name;
        if (!isset($this->totals[$levelName])) return;
        $tot = $this->totals[$levelName];

        if ($this->outputter->outputFormat == 'pdf') {
            // Be sure the row will fit on this page; if not (or if the PDF document
            // has not been opened yet), go to next page and output heading.
            if ($this->outputter->pdf === null) {
                $this->outputHeading(true);
            } else {
                $this->outputter->pdf->SetFont(
                    $this->pdfTotalsFontFamily,
                    $this->pdfTotalsFontStyle,
                    $this->pdfTotalsFontSize
                );
                if (($this->outputter->pdf->GetY() + $this->outputter->getPDFLineSpacing()) > $this->outputter->pdf->getPageBreakTrigger()) {
                    $this->outputHeading(true);
                }
            }
        }

        // If we need a heading, output it now, but don't force a page break.
        if (!$this->headingOutput) {
            $this->outputHeading();
        }

        // Create a copy of $tot into $crow, calculating any calculated fields.
        $crow = new stdClass();
        foreach ($this->columns as &$col) {
            $colName = $col->name;
            if (!in_array($levelName, $col->outputTotalsAtLevels)) continue;

            // If this is a calculated column, call the function to calculate the value.
            // Otherwise, just get the value.
            if ($col->valueCalcCallback !== null) {
                $colval = call_user_func(
                    $col->valueCalcCallback,
                    $tot,
                    $this,
                    $col,
                    $level
                );
            } else {
                $colval = $tot->$colName;
            }

            $crow->$colName = $colval;
        }
        unset($col);        // Release reference to last element

        $sep = '';
        $rowData = '';
        $colNum = -1;

        if ($this->outputter->outputFormat == 'html') {
            $this->ensureHTMLTableSection('tbody');
            $rowData .=
                '<tr'.
                (($this->htmlTBodyTotalsTRClass != '') ?
                    ' class="'.$this->htmlTBodyTotalsTRClass.'"' : '').
                '>';
        }

        if ($this->outputter->outputFormat == 'pdf') {
            $this->outputter->pdf->SetFont(
                $this->pdfTotalsFontFamily,
                $this->pdfTotalsFontStyle,
                $this->pdfTotalsFontSize
            );
            $this->outputter->setPDFRGBTextColor($this->pdfTotalsTextRGB);

            $pdfTotalColSpacing = $this->pdfColumnSpacing*(count($this->columns)-1);

            $totalRelativeWidth = 0.0;
            foreach ($this->columns as &$col) $totalRelativeWidth += $col->relativeWidth;
            unset($col);    // Release reference to last element
        }

        // Output totals columns.
        for ($ci = 0, $nc = count($this->columns); $ci < $nc; $ci++) {
            $col = &$this->columns[$ci];
            $colNum++;
            $colName = $col->name;

            if ($colName == $level->totalsDescriptionLeftColumnName) {
                $nTotDescCols = $level->totalsDescriptionColumnSpan;
                switch ($this->outputter->outputFormat) {
                case 'html':
                    $rowData .=
                        '<td'.
                        (($this->htmlTBodyTotalsTDClass != '') ? ' class="'.$this->htmlTBodyTotalsTDClass.'"' : '').
                        (($this->htmlTBodyTotalsTDNoWrap != '') ? ' nowrap="'.$this->htmlTBodyTotalsTDNoWrap.'"' : '').
                        ' align="'.$level->totalsDescriptionAlign.'"'.
                        (($nTotDescCols > 1) ? (' colspan="'.$nTotDescCols.'"') : '').
                        '>'.htmlspecialchars($level->totalsDescription).':</td>';
                    break;
                case 'csv':
                    $rowData .= $sep.ReportOutputter::encodeCSV($level->totalsDescription.':');
                    if ($sep == '') $sep = ',';
                    for ($i = 1; $i < $nTotDescCols; $i++) $rowData .= $sep;
                    break;
                case 'tsv':
                    $rowData .= $sep.ReportOutputter::encodeTSV($level->totalsDescription.':');
                    if ($sep == '') $sep = "\t";
                    for ($i = 1; $i < $nTotDescCols; $i++) $rowData .= $sep;
                    break;
                case 'xls':
                case 'xlsx':
                case 'ods':
                    $cell = $this->outputter->worksheet->getCellByColumnAndRow($colNum, $this->outputter->worksheetRowNum);
                    $cell->setValueExplicit($level->totalsDescription.':', DataType::TYPE_STRING);
                    $style = $this->outputter->worksheet->getStyleByColumnAndRow($colNum, $this->outputter->worksheetRowNum);
                    $style->getAlignment()->setHorizontal($level->totalsDescriptionAlign);
                    $style->getFont()->setBold(true);
                    if ($nTotDescCols > 1) {
                        $this->outputter->worksheet->mergeCellsByColumnAndRow(
                            $colNum,
                            $this->outputter->worksheetRowNum,
                            $colNum+($nTotDescCols-1),
                            $this->outputter->worksheetRowNum
                        );
                    }
                    break;
                case 'pdf':
                    $colWidth = 0;
                    for ($ci2 = $ci, $nci = $ci+$nTotDescCols; $ci2 < $nci; $ci2++) {
                        $col2 = &$this->columns[$ci2];
                        $colWidth += (int)
                            (((double)$col2->relativeWidth/(double)$totalRelativeWidth) *
                             (double)($this->outputter->getPDFPageWidth()-$pdfTotalColSpacing));
                        if ($ci2 > $ci) $colWidth += $this->pdfColumnSpacing;
                    }
                    unset($col2);        // Release reference to last element
                    $this->outputter->pdf->Cell(
                        $colWidth,
                        $this->outputter->getPDFLineSpacing(),
                        $level->totalsDescription.':',
                        0,
                        0,
                        strtoupper($level->totalsDescriptionAlign[0])
                    );
                    $this->outputter->pdf->SetX($this->outputter->pdf->GetX() + $this->pdfColumnSpacing);
                    break;
                }
                $ci += ($nTotDescCols-1);
                $colNum += ($nTotDescCols-1);
            } else {    // if ($colName == $level->totalsDescriptionLeftColumnName)
                $isTotCol = in_array($levelName, $col->outputTotalsAtLevels);
                if ($isTotCol) {
                    $colval = $crow->$colName;
                    switch ($col->format) {
                    case 'number':
                        if ((($colval < 0) && ($col->suppressNegative)) ||
                            (($colval == 0) && ($col->suppressZero)) ||
                            (($colval > 0) && ($col->suppressPositive))) {
                            $dispval = '';
                        } else {
                            if ($col->formatCallback !== null) {
                                $dispval = call_user_func(
                                    $col->formatCallback,
                                    $tot,
                                    $this,
                                    $col,
                                    $level,
                                    $colval
                                );
                            } else {
                                if (!$col->useThousandsSeparator) {
                                    $dispval = number_format((double)$colval, $col->decimalPlaces, '.', '');
                                } else {
                                    $dispval = number_format((double)$colval, $col->decimalPlaces);
                                }
                            }
                        }
                        break;
                    default:
                        if ($col->formatCallback !== null) {
                            $dispval = call_user_func(
                                $col->formatCallback,
                                $tot,
                                $this,
                                $col,
                                $level,
                                $colval
                            );
                        } else {
                            $dispval = $colval;
                        }
                        break;
                    }
                } else {
                    $dispval = '';
                }

                switch ($this->outputter->outputFormat) {
                case 'html':
                    $rowData .=
                        '<td'.
                        (($this->htmlTBodyTotalsTDClass != '') ? ' class="'.$this->htmlTBodyTotalsTDClass.'"' : '').
                        (($this->htmlTBodyTotalsTDNoWrap != '') ? ' nowrap="'.$this->htmlTBodyTotalsTDNoWrap.'"' : '').
                        ' align="'.$col->align.'">'.
                        ((trim($dispval) != '') ? htmlspecialchars($dispval) : '&nbsp;').
                        '</td>';
                    break;
                case 'csv':
                    $rowData .= $sep.ReportOutputter::encodeCSV($dispval);
                    if ($sep == '') $sep = ',';
                    break;
                case 'tsv':
                    $rowData .= $sep.ReportOutputter::encodeTSV($dispval);
                    if ($sep == '') $sep = "\t";
                    break;
                case 'xls':
                case 'xlsx':
                case 'ods':
                    if (!$isTotCol) break;
                    $cell = $this->outputter->worksheet->getCellByColumnAndRow($colNum, $this->outputter->worksheetRowNum);
                    $cell->setValueExplicit($dispval, DataType::TYPE_STRING);
                    $style = $this->outputter->worksheet->getStyleByColumnAndRow($colNum, $this->outputter->worksheetRowNum);
                    $style->getAlignment()->setHorizontal($col->align);
                    $style->getFont()->setBold(true);
                    break;
                case 'pdf':
                    $colWidth = (int)
                        (((double)$col->relativeWidth/(double)$totalRelativeWidth) *
                         (double)($this->outputter->getPDFPageWidth()-$pdfTotalColSpacing));
                    $this->outputter->pdf->Cell(
                        $colWidth,
                        $this->outputter->getPDFLineSpacing(),
                        $dispval,
                        0,
                        0,
                        strtoupper($col->align[0])
                    );
                    $this->outputter->pdf->SetX($this->outputter->pdf->GetX() + $this->pdfColumnSpacing);
                    break;
                }
            }    // if ($colName == $level->totalsDescriptionLeftColumnName) ... else
        }
        unset($col);        // Release reference to last element

        switch ($this->outputter->outputFormat) {
        case 'html':
            $rowData .= '</tr>';
            $this->outputter->outputText($rowData);
            break;
        case 'csv':
        case 'tsv':
            $rowData .= "\r\n";
            $this->outputter->outputText($rowData);
            break;
        case 'xls':
        case 'xlsx':
        case 'ods':
            $this->outputter->worksheetRowNum++;
            break;
        case 'pdf':
            $this->outputter->pdf->Ln();
            break;
        }

        // Reset totals for this level by unsetting the object.
        unset($this->totals[$levelName]);

        // Force the next detail row to be odd.
        $this->oddRow = true;
    }

    public function resetTotalsForLevelIdx($levelIdx) {
        // Reset totals for this level by unsetting the object.
        unset($this->totals[$this->levels[$levelIdx]->name]);
    }

    public function resetTotalsForLevelName($levelName) {
        // Reset totals for this level by unsetting the object.
        unset($this->totals[$levelName]);
    }

    public function finish() {
        if (!$this->headingOutput) {
            // If we need a heading, output it now, but don't force a page break.
            $this->outputHeading();
        }

        // Output pending totals.
        for ($levelIdx = count($this->levels)-1; $levelIdx >= 0; $levelIdx--) {
            $this->outputAndResetTotals($levelIdx);
        }

        // Output the end of the report.
        if ($this->outputter->outputFormat == 'html') {
            $this->ensureHTMLTableClosed();
        }
    }

    protected function accumulateTotals($row) {
        if ((!$this->outputTotalsInCSVOrTSVFormat) &&
            (($this->outputter->outputFormat == 'csv') ||
             ($this->outputter->outputFormat == 'tsv'))) {
            return;
        }

        foreach ($this->columns as $col) {
            $colName = $col->name;
            foreach ($col->outputTotalsAtLevels as $levelName) {
                if (!isset($this->totals[$levelName])) $this->totals[$levelName] = new stdClass();
                $this->totals[$levelName]->$colName = round(
                    (isset($this->totals[$levelName]->$colName) ?
                        $this->totals[$levelName]->$colName : 0.0) +
                    (isset($row->$colName) ? $row->$colName : 0.0),
                    $col->decimalPlaces
                );
            }
        }
    }

    protected function ensureHTMLTableOpen() {
        if (($this->outputter->outputFormat == 'html') && (!$this->htmlTableOpen)) {
            $this->htmlTableOpen = true;
            if (($this->outputter->outputFormat == 'html') &&
                ($this->outputCompleteHTMLDocument)) {
                $this->outputter->outputText(
                    '<html><head><title>'.htmlspecialchars($this->title).
                    '</title></head><body>'
                );
            }
            $html = '<table';
            if ($this->htmlTableBorder != '') {
                $html .= ' border="'.$this->htmlTableBorder.'"';
            }
            if ($this->htmlTableCellSpacing != '') {
                $html .= ' cellspacing="'.$this->htmlTableCellSpacing.'"';
            }
            if ($this->htmlTableCellPadding != '') {
                $html .= ' cellpadding="'.$this->htmlTableCellPadding.'"';
            }
            if ($this->htmlTableClass != '') {
                $html .= ' class="'.$this->htmlTableClass.'"';
            }
            $html .= '>';
            $this->outputter->outputText($html);
        }
    }

    protected function ensureHTMLTableClosed() {
        if (($this->outputter->outputFormat == 'html') && ($this->htmlTableOpen)) {
            $this->ensureHTMLTableSection(null);
            $this->outputter->outputText('</table>');
            if (($this->outputter->outputFormat == 'html') &&
                ($this->outputCompleteHTMLDocument)) {
                $this->outputter->outputText('</body></html>');
            }
            $this->htmlTableOpen = false;
        }
    }

    protected function ensureHTMLTableSection($section) {
        if ($this->outputter->outputFormat == 'html') {
            switch ($section) {
            case 'thead':
                $this->ensureHTMLTableOpen();
                if ($this->htmlTableSection != $section) {
                    $this->ensureHTMLTableSection(null);
                    $this->outputter->outputText(
                        '<thead'.
                        (($this->htmlTHeadClass != '') ? ' class="'.$this->htmlTHeadClass.'"' : '').
                        '>'
                    );
                    $this->htmlTableSection = $section;
                }
                break;
            case 'tbody':
                $this->ensureHTMLTableOpen();
                if ($this->htmlTableSection != $section) {
                    $this->ensureHTMLTableSection(null);
                    $this->outputter->outputText(
                        '<tbody'.
                        (($this->htmlTBodyClass != '') ? ' class="'.$this->htmlTBodyClass.'"' : '').
                        '>'
                    );
                    $this->htmlTableSection = $section;
                }
                break;
            default:
                if (($this->htmlTableOpen) &&
                    ($this->htmlTableSection !== null) &&
                    ($this->htmlTableSection != '')) {
                    $this->outputter->outputText('</'.$this->htmlTableSection.'>');
                }
                $this->htmlTableSection = null;
                break;
            }
        }
    }
}
