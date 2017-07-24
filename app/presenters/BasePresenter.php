<?php

namespace App\Presenters;

use Nette;
use App\Model;


/**
 * Base presenter for all application presenters.
 */
use Nette\Security\User;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	public function beforeRender()
    {   
        parent::beforeRender(); // nezapomeňte volat metodu předka, stejně jako u startup()
        $uzivatel = $this->getUser();
        if($uzivatel->isLoggedIn())
        {
            $this->template->menuItems = [
                'Úvodní stránka' => 'Homepage:',
                'Nová objednávka' => 'NovaObjednavka:',
                'Objednávky' => 'VypisObjednavek:',
                'Přehled zákazníků' => 'Zakaznici:',
                'Přehled činností' => 'Cinnosti:',
                'Vložit zákazníka' => 'VlozitZakaznika:',
                'Vložit činnost' => 'VlozitCinnost:',
            ];
        }
    }
}