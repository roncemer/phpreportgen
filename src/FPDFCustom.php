<?php
namespace Roncemer\PHPReportGen;

use Fpdf\FPDF;

class FPDFCustom extends FPDF
{
    public function getLMargin() {
        return $this->lMargin;
    }

    public function getRMargin() {
        return $this->rMargin;
    }

    public function getPageBreakTrigger() {
        return $this->PageBreakTrigger;
    }

    public function getFontSize() {
        return $this->FontSize;
    }

    public function resetBuffer() {
        $this->buffer = '';
    }
}
