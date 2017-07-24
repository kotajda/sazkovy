<?php

namespace App\Presenters;

use Nette;
use App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	public function beforeRender()
    {
        parent::beforeRender(); // nezapomeňte volat metodu předka, stejně jako u startup()
        $this->template->menuItems = [
            'Domů' => 'Homepage:',
            'Zápasy' => 'Zapasy:',
            'Generátor' => 'Generator:',
            'Generátor podle zápasu' => 'GeneratorPodleZapasu:',
            'Analýzy' => 'Analyzy:',
            'Nejmenší kurzy' => 'NejmensiKurzy:',
            'Největší kurzy' => 'NejvetsiKurzy:',
            'Nejtěsnější kurzy' => 'NejtesnejsiKurzy:',
            'Tikety' => 'Tikety:',
            'Výsledky' => 'Vysledky:',
            //'Fotbalové výsledky' => 'VysledkyFotbal:Zive',
            /*'Hokejové výsledky' => 'VysledkyHokej:',
            'Tenisové výsledky' => 'VysledkyTenis:',
            'Basketbalové výsledky' => 'VysledkyBasketbal:',
            'Volejbalové výsledky' => 'VysledkyVolejbal:',
            'Baseballové výsledky' => 'VysledkyBaseball:',
            'Výsledky am. fotbalu' => 'VysledkyAmerickyFotbal:',
            'Výsledky ragby' => 'VysledkyRagby:',
            'výsledky kriket' => 'VysledkyKriket:',
            'Výsledky házené' => 'VysledkyHazena:',*/
        ];

        $this->template->vysledkySporty = [
            'Fotbal' => 'Fotbal',
            'Hokej' => 'Hokej',
            'Tenis' => 'Tenis',
            'Basketbal' => 'Basketbal',
            'Házená' => 'Hazena',
            'Volejbal' => 'Volejbal',
            'Americký fotbal' => 'AmerickyFotbal',
            'Ragby' => 'Ragby',
            'Kriket' => 'Kriket',
            'Baseball' => 'Baseball',
        ];
    }
}