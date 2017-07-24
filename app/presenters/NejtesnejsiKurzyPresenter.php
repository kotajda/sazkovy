<?php

namespace App\Presenters;

use Nette;
use App\Model;

use Nette\Application\UI;
use Nette\Utils\Html;

/**
 * Base presenter for all application presenters.
 */
class NejtesnejsiKurzyPresenter extends BasePresenter
{
    private $databaze;

    private $moznosti;

    public function __construct(Nette\Database\Context $databaze)
    {
        $this->databaze = $databaze;
    }


    protected function createComponentSelectForm()
    {
        $form = new UI\Form;

        $moznosti = $this->databaze->query("SELECT id, sport, competitionName FROM tipsport_zakladni_nabidka_souteze");
        //$this->moznosti = $moznosti;
        $moznostiPole = array();

        //$option = array();

        foreach ($moznosti as $moznost) 
        {
            $moznostiPole[$moznost->id] = Html::el('options',  $moznost->competitionName . " - ".  $moznost->sport)->onClick("vyber_souteze(".$moznost->id.",'".$moznost->competitionName."','".$moznost->sport."');");

        }

        $this->template->souteze = array();
        $this->template->souteze = $moznostiPole;

        $form->addMultiSelect("souteze","", $moznostiPole)->setAttribute("style", "position:relative;top: 50%;transform: translateY(-50%);");
        
        $form->addMultiSelect("vybrane_souteze")
            ->setAttribute("id", "vybrane_souteze")
            ->setAttribute("onBlur", "oznacit_vsechny(this);")
            ->setAttribute("style", "position:relative;top: 50%;transform: translateY(-50%);");

        $form->addText("pocet", "Pocet:")
            ->setDefaultValue("Počet zápasů...")
            ->setAttribute("onClick", "smazat_text_input(this);")
            ->setAttribute("onBlur", "vlozit_default(this);")
            ->setAttribute("style", "position:relative;top:25%;height:50%");

        $sazkovky = ["vse" => "Všechny", "tipsport" => "Tipsport", "fortuna" => "Fortuna"];
        $form->addRadioList("sazkovka","Sázková kancelář",$sazkovky);

        $form->addSubmit("zobrazit", "Zobrazit kurzy")
            ->setAttribute("class", "button")
            ->setAttribute("style", "position:relative;top:25%;height:50%");

        $form->onSuccess[] = [$this, "selectFormSucceeded"];


        $renderer = $form->getRenderer();
        //$renderer->wrappers['moznosti']['size'] = 20;

        //echo var_dump($form->getRawValue());

        return $form;
    }

