<?php

namespace App\Presenters;

use Nette;
use App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    protected $databaze;
    public function __construct(Nette\Database\Context $databaze)
    {
        $this->databaze = $databaze;
    }

	public function beforeRender()
    {
        parent::beforeRender(); // nezapomeňte volat metodu předka, stejně jako u startup()
        $this->template->menuItems = [
            "O nás" => "Homepage:",
            "Reference" => "Reference:",
            "Naše služby" => "Sluzby:",
            "Nabídka práce" => "NabidkaPrace:",
            "Certifikáty" => "Certifikaty:",
            "Kde nás najdete" => "KdeNasNajdete:",
            "Napište nám" => "NapisteNam:",
        ];

        $uzivatel = $this->getUser();
        if($uzivatel->isLoggedIn())
        {
        	$this->template->menuAdministrace = [
                "Uživatelé" => "Uzivatele:",
                "Přehled zpráv" => "PrehledZprav:",
        		"Nová fotogalerie" => "NovaFotogalerie:",
        		"Všechny galerie" => "VsechnyFotogalerie:",
                "Upravit reference" => "EditovatReference:",
                "Upravit nabídky" => "EditovatNabidkyPrace:",
                "Upravit služby" => "EditovatSluzby:",
                "Upravit kontakty" => "EditovatKontakty:",
        	];
    	}

        //$this->databaze->fetch("SELECT mobil, pevna_linka, fax, email, ulice, mesto, psc FROM kontaktni_udaje");
        $this->template->kontaktni_udaje = $this->databaze->fetch("SELECT mobil, pevna_linka, fax, email, ulice, mesto, psc FROM kontaktni_udaje");
    }
}