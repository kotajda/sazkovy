<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;

use Nette\Forms\Controls;
use App\Model\FotogalerieManager;


class EditaceFotogaleriePresenter extends BasePresenter
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
        //$data = $this->databaze->query("SELECT id_fotky, nazev_fotky, url_fotky FROM nahrane_fotky WHERE id_fotogalerie=".$id_galerie);
        $data = $this->databaze->query("SELECT id_nahledu, nazev_nahledu, url_nahledu FROM nahrane_fotky_nahledy WHERE id_fotogalerie=".$id_galerie);
        $this->template->nazvy_fotek = array();
        $this->template->url_fotek = array();
        $this->template->id_fotek = array();
        $i = 0;
        foreach($data as $fotka)
        {
        	$this->template->nazvy_fotek[$i] = $fotka["nazev_nahledu"];
        	$this->template->url_fotek[$i] = $fotka["url_nahledu"];
        	$this->template->id_fotek[$i] = $fotka["id_nahledu"];
        	$i++;
        }
        $data = $this->databaze->fetch("SELECT nazev FROM fotogalerie WHERE id_galerie=".$id_galerie);
		$this->template->nazev_galerie = $data["nazev"];
	}

	protected function createComponentSelectForm()
	{
		$data = $this->databaze->query("SELECT kategorie_reference FROM reference");
        $kategorie_reference = array();
        $i = 1;
        foreach($data as $kategorie)
        {
        	$kategorie_reference[$i] = $kategorie["kategorie_reference"];
        	$i++;
        }

		$id_galerie = $this->getParameter("id_galerie");
		$data = $this->databaze->fetch("SELECT id_kategorie_reference, nazev, popis FROM fotogalerie, reference WHERE id_galerie=".$id_galerie);

		$formular = new UI\Form;
		$formular->addText("nazev","Název:")->setDefaultValue($data["nazev"]);
		$formular->addTextArea("popis","Popis:")->setAttribute("id", "popis")->setAttribute("rows", "5")->setDefaultValue($data["popis"]);
		$formular->addMultiUpload('soubory', 'Soubory');//->setRequired(FALSE)->addRule(Form::IMAGE, 'Avatar musí být JPEG, PNG nebo GIF.');
		$formular->addSelect("kategorie","Kategorie",$kategorie_reference)->setPrompt("Vyberte kategorii")->setDefaultValue($data["id_kategorie_reference"]);
		$formular->addSubmit("editovat", "Editovat fotogalerii")->setAttribute("class", "button expanded");
        $formular->onSuccess[] = [$this, "selectFormSucceeded"];
        return $formular;
	}

	public function selectFormSucceeded(UI\Form $form, $values)
    {
    	$id_galerie = $this->getParameter("id_galerie");
    	$nazev = $values["nazev"];
    	$popis = $values["popis"];
    	$soubory = $values["soubory"];
    	$id_kategorie_reference = $values["kategorie"];
    	$obrazky = array();
    	$pocet_souboru = count($soubory);
    	$platne_koncovky = array("jpeg", "jpg", "png");
    	$pocet_obrazku = 0;
    	for($i=0;$i<$pocet_souboru;$i++)
    	{
    		//echo var_dump($soubory[$i])."<br />";
    		$obrazky[$i] = $soubory[$i]->getName();
    		$koncovka = explode('.', $obrazky[$i])[1];
    		if(in_array($koncovka, $platne_koncovky))
    		{
    			$pocet_obrazku++;
    		} 
    	}

    	$this->template->zpravy = array();
    	if($nazev == "")
    	{
    		$this->template->zpravy[0] = "Nebyl vložen žádný název pro fotogalerii.";
    		return 0;
    	}
    	if($popis == "")
    	{
    		$this->template->zpravy[0] = "Nebyl vložen žádný popis pro fotogalerii.";
    		return 0;
    	}
    	if($id_kategorie_reference == "")
    	{
    		$this->template->zpravy[0] = "Nebyla vložena žádná kategorie pro fotogalerii.";
    		return 0;
    	}
    	if($pocet_obrazku == 0)
    	{
    		$this->template->zpravy[0] = "Žádný vybraný soubor není obrázek nebo nebyl vybrán vůbec žádný obrázek. Proběhla aktualizace údajů o galerii.";
    		//return 0;
    	}
    	
    	$this->databaze->query("UPDATE fotogalerie SET id_kategorie_reference='".$id_kategorie_reference."', nazev='".$nazev."', popis='".$popis."' WHERE id_galerie=".$id_galerie);

        $fotogalerie_manager = new FotogalerieManager($this->databaze);
        $fotogalerie_manager->nahrat_obrazek($obrazky, $soubory, $id_galerie, $this->template->zpravy);
        //TADY JE KOD PRO NAHRANAVI, KTERY UŽ ASI NEBUDE TŘEBA
		/*$j = 0;
		for ($i = 0; $i < count($obrazky); $i++) 
		{
			$target_path = "obrazky/";
			$validextensions = array("jpeg", "jpg", "png");      // Extensions which are allowed.
			$ext = explode('.', $obrazky[$i]); 
			$koncovka = $ext[1]; // Store extensions in the variable.
			$target_path = $target_path . Strings::webalize($ext[0]).".".$ext[1];
			$j = $j + 1;
			if (in_array($koncovka, $validextensions) && !file_exists($target_path)) 
			{
				if(move_uploaded_file($soubory[$i], $target_path))
				{
					$this->template->zpravy[$i] = "Obrázek ".$obrazky[$i]." byl nahrán bez problémů.";
					$this->databaze->query("INSERT INTO nahrane_fotky (id_fotogalerie, nazev_fotky, url_fotky) VALUES('".$id_galerie."','".$ext[0]."','".$target_path."')");
				} 
				else 
				{     //  If File Was Not Moved.
					$this->template->zpravy[$i] = "Při nahrávání obrázku ".$obrazky[$i]." se vyskytla chyba!";
				}
			} 
			else 
			{
				$this->template->zpravy[$i] = "Soubor".$obrazky[$i]." nebyl nahrán! Pravděpodobně se nejedná o obrázek nebo už byl obrázek se shodným jmémen v minulosti nahrán. 
													Toto vyřešíte jeho přejmenováním a následným vložením v aktualizaci této fotogalerie";
			}
		}*/
    }
}