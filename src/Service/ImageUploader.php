<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Service permettant de l'upload d'image dans le dossier public/uploads/pictures
 */
class ImageUploader {

    private $targetDirectory;

    /**
     * Constructeur
     * 
     * Lorsque dans un service on précise un paramêtre non "Type-hinté" dans le 
     * constructeur, Symfony va rechercher ce paramêtre dans le fichier 
     * config/services.yaml . S'il le trouve, il transmet la valeur de l'argument 
     * configuré dans le paramêtre du constructeur.
     *
     * @param [type] $targetDirectory
     */
    public function __construct($targetDirectory)
    {
        /** @var string $targetDirectory */
        $this->targetDirectory = $targetDirectory;
    }
    /**
     * Méthode permettant d'uploader une image dans le dossier public/uploads/pictures
     *
     * @param UploadedFile $image
     * @return void
     */
    public function upload(UploadedFile $pictureFile) {
        // On génère un nom unique pour le fichier à déplacer et le concatène avec son extension
        // Ex : 14cd1cdc54dc7cdcd2.png
        $newFilename = uniqid().'.'.$pictureFile->guessExtension();

        // On déplace le fichier physique en lui attribuant le nom créé précédement
        $pictureFile->move($this->targetDirectory, $newFilename);

        // On retourne le nom du fichier pour sauvegarde en BDD
        return $newFilename;
    }
}