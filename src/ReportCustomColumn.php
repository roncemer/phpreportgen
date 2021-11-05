<?php
namespace Roncemer\PHPReportGen;

class ReportCustomColumn
{
    public $align;
    public $columnSpan;

    // Construct a ReportCustomColumn instance.
    // Parameters:
    // $params: An assocative array of parameter names to their values.
    //
    // Recognized paramters:
    // align: The column alignment; one of 'left', 'center' or 'right'.
    //     Optional.  Defaults to 'left'.
    // columnSpan: The number of actual columns to occupy within the report grid.
    //     Optional.  Defaults to 1.
    public function __construct($params) {
        $this->align = isset($params['align']) ? (string)$params['align'] : 'left';
        if (($this->align != 'left') && ($this->align != 'center') && ($this->align != 'right')) {
            $this->align = 'left';
        }
        $this->columnSpan = isset($params['columnSpan']) ? (int)$params['columnSpan'] : 0;
        if ($this->columnSpan < 1) $this->columnSpan = 1;
    }
}
