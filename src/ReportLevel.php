<?php
namespace Roncemer\PHPReportGen;

class ReportLevel
{
    public $name;
    public $uniqueIdColumnNames;
    public $totalsDescription;
    public $totalsDescriptionLeftColumnName;
    public $totalsDescriptionColumnSpan;
    public $totalsDescriptionAlign;
    public $reOutputHeadingAfterEachLevelTotal;

    // Construct a ReportLevel instance.
    // Parameters:
    // $params: An assocative array of parameter names to their values.
    //
    // Recognized paramters:
    // name: The unique mnemonic name for the level; used to distinguish it from different
    //     levels.
    // uniqueIdColumnNames: An array of strings containing the column names which
    //     will all contain the same set of values for a group of rows at this level.
    // totalsDescription: The description which is prepended to the total lines at this level.
    //     Optional.  Defaults to empty.
    // totalsDescriptionLeftColumnName: The name of the leftmost column in which the totals
    //     description should be printed.
    //     This MUST be specified if a totals description is specified, or the totals description
    //     won't print.
    //     Optional.  Defaults to empty.
    // totalsDescriptionColumnSpan: The number of columns to use for the totals description,
    //     with the column referenced by $totalsDescriptionLeftColumnName being the leftmost
    //     column of the contiguous span of columns in which the totals description is printed.
    //     Optional.  Defaults to 1.
    // totalsDescriptionAlign: The alignment ('left', 'right', 'center') for the totals description.
    //     Optional.  Defaults to 'left'.
    // reOutputHeadingAfterEachLevelTotal: true to re-output the headings before outputting
    //     the next row following any totals which are output for this level.
    //     Optional.  Defaults to false.
    public function __construct($params) {
        $this->name = isset($params['name']) ? (string)$params['name'] : '';
        $this->uniqueIdColumnNames = isset($params['uniqueIdColumnNames']) ?
            $params['uniqueIdColumnNames'] : array();
        $this->totalsDescription = isset($params['totalsDescription']) ?
            (string)$params['totalsDescription'] : '';
        $this->totalsDescriptionLeftColumnName = isset($params['totalsDescriptionLeftColumnName']) ?
            (string)$params['totalsDescriptionLeftColumnName'] : '';
        $this->totalsDescriptionColumnSpan = isset($params['totalsDescriptionColumnSpan']) ?
            (int)$params['totalsDescriptionColumnSpan'] : 0;
        if ($this->totalsDescriptionColumnSpan < 1) $this->totalsDescriptionColumnSpan = 1;

        $this->totalsDescriptionAlign = isset($params['totalsDescriptionAlign']) ?
            (string)$params['totalsDescriptionAlign'] : 'left';
        if (($this->totalsDescriptionAlign != 'left') &&
            ($this->totalsDescriptionAlign != 'center') &&
            ($this->totalsDescriptionAlign != 'right')) {
            $this->totalsDescriptionAlign = 'left';
        }
        $this->reOutputHeadingAfterEachLevelTotal =
            isset($params['reOutputHeadingAfterEachLevelTotal']) ?
            (boolean)$params['reOutputHeadingAfterEachLevelTotal'] : false;
    }
}
