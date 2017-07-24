<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;
use Nette\Application\UI\Form;


class EditovatSluzbyPresenter extends BasePresenter
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
		$this->template->nazvy_sluzeb = array();
		$this->template->popisy_sluzeb = array();
		$this->template->aktualnost_sluzeb = array();
		$this->template->id_sluzeb = array();
		$data = $this->databaze->query("SELECT id_sluzby, nazev_sluzby, popis_sluzby, aktualnost FROM sluzby");
		$i = 0;
		foreach ($data as $nabidky) 
		{
			$this->template->nazvy_sluzeb[$i] = $nabidky["nazev_sluzby"];
			$this->template->popisy_sluzeb[$i] = $nabidky["popis_sluzby"];
			$this->template->aktualnost_sluzeb[$i] = $nabidky["aktualnost"];
			$this->template->id_sluzeb[$i] = $nabidky["id_sluzby"];
			$i++;
		}
	}

	protected function createComponentSelectForm()
	{
		$formular = new UI\Form;
		$formular->addText("nazev_sluzby","Název služby:")->setHtmlId("nazev_sluzby");
		$formular->addTextArea("popis_sluzby","Popis služby:")->setAttribute("rows",10)->setHtmlId("popis_sluzby");
		$formular->addRadioList("aktivovat","Aktivovat:",[ 0 => "Ne", 1 => "Ano",])->getSeparatorPrototype()->setName(NULL);
		$formular->addHidden("id_sluzby")->setHtmlId("id_sluzby");
		$formular->addSubmit("vlozit", "Vložit nebo aktualizovat službu")->setAttribute("class", "button expanded");
        $formular->onSuccess[] = [$this, "selectFormSucceeded"];
        return $formular;
	}

	public function selectFormSucceeded(UI\Form $form, $values)
    {
    	$nazev_sluzby = $values["nazev_sluzby"];
    	$popis_sluzby = $values["popis_sluzby"];
    	$aktivovat = $values["aktivovat"];
		$id_sluzby = $values["id_sluzby"];

		if($nazev_sluzby == "")
		{
			$this->template->zprava = "Nebyl vložen název služby.";
			return 0;
		} 
		if($popis_sluzby == "")
		{
			$this->template->zprava = "Nebyl vložen popisek služby.";
			return 0;
		}
		if(!is_numeric($aktivovat))
		{
			$this->template->zprava = "Nebyl vložen status služby.";
			return 0;
		}		

		$data["id_sluzby"] = -1;
		if(!empty($id_sluzby))
		{
			$data = $this->databaze->fetch("SELECT id_sluzby FROM sluzby WHERE id_sluzby=".$id_sluzby);
		}
	  	if($data["id_sluzby"] != $id_sluzby)
	   	{
	        $this->databaze->query("INSERT INTO sluzby(nazev_sluzby, popis_sluzby, aktualnost) VALUES('".$nazev_sluzby."','".$popis_sluzby ."','".$aktivovat."')");
	  	}
	   	else
	   	{
	   		$this->databaze->query("UPDATE sluzby SET nazev_sluzby='".$nazev_sluzby."', popis_sluzby='".$popis_sluzby."', aktualnost='".$aktivovat."' WHERE id_sluzby=".$id_sluzby);
	   	}	

    }

}