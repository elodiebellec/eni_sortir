<?php


namespace App\FileUploader;


use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FileUploader
{
    private ParameterBagInterface $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function saveCSV($file){
        $directory = $this->params->get('upload_dir_csv');
        $newFileName = "CSV".'-'.uniqid().'.'.$file->guessExtension();
        $file->move($directory, $newFileName);
    }
    public function saveImage($file){
        $directory = $this->params->get('upload_dir_images');
        $newFileName = "img".'-'.uniqid().'.'.$file->guessExtension();
        $file->move($directory, $newFileName);
    }
}