<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;

use Nette\Forms\Controls;


class SmazatObrazekPresenter extends BasePresenter
{
	//private $databaze;
	private $id_galerie;
	/*public function __construct(Nette\Database\Context $databaze)
    {
        $this->databaze = $databaze;
    }*/

	public function renderDefault($id_fotky)
	{
		$uzivatel = $this->getUser();
        if(!$uzivatel->isLoggedIn())
        {
            $this->redirect("Prihlaseni:");
        }

        //echo "xxxxxxxxxxxx ".$id_fotky;
        $data = $this->databaze->fetch("SELECT id_fotogalerie, url_fotky FROM nahrane_fotky WHERE id_fotky=".$id_fotky);
        
        //echo $this->template->basePath."/".$data["url_fotky"];
        //\editacni-system\administrace\www\nahrane-obrazky
        //unlink("C:\Programy\xampp\htdocs".$this->template->basePath."/".$data["url_fotky"]);
        
        //echo "base: ".$this->template->basePath;
        //unlink($this->template->basePath."/".$data["url_fotky"]);
        unlink($data["url_fotky"]);
        $this->databaze->query("DELETE FROM nahrane_fotky WHERE id_fotky=".$id_fotky);


        $data = $this->databaze->fetch("SELECT id_fotogalerie, url_nahledu FROM nahrane_fotky_nahledy WHERE id_fotky=".$id_fotky);
        unlink($data["url_nahledu"]);
        $this->databaze->query("DELETE FROM nahrane_fotky_nahledy WHERE id_fotky=".$id_fotky);

        $this->redirect("EditaceFotogalerie:default",$data["id_fotogalerie"]);

       /* //$this->template->id_galerie = $id_galerie;
        $data = $this->databaze->query("SELECT id_fotky, nazev_fotky, url_fotky FROM nahrane_fotky WHERE id_fotogalerie=".$id_galerie);
        $this->template->nazvy_fotek = array();
        $this->template->url_fotek = array();
        $this->template->id_fotek = array();
        $i = 0;
        foreach($data as $fotka)
        {
        	$this->template->nazvy_fotek[$i] = $fotka["nazev_fotky"];
        	$this->template->url_fotek[$i] = $fotka["url_fotky"];
        	$this->template->id_fotek[$i] = $fotka["id_fotky"];
        	$i++;
        }
        $data = $this->databaze->fetch("SELECT nazev FROM fotogalerie WHERE id_galerie=".$id_galerie);
		$this->template->nazev_galerie = $data["nazev"];*/
	}

	protected function createComponentSelectForm()
	{

	}

	public function selectFormSucceeded(UI\Form $form, $values)
    {

    }
}