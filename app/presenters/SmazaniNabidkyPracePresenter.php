<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;

use Nette\Forms\Controls;


class SmazaniNabidkyPracePresenter extends BasePresenter
{
    //private $databaze;
    /*public function __construct(Nette\Database\Context $databaze)
    {
        $this->databaze = $databaze;
    }*/

    public function renderDefault($id_nabidky)
    {
        $uzivatel = $this->getUser();
        if(!$uzivatel->isLoggedIn())
        {
            $this->redirect("Prihlaseni:");
        }
        //$this->template->id_galerie = $id_galerie;
        $this->databaze->query("DELETE FROM nabidka_prace WHERE id_pozice=".$id_nabidky);
        $this->redirect("EditovatNabidkyPrace:");
    }
}