<?php

namespace App\Presenters;

use Nette;
use App\Model;

use Nette\Application\UI;
use Nette\Application\UI\Form;


class EditovatKontaktyPresenter extends BasePresenter
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
	}

	protected function createComponentSelectForm()
	{
		$data = $this->databaze->fetch("SELECT id_kontaktu, mobil, pevna_linka, fax, email, ulice, mesto, psc FROM kontaktni_udaje");
		$formular = new UI\Form;
		$formular->addText("mobil","Mobil:")->setDefaultValue($data["mobil"]);
		$formular->addText("pevna_linka","Pevná linka:")->setDefaultValue($data["pevna_linka"]);
		$formular->addText("fax","Fax:")->setDefaultValue($data["fax"]);
		$formular->addText("email","Email:")->setDefaultValue($data["email"]);
		$formular->addText("ulice","Ulice:")->setDefaultValue($data["ulice"]);
		$formular->addText("mesto","Město:")->setDefaultValue($data["mesto"]);
		$formular->addText("psc","PSČ:")->setDefaultValue($data["psc"]);
		$formular->addHidden("id_kontaktu")->setDefaultValue($data["id_kontaktu"]);
		$formular->addSubmit("vlozit", "Aktualizovat kontakty")->setAttribute("class", "button expanded");
        $formular->onSuccess[] = [$this, "selectFormSucceeded"];
        return $formular;
	}

	public function selectFormSucceeded(UI\Form $form, $values)
    {
    	/*$mobil = $values["mobil"];
    	$pevna_linka = $values["pevna_linka"];
    	$fax = $values["fax"];
    	$email = $values["email"];
    	$ulice = $values["ulice"];
    	$mesto = $values["mesto"];
    	$psc = $data["psc"];
    	$id_kontaktu = $values["id_kontaktu"];*/
    	$this->databaze->query("UPDATE kontaktni_udaje SET mobil='".$values["mobil"]."', pevna_linka='".$values["pevna_linka"]."', fax='".$values["fax"]."', email='".$values["email"]."', ulice='".$values["ulice"]."', mesto='".$values["mesto"]."', psc='".$values["psc"]."' WHERE id_kontaktu=".$values["id_kontaktu"]);
    	$this->template->zprava = "Kontaktní údaje byly aktualizovány";
    }
}	