<?php

namespace App\Model;

use Nette;
use Nette\Security\Passwords;
use Nette\Utils\Strings;
use Nette\Database\Context;
use Nette\Database\Connection;
use Nette\Utils\Image;


/**
 * Users management.
 */
class FotogalerieManager
{
	use Nette\SmartObject;

	/** @var Nette\Database\Context */
	private $databaze;
	public function __construct(Nette\Database\Context $databaze)
	{
		$this->databaze = $databaze;
	}

	public function nahledovy_obrazek($path, $save, $width, $height) //udělá jenom výřez z obrázku původní velikosti
	{
		$info = getimagesize($path);
		$size = array($info[0], $info[1]);
		if ($info['mime'] == 'image/png') 
		{
			$src = imagecreatefrompng($path);
		} 
		else if ($info['mime'] == 'image/jpeg') 
		{
			$src = imagecreatefromjpeg($path);
		} 
		else if ($info['mime'] == 'image/gif') 
		{
			$src = imagecreatefromgif($path);
		} 
		else 
		{
			return false;
		}

		$thumb = imagecreatetruecolor($width, $height);

		$src_aspect = $size[0] / $size[1];
		$thumb_aspect = $width / $height;

		if ($src_aspect < $thumb_aspect) 
		{
			$scale = $width / $size[0];
			$new_size = array($width, $width / $src_aspect);
			$src_post = array(0, ($size[1] * $scale - $height) / $scale / 2);
		} 
		else if ($src_aspect > $thumb_aspect) 
		{
			$scale = $width / $size[1];
			$new_size = array($height * $src_aspect, $height);
			$src_post = array(($size[0] * $scale - $width) / $scale / 2, 0);
		} 
		else 
		{
			$new_size = array($width, $height);
			$src_post = array(0, 0);
		}
		$new_size[0] = max($new_size[0], 1);
		$new_size[1] = max($new_size[1], 1);

		imagecopyresampled($thumb, $src, 0, 0, $src_post[0], $src_post[1], $new_size[0], $new_size[1], $size[0], $size[1]);

		/*if($save === false)
		{
			return imagepng($thumb);
		} 
		else 
		{
			return imagepng($thumb, $save);
		}*/

		if ($info['mime'] == 'image/png') 
		{
			return imagepng($thumb, $save);
		} 
		else if ($info['mime'] == 'image/jpeg') 
		{
			return imagejpeg($thumb, $save);
		} 
		else if ($info['mime'] == 'image/gif') 
		{
			return imagegif($thumb, $save);
		} 
		else 
		{
			return false;
		}
	}

	public function zmenseni_obrazku($puvodni_obrazek, $zmenseny_obrazek)
	{
		$info = getimagesize($puvodni_obrazek);
		$velikost = array($info[0], $info[1]);
		if ($info['mime'] == 'image/png') 
		{
			$puvodni_obrazek = imagecreatefrompng($puvodni_obrazek);
		} 
		else if ($info['mime'] == 'image/jpeg') 
		{
			$puvodni_obrazek = imagecreatefromjpeg($puvodni_obrazek);
		} 
		else if ($info['mime'] == 'image/gif') 
		{
			$puvodni_obrazek = imagecreatefromgif($puvodni_obrazek);
		} 
		else 
		{
			return false;
		}

		/*$nova_sirka = $velikost[0] / 2;
		$nova_vyska = $velikost[1] / 2;
		$novy_obrazek = imagecreatetruecolor($nova_sirka, $nova_vyska);
		imagecopyresampled($novy_obrazek, $puvodni_obrazek, 0, 0, 0, 0, $nova_sirka, $nova_vyska, $velikost[0], $velikost[1]);*/


		$nova_sirka = 200;
		$nova_vyska = $velikost[1] / ($velikost[0] / $nova_sirka);
		$novy_obrazek = imagecreatetruecolor($nova_sirka, $nova_vyska);
		imagecopyresampled($novy_obrazek, $puvodni_obrazek, 0, 0, 0, 0, $nova_sirka, $nova_vyska, $velikost[0], $velikost[1]);

		//tady se bude obrázek ořezávat, aby byl jednotný tvar...
		$nahled_sirka = 200;
		$nahled_vyska = 150;
		$nahled = imagecreatetruecolor($nahled_sirka, $nahled_vyska);

		$pomer_stran = $nova_sirka / $nova_vyska;
		$nahled_pomer_stran = $nahled_sirka / $nahled_vyska;

		if ($pomer_stran < $nahled_pomer_stran) 
		{
			$meritko = $nahled_sirka / $nova_sirka;
			$nahled_velikost = array($nahled_sirka, $nahled_sirka / $pomer_stran);
			$pozice = array(0, ($nova_vyska * $meritko - $nahled_vyska) / $meritko / 2);
		} 
		else if ($pomer_stran > $nahled_pomer_stran) 
		{
			$meritko = $nahled_sirka / $nova_vyska;
			$nahled_velikost = array($nahled_vyska * $pomer_stran, $nahled_vyska);
			$pozice = array(($nova_sirka * $meritko - $nahled_sirka) / $meritko / 2, 0);
		} 
		else 
		{
			$nahled_velikost = array($nahled_sirka, $nahled_vyska);
			$pozice = array(0, 0);
		}
		$nahled_velikost[0] = max($nahled_velikost[0], 1);
		$nahled_velikost[1] = max($nahled_velikost[1], 1);

		imagecopyresampled($nahled, $novy_obrazek, 0, 0, $pozice[0], $pozice[1], $nahled_velikost[0], $nahled_velikost[1], $nova_sirka, $nova_vyska);

		//$this->nahledovy_obrazek($novy_obrazek, $zmenseny_obrazek, 250, 250);

		/*if ($info['mime'] == 'image/png') 
		{
			return imagepng($novy_obrazek, $zmenseny_obrazek);
		} 
		else if ($info['mime'] == 'image/jpeg') 
		{
			return imagejpeg($novy_obrazek, $zmenseny_obrazek);
		} 
		else if ($info['mime'] == 'image/gif') 
		{
			return imagegif($novy_obrazek, $zmenseny_obrazek);
		} 
		else 
		{
			return false;
		}	*/

		if ($info['mime'] == 'image/png') 
		{
			return imagepng($nahled, $zmenseny_obrazek);
		} 
		else if ($info['mime'] == 'image/jpeg') 
		{
			return imagejpeg($nahled, $zmenseny_obrazek);
		} 
		else if ($info['mime'] == 'image/gif') 
		{
			return imagegif($nahled, $zmenseny_obrazek);
		} 
		else 
		{
			return false;
		}	

	}


