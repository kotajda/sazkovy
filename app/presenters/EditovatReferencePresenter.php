<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;
use Nette\Application\UI\Form;


class EditovatReferencePresenter extends BasePresenter
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
        $data = $this->databaze->query("SELECT id_reference, kategorie_reference FROM reference");
        $this->template->kategorie_reference = array();
        $this->template->id_reference = array();
        $i = 0;
        foreach($data as $kategorie)
        {
        	$this->template->kategorie_reference[$i] = $kategorie["kategorie_reference"];
            $this->template->id_reference[$i] = $kategorie["id_reference"];
        	$i++;
        }
	}

	protected function createComponentSelectForm()
	{
		$formular = new UI\Form;
		$formular->addText("kategorie","Kategorie:")->setHtmlId("kategorie");
        $formular->addHidden("id_kategorie")->setHtmlId("id_kategorie");
		$formular->addSubmit("vlozit", "Vložit nebo přejmenovat kategorii")->setAttribute("class", "button expanded");
        $formular->onSuccess[] = [$this, "selectFormSucceeded"];
        return $formular;
	}

	public function selectFormSucceeded(UI\Form $form, $values)
    {
    	$kategorie = $values["kategorie"];
        $id_reference = $values["id_kategorie"];
        if($kategorie == "")
        {
            $this->template->zprava = "Název nebyl vložen.";
            return 0;
        }

        if($kategorie != "")
        {
            $data["id_reference"] = -1;
            if(!empty($id_reference))
            {
                $data = $this->databaze->fetch("SELECT id_reference FROM reference WHERE id_reference=".$id_reference);
            }
            if($data["id_reference"] != $id_reference)
            {
                $this->databaze->query("INSERT INTO reference(kategorie_reference) VALUES('".$kategorie."')");
            }
            else
            {
                $this->databaze->query("UPDATE reference SET kategorie_reference='".$kategorie."' WHERE id_reference=".$id_reference);
            }
        }
    }

}