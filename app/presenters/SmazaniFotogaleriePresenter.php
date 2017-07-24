<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;

use Nette\Forms\Controls;


class SmazaniFotogaleriePresenter extends BasePresenter
{
    //private $databaze;
    private $id_galerie;
    /*public function __construct(Nette\Database\Context $databaze)
    {
        $this->databaze = $databaze;
    }*/

    public function renderDefault($id_galerie)
    {
        $uzivatel = $this->getUser();
        if(!$uzivatel->isLoggedIn())
        {
            $this->redirect("Prihlaseni:");
        }
        //$this->template->id_galerie = $id_galerie;
        $this->databaze->query("DELETE FROM fotogalerie WHERE id_galerie=".$id_galerie);
        $data = $this->databaze->query("SELECT id_fotky, url_fotky FROM nahrane_fotky WHERE id_fotogalerie=".$id_galerie);
        foreach($data as $fotka)
        {
            unlink($fotka["url_fotky"]);
            $this->databaze->query("DELETE FROM nahrane_fotky WHERE id_fotky=".$fotka["id_fotky"]);

            $data_nahled = $this->databaze->fetch("SELECT id_fotky, url_nahledu FROM nahrane_fotky_nahledy WHERE id_fotogalerie=".$id_galerie);
            unlink($data_nahled["url_nahledu"]);
            $this->databaze->query("DELETE FROM nahrane_fotky_nahledy WHERE id_fotky=".$fotka["id_fotky"]);

        }
        $this->redirect("VsechnyFotogalerie:");
    }
}