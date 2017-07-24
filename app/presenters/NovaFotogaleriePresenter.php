<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;

use Nette\Forms\Controls;
use App\Model\FotogalerieManager;

class NovaFotogaleriePresenter extends BasePresenter
{
	/*private $fotogalerie_manager;
	public function __construct(FotogalerieManager $fotogalerie_manager)
    {
        $this->fotogalerie_manager = $fotogalerie_manager;
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
		$data = $this->databaze->query("SELECT kategorie_reference FROM reference");
        $kategorie_reference = array();
        $i = 1;
        foreach($data as $kategorie)
        {
        	$kategorie_reference[$i] = $kategorie["kategorie_reference"];
        	$i++;
        }

		$formular = new UI\Form;
		$formular->addText("nazev","Název:");
		$formular->addTextArea("popis","Popis:")->setAttribute("id", "popis")->setAttribute("rows", "5");
		$formular->addMultiUpload('soubory', 'Soubory');//->setRequired(FALSE)->addRule(Form::IMAGE, 'Avatar musí být JPEG, PNG nebo GIF.');
		$formular->addSelect("kategorie","Kategorie",$kategorie_reference)->setPrompt("Vyberte kategorii");
		$formular->addSubmit("vlozit", "Nová fotogalerie")->setAttribute("class", "button expanded");
        $formular->onSuccess[] = [$this, "selectFormSucceeded"];
        return $formular;
	}

	public function selectFormSucceeded(UI\Form $form, $values)
    {
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
    		$this->template->zpravy[0] = "Žádný vybraný soubor není obrázek nebo nebyl vybrán vůbec žádný obrázek.";
    		return 0;
    	}

    	$sql = "SELECT id_galerie FROM fotogalerie WHERE nazev='".$nazev."'";
    	$data = $this->databaze->fetch($sql);
    	if(isset($data["id_galerie"]))
    	{
    		$this->template->zpravy[0] = "Galerie s názvem ".$nazev." už je v databázi";
    		return 0;
    	}

    	$this->databaze->query("INSERT INTO fotogalerie (id_kategorie_reference, nazev, popis) VALUES('".$id_kategorie_reference."','".$nazev."','".$popis."')");
		$data = $this->databaze->fetch($sql);	

    	//http://www.w3bees.com/2013/02/multiple-file-upload-with-php.html
    	//https://www.formget.com/upload-multiple-images-using-php-and-jquery/
		//http://www.w3schools.com/php/php_file_upload.asp

        $fotogalerie_manager = new FotogalerieManager($this->databaze);
        $fotogalerie_manager->nahrat_obrazek($obrazky, $soubory, $data["id_galerie"], $this->template->zpravy);
		/*$j = 0;
		for ($i = 0; $i < count($obrazky); $i++) 
		{
			$target_path = "obrazky/";
			$validextensions = array("jpeg", "jpg", "png");
			$ext = explode('.', $obrazky[$i]); 
			$koncovka = $ext[1];
			$target_path = $target_path . Strings::webalize($ext[0]).".".$ext[1];
			$j = $j + 1;
			if (in_array($koncovka, $validextensions) && !file_exists($target_path)) 
			{
				if(move_uploaded_file($soubory[$i], $target_path))
				{
					$this->template->zpravy[$i] = "Obrázek ".$obrazky[$i]." byl nahrán bez problémů.";
					$this->databaze->query("INSERT INTO nahrane_fotky (id_fotogalerie, nazev_fotky, url_fotky) VALUES('".$data["id_galerie"]."','".$ext[0]."','".$target_path."')");
				} 
				else 
				{
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