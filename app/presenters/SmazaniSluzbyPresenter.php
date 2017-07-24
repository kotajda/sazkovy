<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;

use Nette\Forms\Controls;


class SmazaniSluzbyPresenter extends BasePresenter
{
    //private $databaze;
    private $id_galerie;
    /*public function __construct(Nette\Database\Context $databaze)
    {
        $this->databaze = $databaze;
    }*/

    public function renderDefault($id_sluzby)
    {
        $uzivatel = $this->getUser();
        if(!$uzivatel->isLoggedIn())
        {
            $this->redirect("Prihlaseni:");
        }
        //$this->template->id_galerie = $id_galerie;
        $this->databaze->query("DELETE FROM sluzby WHERE id_sluzby=".$id_sluzby);
        $this->redirect("EditovatSluzby:");
    }
}