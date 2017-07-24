<?php

namespace App\Presenters;

use Nette;
use App\Model;


class ReferencePresenter extends BasePresenter
{
	/*private $databaze;
	public function __construct(Nette\Database\Context $databaze)
    {
        $this->databaze = $databaze;
    }*/

	public function renderDefault()
	{
		$data = $this->databaze->query("SELECT id_reference, kategorie_reference FROM reference");
        $this->template->kategorie_reference = array();
        $i = 1;
        $this->template->id_reference = array();
        foreach($data as $kategorie)
        {
        	$this->template->kategorie_reference[$i] = $kategorie["kategorie_reference"];
            $this->template->id_reference[$i] = $kategorie["id_reference"];
        	$i++;
        }

        $this->template->fotogalerie_nazvy = array();
        $this->template->fotogalerie_popisy = array();
        $this->template->fotky_nazvy = array();
        $this->template->fotky_url = array();
        $this->template->nahledy_url = array();
        $j=0;
        $k=0;
        for($i=0;$i<count($this->template->id_reference);$i++)
        {
            $data = $this->databaze->query("SELECT id_galerie, nazev, popis FROM fotogalerie WHERE id_kategorie_reference=".$this->template->id_reference[$i+1]);
            $j=0;
            foreach($data as $galerie)
            {
                $this->template->fotogalerie_nazvy[$i][$j] = $galerie["nazev"];
                //array_push($this->template->fotogalerie_nazvy, $galerie["nazev"]);
                $this->template->fotogalerie_popisy[$i][$j] = $galerie["popis"];
                //array_push($this->template->fotogalerie_popisy, $galerie["popis"]);
                $data_fotky = $this->databaze->query("SELECT id_fotky, nazev_fotky, url_fotky FROM nahrane_fotky WHERE id_fotogalerie=".$galerie["id_galerie"]);
                $k=0;
                foreach($data_fotky as $fotky)
                {
                    $this->template->fotky_nazvy[$i][$j][$k] = $fotky["nazev_fotky"];
                    $this->template->fotky_url[$i][$j][$k] = $fotky["url_fotky"];
                    $data_nahled = $this->databaze->fetch("SELECT url_nahledu FROM nahrane_fotky_nahledy WHERE id_fotogalerie=".$galerie["id_galerie"]." AND id_fotky=".$fotky["id_fotky"]);
                    $this->template->nahledy_url[$i][$j][$k]  = $data_nahled["url_nahledu"];
                    $k++;
                }
                $j++;
            }
        }        

        //$data = $this->databaze->query("SELECT id_kategorie_reference, nazev FROM fotogalerie");
        
        /*$this->template->fotogalerie = array();
        $i = 1;
        foreach($data as $fotogalerie)
        {
            $this->template->fotogalerie[$i] = $fotogalerie;
            $i++;
        }*/
    }
}
