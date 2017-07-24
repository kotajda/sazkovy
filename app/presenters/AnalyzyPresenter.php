<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;

use Nette\Database\Context;
use Nette\Database\Connection;

use Tracy\Debugger;

class AnalyzyPresenter extends BasePresenter
{
	/** @var Nette\Database\Context */
	private $databaze;

    private $moznosti;

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
            $this->template->strankovani = $_GET["strana"];
        }

        $sql = "SELECT userName, authorProfileLink, tip, rate, url, text, matchName FROM  tipsport_analyzy, tipsport_jmena_zapasu WHERE tipsport_analyzy.matchId = tipsport_jmena_zapasu.matchId LIMIT 10 OFFSET ".$this->template->strankovani."";
        
        //$sql = "SELECT userName, authorProfileLink, tip, rate, url, text, matchName FROM  tipsport_analyzy, tipsport_jmena_zapasu WHERE tipsport_analyzy.matchId = tipsport_jmena_zapasu.matchId";
        
        $analyzy = $this->databaze->query($sql);

        $this->template->data = array();
        $i = 0;
        foreach($analyzy as $data)
        {
            $this->template->data[$i] = $data;
            $i++;
        }

	}
}