    public function selectFormSucceeded(UI\Form $form, $values)
    {
        $souteze = $form["vybrane_souteze"]->getRawValue();
        $pocet = $values["pocet"];
        $sazkovka = $values["sazkovka"];
        $podminka = "";
        $podminka2 = "";
        for($i=0;$i<count($souteze);$i++)
        {
          $podminka = $podminka."tipsport_zakladni_nabidka_zapasy.id_souteze=".$souteze[$i]." OR ";
          $podminka2 = $podminka2."tipsport_zakladni_nabidka_souteze.id=".$souteze[$i]." OR ";
        }
        $podminka = substr($podminka, 0, strlen($podminka)-4);
        $podminka2 = substr($podminka2, 0, strlen($podminka2)-4);


       /* CASE 
                            WHEN shortName = 1 THEN rate / (SELECT rate FROM tipsport_zakladni_nabidka_moznosti WHERE tipsport_zakladni_nabidka_moznosti.shortName = 2 AND tipsport_zakladni_nabidka_zapasy.matchId = matchId AND tipsport_zakladni_nabidka_moznosti.matchId = tipsport_zakladni_nabidka_zapasy.matchId)
                            WHEN shortName = 2 THEN rate / (SELECT rate FROM tipsport_zakladni_nabidka_moznosti WHERE tipsport_zakladni_nabidka_moznosti.shortName = 1 AND tipsport_zakladni_nabidka_zapasy.matchId = matchId AND tipsport_zakladni_nabidka_moznosti.matchId = tipsport_zakladni_nabidka_zapasy.matchId)
                            END AS vysledek */

        /*CASE 
                    WHEN shortName = 1 THEN rate / (SELECT rate FROM tipsport_zakladni_nabidka_moznosti WHERE tipsport_zakladni_nabidka_moznosti.shortName = 2 AND tipsport_zakladni_nabidka_moznosti.matchId = matchId AND tipsport_zakladni_nabidka_moznosti.matchId = tipsport_zakladni_nabidka_zapasy.matchId)
                    WHEN shortName = 2 THEN rate / (SELECT rate FROM tipsport_zakladni_nabidka_moznosti WHERE tipsport_zakladni_nabidka_moznosti.shortName = 1 AND tipsport_zakladni_nabidka_moznosti.matchId = matchId AND tipsport_zakladni_nabidka_moznosti.matchId = tipsport_zakladni_nabidka_zapasy.matchId)
                    END AS vysledek  */

        /*CASE 
                            WHEN shortName = 1 AND rate > (SELECT rate FROM tipsport_zakladni_nabidka_moznosti WHERE tipsport_zakladni_nabidka_moznosti.shortName = 2 AND tipsport_zakladni_nabidka_moznosti.matchId = matchId) THEN rate
                            WHEN shortName = 2 AND rate < (SELECT rate FROM tipsport_zakladni_nabidka_moznosti WHERE tipsport_zakladni_nabidka_moznosti.shortName = 1 AND tipsport_zakladni_nabidka_moznosti.matchId = matchId) THEN rate
                            END AS vysledek   */

                            //AND tipsport_zakladni_nabidka_moznosti.matchId = tipsport_zakladni_nabidka_zapasy.matchId

                            //$sql = "SELECT tipsport_zakladni_nabidka_zapasy.matchId as matchId, tipsport_zakladni_nabidka_moznosti.shortName as shortName, rate, name, (SELECT rate FROM tipsport_zakladni_nabidka_moznosti WHERE tipsport_zakladni_nabidka_moznosti.shortName = '2' AND tipsport_zakladni_nabidka_moznosti.matchId = matchId) FROM tipsport_zakladni_nabidka_souteze, tipsport_zakladni_nabidka_zapasy, tipsport_zakladni_nabidka_moznosti";

                             /*WHEN shortName = '1' AND rate >= @rateShortName2 THEN (rate / @rateShortName2)
                            WHEN shortName = '1' AND rate < @rateShortName2 THEN (@rateShortName2 / rate)
                            WHEN shortName = '2' AND rate >= @rateShortName1 THEN (rate / @rateShortName1)
                            WHEN shortName = '2' AND rate < @rateShortName1 THEN (@rateShortName1 / rate)*/

        /*$sql = "SELECT  @zapasId:=tipsport_zakladni_nabidka_zapasy.matchId,
                        @rateShortName2:=(SELECT rate FROM tipsport_zakladni_nabidka_moznosti WHERE tipsport_zakladni_nabidka_moznosti.shortName = '2' AND tipsport_zakladni_nabidka_moznosti.matchId = @zapasId),
                        @rateShortName1:=(SELECT rate FROM tipsport_zakladni_nabidka_moznosti WHERE tipsport_zakladni_nabidka_moznosti.shortName = '1' AND tipsport_zakladni_nabidka_moznosti.matchId = @zapasId),
                        tipsport_zakladni_nabidka_zapasy.matchId as matchId, tipsport_zakladni_nabidka_moznosti.shortName as shortName, rate, name,
                    MIN(CASE 
                            WHEN shortName = '1' AND rate >= @rateShortName2 THEN (rate / @rateShortName2)
                            WHEN shortName = '1' AND rate < @rateShortName2 THEN (@rateShortName2 / rate)
                            WHEN shortName = '2' AND rate >= @rateShortName1 THEN (rate / @rateShortName1)
                            WHEN shortName = '2' AND rate < @rateShortName1 THEN (@rateShortName1 / rate)
                            END) AS vysledek       
                FROM tipsport_zakladni_nabidka_souteze, tipsport_zakladni_nabidka_zapasy, tipsport_zakladni_nabidka_moznosti 
                WHERE (".$podminka.") AND tipsport_zakladni_nabidka_zapasy.matchId = tipsport_zakladni_nabidka_moznosti.matchId AND tipsport_zakladni_nabidka_souteze.id = tipsport_zakladni_nabidka_moznosti.id_souteze
                GROUP BY matchId
                ORDER BY vysledek ASC
                LIMIT ".$pocet."";
        //echo $sql;*/

        $sql_fortuna = "";
        if($sazkovka == "vse" || $sazkovka == "fortuna")
        {
            $data = $this->databaze->query("SELECT sport, competitionName FROM tipsport_zakladni_nabidka_souteze WHERE ".$podminka2);
            $sql_fortuna_podminka_souteze = "";
            $sql_fortuna_podminka_sporty = "";
            foreach ($data as $souteze) 
            {
                //echo $souteze["competitionName"]."<br />";
                $nazev_souzeze = substr($souteze["competitionName"],0,6);
                //echo $nazev_souzeze."<br />";
                if(is_numeric(substr($nazev_souzeze, 0,1)))
                {
                    $nazev_souzeze = str_replace(" ", "", $nazev_souzeze);
                }
                //echo $nazev_souzeze."<br />";

                //$sql_fortuna_podminka_souteze = $sql_fortuna_podminka_souteze."fortuna_nabidka_turnaje.nazev_turnaje LIKE '%".substr($souteze["competitionName"],0,5)."%' OR ";
                $sql_fortuna_podminka_souteze = $sql_fortuna_podminka_souteze."fortuna_nabidka_turnaje.nazev_turnaje LIKE '%".$nazev_souzeze."%' OR ";
                //echo explode("-",$souteze["sport"])[0]."<br />";
                $nazev_kategorie = explode(" - ",$souteze["sport"])[0];
                if($nazev_kategorie == "Lední hokej")
                {
                    $nazev_kategorie = "Hokej";
                }
                //echo $nazev_kategorie."<hr />";
                $sql_fortuna_podminka_sporty = $sql_fortuna_podminka_sporty."fortuna_nabidka_kategorie.nazev_kategorie LIKE '%".$nazev_kategorie."%' OR ";
            }
            $sql_fortuna_podminka_souteze = substr($sql_fortuna_podminka_souteze, 0, strlen($sql_fortuna_podminka_souteze)-4);
            $sql_fortuna_podminka_sporty = substr($sql_fortuna_podminka_sporty, 0, strlen($sql_fortuna_podminka_sporty)-4);
            /*SET @zapasId:=fortuna_nabidka_zapasy.id_zapasu;
                            SET @rateShortName2:=(SELECT fortuna_nabidka_sazky_moznosti.kurz_moznosti FROM fortuna_nabidka_sazky_moznosti WHERE fortuna_nabidka_sazky_moznosti.nazev_moznosti='2' AND fortuna_nabidka_sazky_moznosti.id_sazky=@zapasId AND fortuna_nabidka_sazky_moznosti.id_zapasu=@zapasId);
                            SET @rateShortName1:=(SELECT fortuna_nabidka_sazky_moznosti.kurz_moznosti FROM fortuna_nabidka_sazky_moznosti WHERE fortuna_nabidka_sazky_moznosti.nazev_moznosti='1' AND fortuna_nabidka_sazky_moznosti.id_sazky=@zapasId AND fortuna_nabidka_sazky_moznosti.id_zapasu=@zapasId);*/
            $sql_fortuna = "SELECT @zapasId:=fortuna_nabidka_zapasy.id_zapasu,
                                @rateShortName2:=(SELECT fortuna_nabidka_sazky_moznosti.kurz_moznosti FROM fortuna_nabidka_sazky_moznosti WHERE fortuna_nabidka_sazky_moznosti.nazev_moznosti='2' AND fortuna_nabidka_sazky_moznosti.id_sazky=@zapasId AND fortuna_nabidka_sazky_moznosti.id_zapasu=@zapasId),
                                @rateShortName1:=(SELECT fortuna_nabidka_sazky_moznosti.kurz_moznosti FROM fortuna_nabidka_sazky_moznosti WHERE fortuna_nabidka_sazky_moznosti.nazev_moznosti='1' AND fortuna_nabidka_sazky_moznosti.id_sazky=@zapasId AND fortuna_nabidka_sazky_moznosti.id_zapasu=@zapasId),
                                kurz_moznosti as nej_kurz, nazev_zapasu as name, nazev_kategorie as sport, nazev_turnaje as competitionName, odkaz as nabidka_url, fortuna_nabidka_zapasy.id_zapasu as matchId, nazev_moznosti as nejmensi_pozice, GROUP_CONCAT(nazev_moznosti) as shortName, GROUP_CONCAT(kurz_moznosti) as rate, GROUP_CONCAT(id_moznosti) as opportunityId,
                                MIN(CASE 
                                WHEN nazev_moznosti = '1' AND kurz_moznosti >= @rateShortName2 THEN (kurz_moznosti / @rateShortName2)
                                WHEN nazev_moznosti = '1' AND kurz_moznosti < @rateShortName2 THEN (@rateShortName2 / kurz_moznosti)
                                WHEN nazev_moznosti = '2' AND kurz_moznosti >= @rateShortName1 THEN (kurz_moznosti / @rateShortName1)
                                WHEN nazev_moznosti = '2' AND kurz_moznosti < @rateShortName1 THEN (@rateShortName1 / kurz_moznosti)
                                END) AS vysledek
                                FROM fortuna_nabidka_kategorie, fortuna_nabidka_sazky, fortuna_nabidka_sazky_moznosti, fortuna_nabidka_turnaje, fortuna_nabidka_zapasy 
                                WHERE (".$sql_fortuna_podminka_souteze.") AND (".$sql_fortuna_podminka_sporty.")
                                AND fortuna_nabidka_kategorie.id_kategorie = fortuna_nabidka_turnaje.id_kategorie 
                                AND fortuna_nabidka_turnaje.id_kategorie = fortuna_nabidka_zapasy.id_kategorie
                                AND fortuna_nabidka_zapasy.id_kategorie = fortuna_nabidka_sazky.id_kategorie
                                AND fortuna_nabidka_sazky.id_kategorie = fortuna_nabidka_sazky_moznosti.id_kategorie
                                AND fortuna_nabidka_turnaje.id_turnaje = fortuna_nabidka_zapasy.id_turnaje 
                                AND fortuna_nabidka_zapasy.id_turnaje = fortuna_nabidka_sazky.id_turnaje
                                AND fortuna_nabidka_sazky.id_turnaje = fortuna_nabidka_sazky_moznosti.id_turnaje
                                AND fortuna_nabidka_zapasy.id_zapasu = fortuna_nabidka_sazky.id_zapasu 
                                AND fortuna_nabidka_sazky.id_zapasu = fortuna_nabidka_sazky_moznosti.id_zapasu
                                AND fortuna_nabidka_sazky.id_sazky = fortuna_nabidka_sazky_moznosti.id_sazky
                                AND nazev_sazky = 'zápas'
                                AND nazev_moznosti !='12'
                                GROUP BY matchId ORDER BY vysledek ASC LIMIT ".$pocet."";
        }
        //echo $sql_fortuna;

        $sql_tipsport = "";
        if($sazkovka == "vse" || $sazkovka == "tipsport")
        {
        $sql_tipsport = "SELECT @zapasId:=tipsport_zakladni_nabidka_zapasy.matchId,
                        @rateShortName2:=(SELECT rate FROM tipsport_zakladni_nabidka_moznosti WHERE tipsport_zakladni_nabidka_moznosti.shortName = '2' AND tipsport_zakladni_nabidka_moznosti.matchId = @zapasId),
                        @rateShortName1:=(SELECT rate FROM tipsport_zakladni_nabidka_moznosti WHERE tipsport_zakladni_nabidka_moznosti.shortName = '1' AND tipsport_zakladni_nabidka_moznosti.matchId = @zapasId),
                        rate as nej_kurz, name, sport, competitionName, tipsport_zakladni_nabidka_souteze.url as nabidka_url, tipsport_zakladni_nabidka_zapasy.matchId as matchId, tipsport_zakladni_nabidka_moznosti.shortName as nejmensi_pozice, GROUP_CONCAT(tipsport_zakladni_nabidka_moznosti.shortName) as shortName, GROUP_CONCAT(tipsport_zakladni_nabidka_moznosti.rate) as rate, GROUP_CONCAT(tipsport_zakladni_nabidka_moznosti.opportunityId) as opportunityId,
                        MIN(CASE 
                            WHEN shortName = '1' AND rate >= @rateShortName2 THEN (rate / @rateShortName2)
                            WHEN shortName = '1' AND rate < @rateShortName2 THEN (@rateShortName2 / rate)
                            WHEN shortName = '2' AND rate >= @rateShortName1 THEN (rate / @rateShortName1)
                            WHEN shortName = '2' AND rate < @rateShortName1 THEN (@rateShortName1 / rate)
                            END) AS vysledek
                        FROM tipsport_zakladni_nabidka_souteze, tipsport_zakladni_nabidka_zapasy, tipsport_zakladni_nabidka_moznosti
                        WHERE (".$podminka.") AND tipsport_zakladni_nabidka_zapasy.matchId = tipsport_zakladni_nabidka_moznosti.matchId AND tipsport_zakladni_nabidka_souteze.id = tipsport_zakladni_nabidka_moznosti.id_souteze
                            GROUP BY matchId
                            ORDER BY vysledek ASC
                            LIMIT ".$pocet."";
        }
        $sql = "";
        if($sazkovka == "vse")
        {
            $sql = "(".$sql_fortuna.") UNION (".$sql_tipsport.") ORDER BY vysledek ASC";
        }
        else if($sazkovka == "tipsport")
        {
            $sql = $sql_tipsport;
        }
        else if($sazkovka == "fortuna")
        {
            $sql = $sql_fortuna;
        }
        //echo $sql;
        $data = $this->databaze->query($sql);

        $this->template->nabidky = array();
        $this->template->kurzy_pozice = array();
        $this->template->nabidky_kurzy = array();
        $this->template->nabidky_moznosti = array();
        $this->template->i = 0;
        $this->template->kurzy_pole = array();
        $this->template->nabidky_moznosti_id = array();
        $this->template->nabidky_moznosti_id_pole = array();
        $this->template->id_tiketu = array();
        $this->template->sazkovka = array();

        foreach($data as $prilezitosti)
        {

            /*$sql = "SELECT sport, competitionName, tipsport_zakladni_nabidka_souteze.url as nabidka_url, tipsport_zakladni_nabidka_zapasy.matchId as matchId, name, tipsport_zakladni_nabidka_zapasy.url as zapasy_url, GROUP_CONCAT(tipsport_zakladni_nabidka_moznosti.shortName) as shortName, GROUP_CONCAT(tipsport_zakladni_nabidka_moznosti.rate) as rate, GROUP_CONCAT(tipsport_zakladni_nabidka_moznosti.opportunityId) as opportunityId 
                    FROM tipsport_zakladni_nabidka_souteze, tipsport_zakladni_nabidka_zapasy, tipsport_zakladni_nabidka_moznosti 
                    WHERE (".$podminka.") AND tipsport_zakladni_nabidka_zapasy.matchId = tipsport_zakladni_nabidka_moznosti.matchId AND tipsport_zakladni_nabidka_souteze.id = tipsport_zakladni_nabidka_moznosti.id_souteze AND tipsport_zakladni_nabidka_zapasy.matchId =".$prilezitosti["matchId"]."
                    GROUP BY matchId";
            $nabidky = $this->databaze->fetch($sql);*/

            //echo var_dump($prilezitosti)."<br /><br />";
            //echo var_dump($nabidky)."<br /><br />";
            //echo $nabidky["sport"]."<br /><br />";
            //array_push($this->template->nabidky, $nabidky);
            
            if(strpos($prilezitosti["nabidka_url"],"tipsport") != false)
            {
                array_push($this->template->sazkovka, "Tipsport");
            }
            else
            {
                array_push($this->template->sazkovka, "Fortuna");   
            }

            array_push($this->template->nabidky, $prilezitosti);

            array_push($this->template->kurzy_pozice, $prilezitosti["nejmensi_pozice"]);
            //array_push($this->template->nabidky_kurzy, explode(",", end($this->template->nabidky)["rate"]));
            array_push($this->template->nabidky_kurzy, explode(",", $prilezitosti["rate"]));
            //array_push($this->template->nabidky_moznosti, explode(",", end($this->template->nabidky)["shortName"]));
            array_push($this->template->nabidky_moznosti, explode(",", $prilezitosti["shortName"]));
            //dump($prilezitosti["rate"]);
            //dump($prilezitosti["shortName"]);

            array_push($this->template->kurzy_pole, array_combine(end($this->template->nabidky_moznosti), end($this->template->nabidky_kurzy)));
            
            array_push($this->template->id_tiketu, 1);

            $this->template->i++;

        }
        //echo var_dump($this->template->nabidky);
        //echo $this->template->i;
        //$this->template->i--;
    }
}
