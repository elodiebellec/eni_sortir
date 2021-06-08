<?php


namespace App\FileUploader;


use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FileUploader
{
    private ParameterBagInterface $params;
    static private string $lastFileUploaded;
    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function saveCSV($file) : bool {

        if($file->getClientOriginalExtension() !== 'csv'){
            return false;
        }
        $directory = $this->params->get('upload_dir_csv');
        $newFileName = $this->generateNewFileName($file,'participants');
        $this->registerLastUploadedFile($directory,$newFileName);
        $file->move($directory, $newFileName);
        return true;
    }
    public function saveImage($file){
        $directory = $this->params->get('upload_dir_images');
        $newFileName = $this->generateNewFileName($file,'image');
        $this->registerLastUploadedFile($directory,$newFileName);
        $file->move($directory, $newFileName);
    }

    private function generateNewFileName($file,$prefix): string
    {
        return $prefix.'-'.uniqid().'.'.$file->getClientOriginalExtension();
    }
    private function registerLastUploadedFile($directory,$fileName): void{
        FileUploader::$lastFileUploaded = $directory."/".$fileName;
    }
    public function getLastUploadedFile(): string
    {
        return FileUploader::$lastFileUploaded;
    }
}