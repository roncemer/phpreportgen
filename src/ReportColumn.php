<?php
namespace Roncemer\PHPReportGen;

class ReportColumn
{
    public $name;
    public $heading;
    public $align;
    public $format;
    public $decimalPlaces;
    public $useThousandsSeparator;
    public $suppressNegative;
    public $suppressZero;
    public $suppressPositive;
    public $relativeWidth;
    public $outputTotalsAtLevels;
    public $valueCalcCallback;
    public $formatCallback;

    // Construct a ReportColumn instance.
    // Parameters:
    // $params: An assocative array of parameter names to their values.
    //
    // Recognized paramters:
    // name: The mnemonic field name for the column, used to retrieve data from the data object.
    // heading: The column heading which is outputted at the top of the column.
    // align: The column alignment; one of 'left', 'center' or 'right'.
    //     Optional.  Defaults to 'left'.
    // format: The output format.  One of 'string', 'number'.
    //     Optional.  Defaults to 'string'.
    // decimalPlaces: For output formats of type 'number', this is the number of decimal places.
    //     Optional.  Defaults to 0.
    // useThousandsSeparator: For output formats of type 'number', this allows the caller to turn
    //     on or off the thousands separator (typically a comma [,] character).
    //     Optional.  Defaults to true.
    // suppressNegative: true to suppress negative values; false to not (only used for numeric
    //     format columns).
    // suppressZero: true to suppress zero values; false to not (only used for numeric
    //     format columns).
    // suppressPositive: true to suppress positive values; false to not (only used for numeric
    //     format columns).
    // relativeWidth: A relative width (relative to the other columns).  This is only used in
    //     PDF format.
    //     Optional.  Defaults to 1.0.
    // outputTotalsAtLevels: An array of strings containing report level names at which
    //     to print totals for this column.  This can also be null, false or an empty array.
    //     Optional.  Defaults to null.
    // valueCalcCallback: A function callback in the form used for call_user_func().  If this
    //     is set to a valid function callback, the function will be called each time a value
    //     calculation is needed.  This includes when a report row is being output, and when
    //     totals are being output at any level.  The following parameters are passed to the
    //     callback function:
    //         $row: An object containing the row or totals data.
    //         $report: The Report instance.
    //         $column: The ReportColumn instance.
    //         $totalsLevel: The ReportLevel instance for the current totals level being output,
    //             or null if we are currently outputting a regular report row.
    //     The callback function must return the calculated value.
    //     NOTE: The parameters to the callback function are NOT passed by reference, according
    //     to the PHP documentation.  If this is true, this means that changes made to these
    //     objects within the callback function will NOT affect the original objects.
    // formatCallback: A function callback in the form used for call_user_func().  If this
    //     is set to a valid function callback, the function will be called each time a value
    //     is output, in order to do custom formatting for the value just before it is output.
    //     This includes when a report row is being output, and when totals are being output at
    //     any level.  The following parameters are passed to the
    //     callback function:
    //         $row: An object containing the row or totals data.
    //         $report: The Report instance.
    //         $column: The ReportColumn instance.
    //         $totalsLevel: The ReportLevel instance for the current totals level being output,
    //             or null if we are currently outputting a regular report row.
    //         $value: The unformatted value.
    //     The callback function must return the formatted value, in the format that is desired
    //     for final output.
    //     NOTE: The parameters to the callback function are NOT passed by reference, according
    //     to the PHP documentation.  If this is true, this means that changes made to these
    //     objects within the callback function will NOT affect the original objects.
    public function __construct($params) {
        $this->name = isset($params['name']) ? (string)$params['name'] : '';
        $this->heading = isset($params['heading']) ? (string)$params['heading'] : '';
        $this->align = isset($params['align']) ? (string)$params['align'] : 'left';
        if (($this->align != 'left') && ($this->align != 'center') && ($this->align != 'right')) {
            $this->align = 'left';
        }
        $this->format = isset($params['format']) ? (string)$params['format'] : 'string';
        if ( ($this->format != 'string') && ($this->format != 'number') ) {
            $this->format = 'string';
        }
        $this->decimalPlaces = isset($params['decimalPlaces']) ? (int)$params['decimalPlaces'] : 0;
        if ($this->decimalPlaces < 0) $this->decimalPlaces = 0;
        $this->useThousandsSeparator = isset($params['useThousandsSeparator']) ?
            (boolean)$params['useThousandsSeparator'] : true;
        $this->suppressNegative = isset($params['suppressNegative']) ?
            (boolean)$params['suppressNegative'] : false;
        $this->suppressZero = isset($params['suppressZero']) ?
            (boolean)$params['suppressZero'] : false;
        $this->suppressPositive = isset($params['suppressPositive']) ?
            (boolean)$params['suppressPositive'] : false;
        $this->relativeWidth = isset($params['relativeWidth']) ? (double)$params['relativeWidth'] : 1.0;
        $this->outputTotalsAtLevels = isset($params['outputTotalsAtLevels']) ?
            $params['outputTotalsAtLevels'] : array();
        if (!is_array($this->outputTotalsAtLevels)) {
            $this->outputTotalsAtLevels = trim($this->outputTotalsAtLevels);
            $this->outputTotalsAtLevels = ($this->outputTotalsAtLevels != '') ?
                explode(',', $this->outputTotalsAtLevels) : array();
        }
        $this->valueCalcCallback = isset($params['valueCalcCallback']) ?
            $params['valueCalcCallback'] : null;
        $this->formatCallback = isset($params['formatCallback']) ?
            $params['formatCallback'] : null;
    }
}
