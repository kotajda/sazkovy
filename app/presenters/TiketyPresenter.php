<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;

use Nette\Database\Context;
use Nette\Database\Connection;

use Tracy\Debugger;

class TiketyPresenter extends BasePresenter
{
	/** @var Nette\Database\Context */
	private $databaze;

    public function __construct(Nette\Database\Context $databaze)
	{
		$this->databaze = $databaze;
	}

	public function renderDefault()
	{
		$this->template->anyVariable = "any value";
        /*$this->template->moznosti = $this->moznosti;
        echo "xxx".var_dump($this->template->moznosti);*/

        $this->template->strankovani = 0;

        if(isset($_GET["strana"]))
        {
            echo "strankovani";
            $this->template->strankovani = $_GET["strana"];
        }

        $sql = "SELECT id_tiketu, userName, author, rate, amountPaid, potentialWin, currency, url FROM tipsport_tiketarena LIMIT 10";

        ///$sql = "SELECT userName, author, rate, amountPaid, potentialWin, currency, url, GROUP_CONCAT(eventName) As eventName, GROUP_CONCAT(opportunityfullName) AS opportunityfullName FROM tipsport_tiketarena, tipsport_tiketarena_sazky WHERE tipsport_tiketarena.id_tiketu = tipsport_tiketarena_sazky.id_tiketu GROUP BY id_tiketu LIMIT 10";
        //echo $sql."<br /><br /><br />";
        
        $tikety = $this->databaze->query($sql);

        $this->template->title = "xxx";

        $this->template->data = array();
        $this->template->data_zapasy = array();
        $i = 0;
        foreach($tikety as $data)
        {
            $this->template->data[$i] = $data;
            $sql2 = "SELECT eventName, opportunityfullName FROM tipsport_tiketarena_sazky WHERE id_tiketu = ".$data["id_tiketu"];
            $zapasy = $this->databaze->query($sql2);
            $j=0;
            foreach($zapasy as $data_zapasy)
            {
                $this->template->data_zapasy[$data["id_tiketu"]][$j] = $data_zapasy;
                $j++;
            }
            $i++;
        }

	}
}