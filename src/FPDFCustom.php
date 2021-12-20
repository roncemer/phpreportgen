<?php
namespace Roncemer\PHPReportGen;

use Fpdf\Fpdf;

class FPDFCustom extends Fpdf
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