	public function vytvorit_nahled($puvodni_obrazek, $zmenseny_obrazek)
	{
		$obrazek = Image::fromFile($puvodni_obrazek);
		$obrazek->resize(200, 150, Image::EXACT | Image::STRETCH);
		//$obrazek->resize(200, 150, Image::STRETCH);
		$obrazek->save($zmenseny_obrazek);
	}

	public function nahrat_obrazek($obrazky, $soubory, $id_galerie, &$zpravy)
	{
		$j = 0;
		for ($i = 0; $i < count($obrazky); $i++) 
		{
			$umisteni_obrazku = "obrazky/";
			$platne_koncovky = array("jpeg", "jpg", "png");      // Extensions which are allowed.
			$ext = explode('.', $obrazky[$i]); 
			$koncovka = $ext[1]; // Store extensions in the variable.
			$umisteni_nahledu = $umisteni_obrazku . "nahledy/" . Strings::webalize($ext[0]).".".$ext[1];
			$umisteni_obrazku = $umisteni_obrazku . Strings::webalize($ext[0]).".".$ext[1];
			$j = $j + 1;
			if (in_array($koncovka, $platne_koncovky) && !file_exists($umisteni_obrazku)) 
			{
				if(move_uploaded_file($soubory[$i], $umisteni_obrazku))
				{
					$this->databaze->query("INSERT INTO nahrane_fotky (id_fotogalerie, nazev_fotky, url_fotky) VALUES('".$id_galerie."','".addslashes($ext[0])."','".$umisteni_obrazku."')");
					
					//$this->nahledovy_obrazek($target_path, $save, 250, 250);
					//$this->zmenseni_obrazku($umisteni_obrazku, $umisteni_nahledu);
					$this->vytvorit_nahled($umisteni_obrazku, $umisteni_nahledu);
					
					$data = $this->databaze->fetch("SELECT id_fotky FROM nahrane_fotky WHERE nazev_fotky='".addslashes($ext[0])."'");
					$this->databaze->query("INSERT INTO nahrane_fotky_nahledy (id_fotky, id_fotogalerie, nazev_nahledu, url_nahledu) VALUES('".$data["id_fotky"]."','".$id_galerie."','".addslashes($ext[0])."','".$umisteni_nahledu."')");
					$zpravy[$i] = "Obrázek ".$obrazky[$i]." byl nahrán bez problémů.";
				} 
				else 
				{     //  If File Was Not Moved.
					$zpravy[$i] = "Při nahrávání obrázku ".$obrazky[$i]." se vyskytla chyba!";
				}
			} 
			else 
			{
				$zpravy[$i] = "Soubor ".$obrazky[$i]." nebyl nahrán! Pravděpodobně se nejedná o obrázek nebo už byl obrázek se shodným jmémen v minulosti nahrán. 
													Toto vyřešíte jeho přejmenováním a následným vložením v aktualizaci této fotogalerie";
			}
		}
	}

}