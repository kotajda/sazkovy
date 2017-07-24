<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;
use Nette\Application\UI\Form;
use Nette\Security\Passwords;

class UzivatelePresenter extends BasePresenter
{
	/*private $databaze;
	public function __construct(Nette\Database\Context $databaze)
    {
        $this->databaze = $databaze;
    }*/

	public function renderDefault()
	{
		$uzivatel = $this->getUser();
        if(!$uzivatel->isLoggedIn())
        {
            $this->redirect("Prihlaseni:");
        }

        $data = $this->databaze->query("SELECT id_uzivatele, jmeno FROM uzivatele");
        $this->template->jmena = array();
        $this->template->id_uzivatelu = array();
        $i=0;
        foreach($data as $uzivatel)
        {
        	$this->template->jmena[$i] = $uzivatel["jmeno"];
        	$this->template->id_uzivatelu[$i] = $uzivatel["id_uzivatele"];
        	$i++;
        }
	}

	protected function createComponentSelectForm()
	{
		$data = $this->databaze->fetch("SELECT id_uzivatele, jmeno FROM uzivatele");
		$formular = new UI\Form;
		$formular->addText("jmeno","Jméno:")->setHtmlId("jmeno")->setDefaultValue($data["jmeno"]);
		$formular->addPassword("puvodni_heslo","Původní heslo");
		$formular->addPassword("nove_heslo","Nové heslo");
		$formular->addPassword("opakovane_heslo","Opakované heslo");
		$formular->addHidden("id_uzivatele")->setHtmlId("id_uzivatele")->setDefaultValue($data["id_uzivatele"]);;
		$formular->addSubmit("vlozit", "Aktualizovat uživatele")->setAttribute("class", "button expanded");
        $formular->onSuccess[] = [$this, "selectFormSucceeded"];
        return $formular;
	}

	public function selectFormSucceeded(UI\Form $form, $values)
    {
    	$jmeno = $values["jmeno"];
    	$puvodni_heslo = $values["puvodni_heslo"];
    	$nove_heslo = $values["nove_heslo"];
    	$opakovane_heslo = $values["opakovane_heslo"];
    	$id_uzivatele = $values["id_uzivatele"];

    	if($jmeno == "")
    	{
    		$this->template->zprava = "Nebylo vloženo žádné jméno.";
    		return 0;
    	}
    	$data = $this->databaze->fetch("SELECT heslo FROM uzivatele WHERE id_uzivatele=".$id_uzivatele);
    	if(!Passwords::verify($puvodni_heslo, $data["heslo"]))
    	{
    		$this->template->zprava = "Původní heslo neodpovídá tomu v databázi.";
    		return 0;	
    	}
    	if($nove_heslo != "" && $nove_heslo != $opakovane_heslo)
    	{
    		$this->template->zprava = "Nové a opakované heslo musí být stejné.";
    		return 0;
    	}

    	$this->databaze->query("UPDATE uzivatele SET jmeno='".$jmeno."', heslo='".Passwords::hash($nove_heslo)."' WHERE id_uzivatele=".$id_uzivatele);
    	$this->template->zprava = "Uživatel byl aktualizován.";

	}

}
