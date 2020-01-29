<?php
namespace Domain;

abstract class Html2Pdf
{
    /**
     * Debe recibir in string con el html y guarda el fichero en la ruta.
     * Devolverá true o false según haya funcionado o no.
     *
     * @param string $html
     * @param string $filePath
     * @return mixed
     */
    abstract public function generate(string $html, string $filePath): bool;
    abstract public function stream(string $html, string $name = ""): bool;

    /**
     * @param string $filePath
     * @return string
     */
    protected function getDirPath(string $filePath): string
    {
        $path = explode('/', $filePath);
        unset($path[count($path) - 1]);
        $dirPath = implode($path, '/');
        return $dirPath;
    }

    /**
     * @param string $filePath
     */
    protected function makeDir(string $filePath)
    {
        $dirPath = $this->getDirPath($filePath);
        if (!is_dir($dirPath)) {
            // dir doesn't exist, make it
            mkdir($dirPath, 0755, true);
        }
    }
}