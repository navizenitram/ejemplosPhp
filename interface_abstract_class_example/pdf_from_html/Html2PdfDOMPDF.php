<?php


namespace Structure;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Implementación de la librería Dompdf según el contrato definido en la clase abstracta Html2Pdf
 *
 * Class Html2PdfDOMPDF
 * @package Utils
 */
final class Html2PdfDOMPDF extends Html2Pdf
{
    private $dompdf;

    public function __construct()
    {
        $this->dompdf = new DOMPDF();

    }

    public function stream(string $documentHTML, string $name=""): bool
    {
        $options = new Options();
        $options->setIsRemoteEnabled(true);
        $options->setIsHtml5ParserEnabled(true);
        $this->dompdf->setOptions($options);

        $contxt = stream_context_create([
            'ssl' => [
                'verify_peer' => FALSE,
                'verify_peer_name' => FALSE,
                'allow_self_signed'=> TRUE
            ]
        ]);
        $this->dompdf->setHttpContext($contxt);
        $this->dompdf->loadHtml($documentHTML);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();
        $this->dompdf->stream($name);
    }


    public function generate(string $html, string $filePath): bool
    {

        $this->dompdf->load_html($html);
        $this->dompdf->render();
        $output = $this->dompdf->output(array('compress'=>0));

        $this->makeDir($filePath);

        $result = file_put_contents($filePath, $output);
        return $result;

    }
}