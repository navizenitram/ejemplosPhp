<?php


namespace Service;


use Domain\Html2Pdf;

final class PrintPdf
{
    private $html2Pdf;

    /**
     * PrintPdf constructor.
     * @param Html2Pdf $html2Pdf
     */
    public function __construct(Html2Pdf $html2Pdf)
    {
        $this->html2Pdf = $html2Pdf;
    }

    public function __invoke(string $html): void
    {
        $this->html2Pdf->stream($html);
    }
}