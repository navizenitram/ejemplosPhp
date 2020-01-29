<?php


namespace Structure;

use Spipu\Html2Pdf\Html2Pdf;

/**
 * Implementación de la librería Spipu según el contrato definido en la clase abstracta Html2Pdf
 *
 * https://github.com/spipu/html2pdf
 * Class Html2PdfSpipu
 * @package Utils
 */
final class Html2PdfSpipu extends Html2Pdf
{
    private $html2pdf;

    public function __construct()
    {
        $this->html2pdf = new Html2Pdf('P', 'A4', 'en');
    }

    public function stream(string $html, string $name = ""): bool
    {
        // TODO: Implement stream() method.
    }


    public function generate(string $html, string $filePath): bool
    {
        $this->html2pdf->writeHTML($html);
        $output = $this->html2pdf->output('doc.pdf', 'S');

        $this->makeDir($filePath);

        $result = file_put_contents($filePath, $output);
        return $result;
    }

}