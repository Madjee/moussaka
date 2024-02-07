<?php

namespace App\Service;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PictureService
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function add(UploadedFile $picture, ?string $folder = '', ?int $width = 250, ?int $height = 250)
    {
        // On donne un nouveau nom à l'image
        $fichier = md5(uniqid(rand(), true)) . '.webp';

        // On crée le dossier de destination s'il n'existe pas
        $path = $this->params->get('images_directory') . $folder;
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        // On déplace le fichier uploadé vers le répertoire de destination
        $picture->move($path, $fichier);

        // On crée le chemin complet du fichier
        $imagePath = $path . '/' . $fichier;

        // On vérifie le type de l'image et on charge l'image en conséquence
        $picture_source = null;
        switch ($picture->getMimeType()) {
            case 'image/png':
                $picture_source = imagecreatefrompng($imagePath);
                break;
            case 'image/jpeg':
                $picture_source = imagecreatefromjpeg($imagePath);
                break;
            case 'image/webp':
                $picture_source = imagecreatefromwebp($imagePath);
                break;
            default:
                throw new Exception('Format d\'image incorrect');
        }

        // Votre logique de manipulation d'image continue ici...

        // N'oubliez pas de retourner le nom du fichier après manipulation
        return $fichier;
    }

    public function delete(string $fichier, ?string $folder = '', ?int $width = 250, ?int $height = 250)
    {
        if ($fichier !== 'default.webp') {
            $success = false;
            $path = $this->params->get('images_directory') . $folder;

            $mini = $path . '/mini/' . $width . 'x' . $height . '-' . $fichier;

            if (file_exists($mini)) {
                unlink($mini);
                $success = true;
            }

            $original = $path . '/' . $fichier;

            if (file_exists($original)) {
                unlink($original);
                $success = true;
            }
            return $success;
        }
        return false;
    }
}
