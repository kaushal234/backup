<?php
require_once('fpdf/fpdf.php');

$Big5_widths=array(' '=>250,'!'=>250,'"'=>408,'#'=>668,'$'=>490,'%'=>875,'&'=>698,'\''=>250,
	'('=>240,')'=>240,'*'=>417,'+'=>667,','=>250,'-'=>313,'.'=>250,'/'=>520,'0'=>500,'1'=>500,
	'2'=>500,'3'=>500,'4'=>500,'5'=>500,'6'=>500,'7'=>500,'8'=>500,'9'=>500,':'=>250,';'=>250,
	'<'=>667,'='=>667,'>'=>667,'?'=>396,'@'=>921,'A'=>677,'B'=>615,'C'=>719,'D'=>760,'E'=>625,
	'F'=>552,'G'=>771,'H'=>802,'I'=>354,'J'=>354,'K'=>781,'L'=>604,'M'=>927,'N'=>750,'O'=>823,
	'P'=>563,'Q'=>823,'R'=>729,'S'=>542,'T'=>698,'U'=>771,'V'=>729,'W'=>948,'X'=>771,'Y'=>677,
	'Z'=>635,'['=>344,'\\'=>520,']'=>344,'^'=>469,'_'=>500,'`'=>250,'a'=>469,'b'=>521,'c'=>427,
	'd'=>521,'e'=>438,'f'=>271,'g'=>469,'h'=>531,'i'=>250,'j'=>250,'k'=>458,'l'=>240,'m'=>802,
	'n'=>531,'o'=>500,'p'=>521,'q'=>521,'r'=>365,'s'=>333,'t'=>292,'u'=>521,'v'=>458,'w'=>677,
	'x'=>479,'y'=>458,'z'=>427,'{'=>480,'|'=>496,'}'=>480,'~'=>667);

$GB_widths=array(' '=>207,'!'=>270,'"'=>342,'#'=>467,'$'=>462,'%'=>797,'&'=>710,'\''=>239,
	'('=>374,')'=>374,'*'=>423,'+'=>605,','=>238,'-'=>375,'.'=>238,'/'=>334,'0'=>462,'1'=>462,
	'2'=>462,'3'=>462,'4'=>462,'5'=>462,'6'=>462,'7'=>462,'8'=>462,'9'=>462,':'=>238,';'=>238,
	'<'=>605,'='=>605,'>'=>605,'?'=>344,'@'=>748,'A'=>684,'B'=>560,'C'=>695,'D'=>739,'E'=>563,
	'F'=>511,'G'=>729,'H'=>793,'I'=>318,'J'=>312,'K'=>666,'L'=>526,'M'=>896,'N'=>758,'O'=>772,
	'P'=>544,'Q'=>772,'R'=>628,'S'=>465,'T'=>607,'U'=>753,'V'=>711,'W'=>972,'X'=>647,'Y'=>620,
	'Z'=>607,'['=>374,'\\'=>333,']'=>374,'^'=>606,'_'=>500,'`'=>239,'a'=>417,'b'=>503,'c'=>427,
	'd'=>529,'e'=>415,'f'=>264,'g'=>444,'h'=>518,'i'=>241,'j'=>230,'k'=>495,'l'=>228,'m'=>793,
	'n'=>527,'o'=>524,'p'=>524,'q'=>504,'r'=>338,'s'=>336,'t'=>277,'u'=>517,'v'=>450,'w'=>652,
	'x'=>466,'y'=>452,'z'=>407,'{'=>370,'|'=>258,'}'=>370,'~'=>605);

class PDF_Chinese extends FPDF
{
	protected $T128;                                         // Tableau des codes 128
	protected $ABCset = "";                                  // jeu des caractères éligibles au C128
	protected $Aset = "";                                    // Set A du jeu des caractères éligibles
	protected $Bset = "";                                    // Set B du jeu des caractères éligibles
	protected $Cset = "";                                    // Set C du jeu des caractères éligibles
	protected $SetFrom;                                      // Convertisseur source des jeux vers le tableau
	protected $SetTo;                                        // Convertisseur destination des jeux vers le tableau
	protected $JStart = array("A"=>103, "B"=>104, "C"=>105); // Caractères de sélection de jeu au début du C128
	protected $JSwap = array("A"=>101, "B"=>100, "C"=>99);   // Caractères de changement de jeu

//____________________________ Extension du constructeur _______________________
	function __construct($orientation = 'P', $unit = 'mm', $format = 'A4')
	{

		parent::__construct($orientation, $unit, $format);

		$this->T128[] = [2, 1, 2, 2, 2, 2];           //0 : [ ]               // composition des caractères
		$this->T128[] = [2, 2, 2, 1, 2, 2];           //1 : [!]
		$this->T128[] = [2, 2, 2, 2, 2, 1];           //2 : ["]
		$this->T128[] = [1, 2, 1, 2, 2, 3];           //3 : [#]
		$this->T128[] = [1, 2, 1, 3, 2, 2];           //4 : [$]
		$this->T128[] = [1, 3, 1, 2, 2, 2];           //5 : [%]
		$this->T128[] = [1, 2, 2, 2, 1, 3];           //6 : [&]
		$this->T128[] = [1, 2, 2, 3, 1, 2];           //7 : [']
		$this->T128[] = [1, 3, 2, 2, 1, 2];           //8 : [(]
		$this->T128[] = [2, 2, 1, 2, 1, 3];           //9 : [)]
		$this->T128[] = [2, 2, 1, 3, 1, 2];           //10 : [*]
		$this->T128[] = [2, 3, 1, 2, 1, 2];           //11 : [+]
		$this->T128[] = [1, 1, 2, 2, 3, 2];           //12 : [,]
		$this->T128[] = [1, 2, 2, 1, 3, 2];           //13 : [-]
		$this->T128[] = [1, 2, 2, 2, 3, 1];           //14 : [.]
		$this->T128[] = [1, 1, 3, 2, 2, 2];           //15 : [/]
		$this->T128[] = [1, 2, 3, 1, 2, 2];           //16 : [0]
		$this->T128[] = [1, 2, 3, 2, 2, 1];           //17 : [1]
		$this->T128[] = [2, 2, 3, 2, 1, 1];           //18 : [2]
		$this->T128[] = [2, 2, 1, 1, 3, 2];           //19 : [3]
		$this->T128[] = [2, 2, 1, 2, 3, 1];           //20 : [4]
		$this->T128[] = [2, 1, 3, 2, 1, 2];           //21 : [5]
		$this->T128[] = [2, 2, 3, 1, 1, 2];           //22 : [6]
		$this->T128[] = [3, 1, 2, 1, 3, 1];           //23 : [7]
		$this->T128[] = [3, 1, 1, 2, 2, 2];           //24 : [8]
		$this->T128[] = [3, 2, 1, 1, 2, 2];           //25 : [9]
		$this->T128[] = [3, 2, 1, 2, 2, 1];           //26 : [:]
		$this->T128[] = [3, 1, 2, 2, 1, 2];           //27 : [;]
		$this->T128[] = [3, 2, 2, 1, 1, 2];           //28 : [<]
		$this->T128[] = [3, 2, 2, 2, 1, 1];           //29 : [=]
		$this->T128[] = [2, 1, 2, 1, 2, 3];           //30 : [>]
		$this->T128[] = [2, 1, 2, 3, 2, 1];           //31 : [?]
		$this->T128[] = [2, 3, 2, 1, 2, 1];           //32 : [@]
		$this->T128[] = [1, 1, 1, 3, 2, 3];           //33 : [A]
		$this->T128[] = [1, 3, 1, 1, 2, 3];           //34 : [B]
		$this->T128[] = [1, 3, 1, 3, 2, 1];           //35 : [C]
		$this->T128[] = [1, 1, 2, 3, 1, 3];           //36 : [D]
		$this->T128[] = [1, 3, 2, 1, 1, 3];           //37 : [E]
		$this->T128[] = [1, 3, 2, 3, 1, 1];           //38 : [F]
		$this->T128[] = [2, 1, 1, 3, 1, 3];           //39 : [G]
		$this->T128[] = [2, 3, 1, 1, 1, 3];           //40 : [H]
		$this->T128[] = [2, 3, 1, 3, 1, 1];           //41 : [I]
		$this->T128[] = [1, 1, 2, 1, 3, 3];           //42 : [J]
		$this->T128[] = [1, 1, 2, 3, 3, 1];           //43 : [K]
		$this->T128[] = [1, 3, 2, 1, 3, 1];           //44 : [L]
		$this->T128[] = [1, 1, 3, 1, 2, 3];           //45 : [M]
		$this->T128[] = [1, 1, 3, 3, 2, 1];           //46 : [N]
		$this->T128[] = [1, 3, 3, 1, 2, 1];           //47 : [O]
		$this->T128[] = [3, 1, 3, 1, 2, 1];           //48 : [P]
		$this->T128[] = [2, 1, 1, 3, 3, 1];           //49 : [Q]
		$this->T128[] = [2, 3, 1, 1, 3, 1];           //50 : [R]
		$this->T128[] = [2, 1, 3, 1, 1, 3];           //51 : [S]
		$this->T128[] = [2, 1, 3, 3, 1, 1];           //52 : [T]
		$this->T128[] = [2, 1, 3, 1, 3, 1];           //53 : [U]
		$this->T128[] = [3, 1, 1, 1, 2, 3];           //54 : [V]
		$this->T128[] = [3, 1, 1, 3, 2, 1];           //55 : [W]
		$this->T128[] = [3, 3, 1, 1, 2, 1];           //56 : [X]
		$this->T128[] = [3, 1, 2, 1, 1, 3];           //57 : [Y]
		$this->T128[] = [3, 1, 2, 3, 1, 1];           //58 : [Z]
		$this->T128[] = [3, 3, 2, 1, 1, 1];           //59 : [[]
		$this->T128[] = [3, 1, 4, 1, 1, 1];           //60 : [\]
		$this->T128[] = [2, 2, 1, 4, 1, 1];           //61 : []]
		$this->T128[] = [4, 3, 1, 1, 1, 1];           //62 : [^]
		$this->T128[] = [1, 1, 1, 2, 2, 4];           //63 : [_]
		$this->T128[] = [1, 1, 1, 4, 2, 2];           //64 : [`]
		$this->T128[] = [1, 2, 1, 1, 2, 4];           //65 : [a]
		$this->T128[] = [1, 2, 1, 4, 2, 1];           //66 : [b]
		$this->T128[] = [1, 4, 1, 1, 2, 2];           //67 : [c]
		$this->T128[] = [1, 4, 1, 2, 2, 1];           //68 : [d]
		$this->T128[] = [1, 1, 2, 2, 1, 4];           //69 : [e]
		$this->T128[] = [1, 1, 2, 4, 1, 2];           //70 : [f]
		$this->T128[] = [1, 2, 2, 1, 1, 4];           //71 : [g]
		$this->T128[] = [1, 2, 2, 4, 1, 1];           //72 : [h]
		$this->T128[] = [1, 4, 2, 1, 1, 2];           //73 : [i]
		$this->T128[] = [1, 4, 2, 2, 1, 1];           //74 : [j]
		$this->T128[] = [2, 4, 1, 2, 1, 1];           //75 : [k]
		$this->T128[] = [2, 2, 1, 1, 1, 4];           //76 : [l]
		$this->T128[] = [4, 1, 3, 1, 1, 1];           //77 : [m]
		$this->T128[] = [2, 4, 1, 1, 1, 2];           //78 : [n]
		$this->T128[] = [1, 3, 4, 1, 1, 1];           //79 : [o]
		$this->T128[] = [1, 1, 1, 2, 4, 2];           //80 : [p]
		$this->T128[] = [1, 2, 1, 1, 4, 2];           //81 : [q]
		$this->T128[] = [1, 2, 1, 2, 4, 1];           //82 : [r]
		$this->T128[] = [1, 1, 4, 2, 1, 2];           //83 : [s]
		$this->T128[] = [1, 2, 4, 1, 1, 2];           //84 : [t]
		$this->T128[] = [1, 2, 4, 2, 1, 1];           //85 : [u]
		$this->T128[] = [4, 1, 1, 2, 1, 2];           //86 : [v]
		$this->T128[] = [4, 2, 1, 1, 1, 2];           //87 : [w]
		$this->T128[] = [4, 2, 1, 2, 1, 1];           //88 : [x]
		$this->T128[] = [2, 1, 2, 1, 4, 1];           //89 : [y]
		$this->T128[] = [2, 1, 4, 1, 2, 1];           //90 : [z]
		$this->T128[] = [4, 1, 2, 1, 2, 1];           //91 : [{]
		$this->T128[] = [1, 1, 1, 1, 4, 3];           //92 : [|]
		$this->T128[] = [1, 1, 1, 3, 4, 1];           //93 : [}]
		$this->T128[] = [1, 3, 1, 1, 4, 1];           //94 : [~]
		$this->T128[] = [1, 1, 4, 1, 1, 3];           //95 : [DEL]
		$this->T128[] = [1, 1, 4, 3, 1, 1];           //96 : [FNC3]
		$this->T128[] = [4, 1, 1, 1, 1, 3];           //97 : [FNC2]
		$this->T128[] = [4, 1, 1, 3, 1, 1];           //98 : [SHIFT]
		$this->T128[] = [1, 1, 3, 1, 4, 1];           //99 : [Cswap]
		$this->T128[] = [1, 1, 4, 1, 3, 1];           //100 : [Bswap]
		$this->T128[] = [3, 1, 1, 1, 4, 1];           //101 : [Aswap]
		$this->T128[] = [4, 1, 1, 1, 3, 1];           //102 : [FNC1]
		$this->T128[] = [2, 1, 1, 4, 1, 2];           //103 : [Astart]
		$this->T128[] = [2, 1, 1, 2, 1, 4];           //104 : [Bstart]
		$this->T128[] = [2, 1, 1, 2, 3, 2];           //105 : [Cstart]
		$this->T128[] = [2, 3, 3, 1, 1, 1];           //106 : [STOP]
		$this->T128[] = [2, 1];                       //107 : [END BAR]

		for ($i = 32; $i <= 95; $i++) {                                            // jeux de caractères
			$this->ABCset .= chr($i);
		}
		$this->Aset = $this->ABCset;
		$this->Bset = $this->ABCset;

		for ($i = 0; $i <= 31; $i++) {
			$this->ABCset .= chr($i);
			$this->Aset .= chr($i);
		}
		for ($i = 96; $i <= 127; $i++) {
			$this->ABCset .= chr($i);
			$this->Bset .= chr($i);
		}
		for ($i = 200; $i <= 210; $i++) {                                           // controle 128
			$this->ABCset .= chr($i);
			$this->Aset .= chr($i);
			$this->Bset .= chr($i);
		}
		$this->Cset = "0123456789" . chr(206);

		for ($i = 0; $i < 96; $i++) {                                                   // convertisseurs des jeux A & B
			@$this->SetFrom["A"] .= chr($i);
			@$this->SetFrom["B"] .= chr($i + 32);
			@$this->SetTo["A"] .= chr(($i < 32) ? $i + 64 : $i - 32);
			@$this->SetTo["B"] .= chr($i);
		}
		for ($i = 96; $i < 107; $i++) {                                                 // contrôle des jeux A & B
			@$this->SetFrom["A"] .= chr($i + 104);
			@$this->SetFrom["B"] .= chr($i + 104);
			@$this->SetTo["A"] .= chr($i);
			@$this->SetTo["B"] .= chr($i);
		}
	}

//________________ Fonction encodage et dessin du code 128 _____________________
	function Code128($x, $y, $code, $w, $h)
	{
		$Aguid = "";                                                                      // Création des guides de choix ABC
		$Bguid = "";
		$Cguid = "";
		for ($i = 0; $i < strlen($code); $i++) {
			$needle = substr($code, $i, 1);
			$Aguid .= ((strpos($this->Aset, $needle) === false) ? "N" : "O");
			$Bguid .= ((strpos($this->Bset, $needle) === false) ? "N" : "O");
			$Cguid .= ((strpos($this->Cset, $needle) === false) ? "N" : "O");
		}

		$SminiC = "OOOO";
		$IminiC = 4;

		$crypt = "";
		while ($code > "") {
			// BOUCLE PRINCIPALE DE CODAGE
			$i = strpos($Cguid, $SminiC);                                                // forçage du jeu C, si possible
			if ($i !== false) {
				$Aguid [$i] = "N";
				$Bguid [$i] = "N";
			}

			if (substr($Cguid, 0, $IminiC) == $SminiC) {                                  // jeu C
				$crypt .= chr(($crypt > "") ? $this->JSwap["C"] : $this->JStart["C"]);  // début Cstart, sinon Cswap
				$made = strpos($Cguid, "N");                                             // étendu du set C
				if ($made === false) {
					$made = strlen($Cguid);
				}
				if (fmod($made, 2) == 1) {
					$made--;                                                            // seulement un nombre pair
				}
				for ($i = 0; $i < $made; $i += 2) {
					$crypt .= chr(strval(substr($code, $i, 2)));                          // conversion 2 par 2
				}
				$jeu = "C";
			} else {
				$madeA = strpos($Aguid, "N");                                            // étendu du set A
				if ($madeA === false) {
					$madeA = strlen($Aguid);
				}
				$madeB = strpos($Bguid, "N");                                            // étendu du set B
				if ($madeB === false) {
					$madeB = strlen($Bguid);
				}
				$made = (($madeA < $madeB) ? $madeB : $madeA);                         // étendu traitée
				$jeu = (($madeA < $madeB) ? "B" : "A");                                // Jeu en cours

				$crypt .= chr(($crypt > "") ? $this->JSwap[$jeu] : $this->JStart[$jeu]); // début start, sinon swap

				$crypt .= strtr(substr($code, 0, $made), $this->SetFrom[$jeu], $this->SetTo[$jeu]); // conversion selon jeu

			}
			$code = substr($code, $made);                                           // raccourcir légende et guides de la zone traitée
			$Aguid = substr($Aguid, $made);
			$Bguid = substr($Bguid, $made);
			$Cguid = substr($Cguid, $made);
		}                                                                          // FIN BOUCLE PRINCIPALE

		$check = ord($crypt[0]);                                                   // calcul de la somme de contrôle
		for ($i = 0; $i < strlen($crypt); $i++) {
			$check += (ord($crypt[$i]) * $i);
		}
		$check %= 103;

		$crypt .= chr($check) . chr(106) . chr(107);                               // Chaine cryptée complète

		$i = (strlen($crypt) * 11) - 8;                                            // calcul de la largeur du module
		$modul = $w / $i;

		for ($i = 0; $i < strlen($crypt); $i++) {                                      // BOUCLE D'IMPRESSION
			$c = $this->T128[ord($crypt[$i])];
			for ($j = 0; $j < count($c); $j++) {
				$this->Rect($x, $y, $c[$j] * $modul, $h, "F");
				$x += ($c[$j++] + $c[$j]) * $modul;
			}
		}
	}

	function Code39($x, $y, $code, $ext = true, $cks = false, $w = 0.4, $h = 20, $wide = true)
	{

		//Display code
		$this->SetFont('Arial', '', 10);
		$this->Text($x, $y + $h + 4, $code);

		if ($ext) {
			//Extended encoding
			$code = $this->encode_code39_ext($code);
		} else {
			//Convert to upper case
			$code = strtoupper($code);
			//Check validity
			if (!preg_match('|^[0-9A-Z. $/+%-]*$|', $code))
				$this->Error('Invalid barcode value: ' . $code);
		}

		//Compute checksum
		if ($cks)
			$code .= $this->checksum_code39($code);

		//Add start and stop characters
		$code = '*' . $code . '*';

		//Conversion tables
		$narrow_encoding = [
			'0' => '101001101101', '1' => '110100101011', '2' => '101100101011',
			'3' => '110110010101', '4' => '101001101011', '5' => '110100110101',
			'6' => '101100110101', '7' => '101001011011', '8' => '110100101101',
			'9' => '101100101101', 'A' => '110101001011', 'B' => '101101001011',
			'C' => '110110100101', 'D' => '101011001011', 'E' => '110101100101',
			'F' => '101101100101', 'G' => '101010011011', 'H' => '110101001101',
			'I' => '101101001101', 'J' => '101011001101', 'K' => '110101010011',
			'L' => '101101010011', 'M' => '110110101001', 'N' => '101011010011',
			'O' => '110101101001', 'P' => '101101101001', 'Q' => '101010110011',
			'R' => '110101011001', 'S' => '101101011001', 'T' => '101011011001',
			'U' => '110010101011', 'V' => '100110101011', 'W' => '110011010101',
			'X' => '100101101011', 'Y' => '110010110101', 'Z' => '100110110101',
			'-' => '100101011011', '.' => '110010101101', ' ' => '100110101101',
			'*' => '100101101101', '$' => '100100100101', '/' => '100100101001',
			'+' => '100101001001', '%' => '101001001001'];

		$wide_encoding = [
			'0' => '101000111011101', '1' => '111010001010111', '2' => '101110001010111',
			'3' => '111011100010101', '4' => '101000111010111', '5' => '111010001110101',
			'6' => '101110001110101', '7' => '101000101110111', '8' => '111010001011101',
			'9' => '101110001011101', 'A' => '111010100010111', 'B' => '101110100010111',
			'C' => '111011101000101', 'D' => '101011100010111', 'E' => '111010111000101',
			'F' => '101110111000101', 'G' => '101010001110111', 'H' => '111010100011101',
			'I' => '101110100011101', 'J' => '101011100011101', 'K' => '111010101000111',
			'L' => '101110101000111', 'M' => '111011101010001', 'N' => '101011101000111',
			'O' => '111010111010001', 'P' => '101110111010001', 'Q' => '101010111000111',
			'R' => '111010101110001', 'S' => '101110101110001', 'T' => '101011101110001',
			'U' => '111000101010111', 'V' => '100011101010111', 'W' => '111000111010101',
			'X' => '100010111010111', 'Y' => '111000101110101', 'Z' => '100011101110101',
			'-' => '100010101110111', '.' => '111000101011101', ' ' => '100011101011101',
			'*' => '100010111011101', '$' => '100010001000101', '/' => '100010001010001',
			'+' => '100010100010001', '%' => '101000100010001'];

		$encoding = $wide ? $wide_encoding : $narrow_encoding;

		//Inter-character spacing
		$gap = ($w > 0.29) ? '00' : '0';

		//Convert to bars
		$encode = '';
		for ($i = 0; $i < strlen($code); $i++)
			$encode .= $encoding[$code[$i]] . $gap;

		//Draw bars
		$this->draw_code39($encode, $x, $y, $w, $h);
	}

	function checksum_code39($code)
	{

		//Compute the modulo 43 checksum

		$chars = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
			'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K',
			'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V',
			'W', 'X', 'Y', 'Z', '-', '.', ' ', '$', '/', '+', '%'];
		$sum = 0;
		for ($i = 0; $i < strlen($code); $i++) {
			$a = array_keys($chars, $code[$i]);
			$sum += $a[0];
		}
		$r = $sum % 43;
		return $chars[$r];
	}

	function encode_code39_ext($code)
	{

		//Encode characters in extended mode

		$encode = [
			chr(0) => '%U', chr(1) => '$A', chr(2) => '$B', chr(3) => '$C',
			chr(4) => '$D', chr(5) => '$E', chr(6) => '$F', chr(7) => '$G',
			chr(8) => '$H', chr(9) => '$I', chr(10) => '$J', chr(11) => '£K',
			chr(12) => '$L', chr(13) => '$M', chr(14) => '$N', chr(15) => '$O',
			chr(16) => '$P', chr(17) => '$Q', chr(18) => '$R', chr(19) => '$S',
			chr(20) => '$T', chr(21) => '$U', chr(22) => '$V', chr(23) => '$W',
			chr(24) => '$X', chr(25) => '$Y', chr(26) => '$Z', chr(27) => '%A',
			chr(28) => '%B', chr(29) => '%C', chr(30) => '%D', chr(31) => '%E',
			chr(32) => ' ', chr(33) => '/A', chr(34) => '/B', chr(35) => '/C',
			chr(36) => '/D', chr(37) => '/E', chr(38) => '/F', chr(39) => '/G',
			chr(40) => '/H', chr(41) => '/I', chr(42) => '/J', chr(43) => '/K',
			chr(44) => '/L', chr(45) => '-', chr(46) => '.', chr(47) => '/O',
			chr(48) => '0', chr(49) => '1', chr(50) => '2', chr(51) => '3',
			chr(52) => '4', chr(53) => '5', chr(54) => '6', chr(55) => '7',
			chr(56) => '8', chr(57) => '9', chr(58) => '/Z', chr(59) => '%F',
			chr(60) => '%G', chr(61) => '%H', chr(62) => '%I', chr(63) => '%J',
			chr(64) => '%V', chr(65) => 'A', chr(66) => 'B', chr(67) => 'C',
			chr(68) => 'D', chr(69) => 'E', chr(70) => 'F', chr(71) => 'G',
			chr(72) => 'H', chr(73) => 'I', chr(74) => 'J', chr(75) => 'K',
			chr(76) => 'L', chr(77) => 'M', chr(78) => 'N', chr(79) => 'O',
			chr(80) => 'P', chr(81) => 'Q', chr(82) => 'R', chr(83) => 'S',
			chr(84) => 'T', chr(85) => 'U', chr(86) => 'V', chr(87) => 'W',
			chr(88) => 'X', chr(89) => 'Y', chr(90) => 'Z', chr(91) => '%K',
			chr(92) => '%L', chr(93) => '%M', chr(94) => '%N', chr(95) => '%O',
			chr(96) => '%W', chr(97) => '+A', chr(98) => '+B', chr(99) => '+C',
			chr(100) => '+D', chr(101) => '+E', chr(102) => '+F', chr(103) => '+G',
			chr(104) => '+H', chr(105) => '+I', chr(106) => '+J', chr(107) => '+K',
			chr(108) => '+L', chr(109) => '+M', chr(110) => '+N', chr(111) => '+O',
			chr(112) => '+P', chr(113) => '+Q', chr(114) => '+R', chr(115) => '+S',
			chr(116) => '+T', chr(117) => '+U', chr(118) => '+V', chr(119) => '+W',
			chr(120) => '+X', chr(121) => '+Y', chr(122) => '+Z', chr(123) => '%P',
			chr(124) => '%Q', chr(125) => '%R', chr(126) => '%S', chr(127) => '%T'];

		$code_ext = '';
		for ($i = 0; $i < strlen($code); $i++) {
			if (ord($code[$i]) > 127)
				$this->Error('Invalid character: ' . $code[$i]);
			$code_ext .= $encode[$code[$i]];
		}
		return $code_ext;
	}

	function draw_code39($code, $x, $y, $w, $h)
	{

		//Draw bars

		for ($i = 0; $i < strlen($code); $i++) {
			if ($code[$i] == '1')
				$this->Rect($x + $i * $w, $y, $w, $h, 'F');
		}
	}

	function AddCIDFont($family, $style, $name, $cw, $CMap, $registry)
	{
		$fontkey = strtolower($family) . strtoupper($style);
		if (isset($this->fonts[$fontkey]))
			$this->Error("Font already added: $family $style");
		$i = count($this->fonts) + 1;
		$name = str_replace(' ', '', $name);
		$this->fonts[$fontkey] = ['i' => $i, 'type' => 'Type0', 'name' => $name, 'up' => -130, 'ut' => 40, 'cw' => $cw, 'CMap' => $CMap, 'registry' => $registry];
	}

	function AddCIDFonts($family, $name, $cw, $CMap, $registry)
	{
		$this->AddCIDFont($family, '', $name, $cw, $CMap, $registry);
		$this->AddCIDFont($family, 'B', $name . ',Bold', $cw, $CMap, $registry);
		$this->AddCIDFont($family, 'I', $name . ',Italic', $cw, $CMap, $registry);
		$this->AddCIDFont($family, 'BI', $name . ',BoldItalic', $cw, $CMap, $registry);
	}

	function AddBig5Font($family = 'Big5', $name = 'MSungStd-Light-Acro')
	{
		//Add Big5 font with proportional Latin
		$cw = $GLOBALS['Big5_widths'];
		$CMap = 'ETenms-B5-H';
		$registry = ['ordering' => 'CNS1', 'supplement' => 0];
		$this->AddCIDFonts($family, $name, $cw, $CMap, $registry);
	}

	function AddBig5hwFont($family = 'Big5-hw', $name = 'MSungStd-Light-Acro')
	{
		//Add Big5 font with half-witdh Latin
		for ($i = 32; $i <= 126; $i++)
			$cw[chr($i)] = 500;
		$CMap = 'ETen-B5-H';
		$registry = ['ordering' => 'CNS1', 'supplement' => 0];
		$this->AddCIDFonts($family, $name, $cw, $CMap, $registry);
	}

	function AddGBFont($family = 'GB', $name = 'STSongStd-Light-Acro')
	{
		//Add GB font with proportional Latin
		$cw = $GLOBALS['GB_widths'];
		$CMap = 'GBKp-EUC-H';
		$registry = ['ordering' => 'GB1', 'supplement' => 2];
		$this->AddCIDFonts($family, $name, $cw, $CMap, $registry);
	}

	function AddGBhwFont($family = 'GB-hw', $name = 'STSongStd-Light-Acro')
	{
		//Add GB font with half-width Latin
		for ($i = 32; $i <= 126; $i++)
			$cw[chr($i)] = 500;
		$CMap = 'GBK-EUC-H';
		$registry = ['ordering' => 'GB1', 'supplement' => 2];
		$this->AddCIDFonts($family, $name, $cw, $CMap, $registry);
	}

	function GetStringWidth($s)
	{
		if ($this->CurrentFont['type'] == 'Type0')
			return $this->GetMBStringWidth($s);
		else
			return parent::GetStringWidth($s);
	}

	function GetMBStringWidth($s)
	{
		//Multi-byte version of GetStringWidth()
		$l = 0;
		$cw =& $this->CurrentFont['cw'];
		$nb = strlen($s);
		$i = 0;
		while ($i < $nb) {
			$c = $s[$i];
			if (ord($c) < 128) {
				$l += $cw[$c];
				$i++;
			} else {
				$l += 1000;
				$i += 2;
			}
		}
		return $l * $this->FontSize / 1000;
	}

	function MultiCell($w, $h, $txt, $border = 0, $align = 'L', $fill = 0)
	{
		if ($this->CurrentFont['type'] == 'Type0')
			$this->MBMultiCell($w, $h, $txt, $border, $align, $fill);
		else
			parent::MultiCell($w, $h, $txt, $border, $align, $fill);
	}

	function MBMultiCell($w, $h, $txt, $border = 0, $align='L',$fill=0)
{
	//Multi-byte version of MultiCell()
	$cw=&$this->CurrentFont['cw'];
	if($w==0)
		$w=$this->w-$this->rMargin-$this->x;
	$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
	$s=str_replace("\r",'',$txt);
	$nb=strlen($s);
	if($nb>0 and $s[$nb-1]=="\n")
		$nb--;
	$b=0;
	if($border)
	{
		if($border==1)
		{
			$border='LTRB';
			$b='LRT';
			$b2='LR';
		}
		else
		{
			$b2='';
			if(is_int(strpos($border,'L')))
				$b2.='L';
			if(is_int(strpos($border,'R')))
				$b2.='R';
			$b=is_int(strpos($border,'T')) ? $b2.'T' : $b2;
		}
	}
	$sep=-1;
	$i=0;
	$j=0;
	$l=0;
	$nl=1;
	while($i<$nb)
	{
		//Get next character
		$c=$s[$i];
		//Check if ASCII or MB
		$ascii=(ord($c)<128);
		if($c=="\n")
		{
			//Explicit line break
			$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
			$i++;
			$sep=-1;
			$j=$i;
			$l=0;
			$nl++;
			if($border and $nl==2)
				$b=$b2;
			continue;
		}
		if(!$ascii)
		{
			$sep=$i;
			$ls=$l;
		}
		elseif($c==' ')
		{
			$sep=$i;
			$ls=$l;
		}
		$l+=$ascii ? $cw[$c] : 1000;
		if($l>$wmax)
		{
			//Automatic line break
			if($sep==-1 or $i==$j)
			{
				if($i==$j)
					$i+=$ascii ? 1 : 2;
				$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
			}
			else
			{
				$this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill);
				$i=($s[$sep]==' ') ? $sep+1 : $sep;
			}
			$sep=-1;
			$j=$i;
			$l=0;
			$nl++;
			if($border and $nl==2)
				$b=$b2;
		}
		else
			$i+=$ascii ? 1 : 2;
	}
	//Last chunk
	if($border and is_int(strpos($border,'B')))
		$b.='B';
	$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
	$this->x=$this->lMargin;
}

function Write($h,$txt,$link='')
{
	if($this->CurrentFont['type']=='Type0')
		$this->MBWrite($h,$txt,$link);
	else
		parent::Write($h,$txt,$link);
}

function MBWrite($h,$txt,$link)
{
	//Multi-byte version of Write()
	$cw=&$this->CurrentFont['cw'];
	$w=$this->w-$this->rMargin-$this->x;
	$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
	$s=str_replace("\r",'',$txt);
	$nb=strlen($s);
	$sep=-1;
	$i=0;
	$j=0;
	$l=0;
	$nl=1;
	while($i<$nb)
	{
		//Get next character
		$c=$s[$i];
		//Check if ASCII or MB
		$ascii=(ord($c)<128);
		if($c=="\n")
		{
			//Explicit line break
			$this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',0,$link);
			$i++;
			$sep=-1;
			$j=$i;
			$l=0;
			if($nl==1)
			{
				$this->x=$this->lMargin;
				$w=$this->w-$this->rMargin-$this->x;
				$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
			}
			$nl++;
			continue;
		}
		if(!$ascii or $c==' ')
			$sep=$i;
		$l+=$ascii ? $cw[$c] : 1000;
		if($l>$wmax)
		{
			//Automatic line break
			if($sep==-1 or $i==$j)
			{
				if($this->x>$this->lMargin)
				{
					//Move to next line
					$this->x=$this->lMargin;
					$this->y+=$h;
					$w=$this->w-$this->rMargin-$this->x;
					$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
					$i++;
					$nl++;
					continue;
				}
				if($i==$j)
					$i+=$ascii ? 1 : 2;
				$this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',0,$link);
			}
			else
			{
				$this->Cell($w,$h,substr($s,$j,$sep-$j),0,2,'',0,$link);
				$i=($s[$sep]==' ') ? $sep+1 : $sep;
			}
			$sep=-1;
			$j=$i;
			$l=0;
			if($nl==1)
			{
				$this->x=$this->lMargin;
				$w=$this->w-$this->rMargin-$this->x;
				$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
			}
			$nl++;
		}
		else
			$i+=$ascii ? 1 : 2;
	}
	//Last chunk
	if($i!=$j)
		$this->Cell($l/1000*$this->FontSize,$h,substr($s,$j,$i-$j),0,0,'',0,$link);
}

function _putfonts()
{
	$nf=$this->n;
	foreach($this->diffs as $diff)
	{
		//Encodings
		$this->_newobj();
		$this->_out('<</Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences ['.$diff.']>>');
		$this->_out('endobj');
	}
	$mqr= false;
	foreach($this->FontFiles as $file=>$info)
	{
		//Font file embedding
		$this->_newobj();
		$this->FontFiles[$file]['n']=$this->n;
		if(defined('FPDF_FONTPATH'))
			$file=FPDF_FONTPATH.$file;
		$size=filesize($file);
		if(!$size)
			$this->Error('Font file not found');
		$this->_out('<</Length '.$size);
		if(substr($file,-2)=='.z')
			$this->_out('/Filter /FlateDecode');
		$this->_out('/Length1 '.$info['length1']);
		if(isset($info['length2']))
			$this->_out('/Length2 '.$info['length2'].' /Length3 0');
		$this->_out('>>');
		$f=fopen($file,'rb');
		$this->_putstream(fread($f,$size));
		fclose($f);
		$this->_out('endobj');
	}
	foreach($this->fonts as $k=>$font)
	{
		//Font objects
		$this->_newobj();
		$this->fonts[$k]['n']=$this->n;
		$this->_out('<</Type /Font');
		if($font['type']=='Type0')
			$this->_putType0($font);
		else
		{
			$name=$font['name'];
			$this->_out('/BaseFont /'.$name);
			if($font['type']=='core')
			{
				//Standard font
				$this->_out('/Subtype /Type1');
				if($name!='Symbol' and $name!='ZapfDingbats')
					$this->_out('/Encoding /WinAnsiEncoding');
			}
			else
			{
				//Additional font
				$this->_out('/Subtype /'.$font['type']);
				$this->_out('/FirstChar 32');
				$this->_out('/LastChar 255');
				$this->_out('/Widths '.($this->n+1).' 0 R');
				$this->_out('/FontDescriptor '.($this->n+2).' 0 R');
				if($font['enc'])
				{
					if(isset($font['diff']))
						$this->_out('/Encoding '.($nf+$font['diff']).' 0 R');
					else
						$this->_out('/Encoding /WinAnsiEncoding');
				}
			}
			$this->_out('>>');
			$this->_out('endobj');
			if($font['type']!='core')
			{
				//Widths
				$this->_newobj();
				$cw=&$font['cw'];
				$s='[';
				for($i=32;$i<=255;$i++)
					$s.=$cw[chr($i)].' ';
				$this->_out($s.']');
				$this->_out('endobj');
				//Descriptor
				$this->_newobj();
				$s='<</Type /FontDescriptor /FontName /'.$name;
				foreach($font['desc'] as $k=>$v)
					$s.=' /'.$k.' '.$v;
				$file=$font['file'];
				if($file)
					$s.=' /FontFile'.($font['type']=='Type1' ? '' : '2').' '.$this->FontFiles[$file]['n'].' 0 R';
				$this->_out($s.'>>');
				$this->_out('endobj');
			}
		}
	}
}

function _putType0($font)
{
	//Type0
	$this->_out('/Subtype /Type0');
	$this->_out('/BaseFont /'.$font['name'].'-'.$font['CMap']);
	$this->_out('/Encoding /'.$font['CMap']);
	$this->_out('/DescendantFonts ['.($this->n+1).' 0 R]');
	$this->_out('>>');
	$this->_out('endobj');
	//CIDFont
	$this->_newobj();
	$this->_out('<</Type /Font');
	$this->_out('/Subtype /CIDFontType0');
	$this->_out('/BaseFont /'.$font['name']);
	$this->_out('/CIDSystemInfo <</Registry '.$this->_textstring('Adobe').' /Ordering '.$this->_textstring($font['registry']['ordering']).' /Supplement '.$font['registry']['supplement'].'>>');
	$this->_out('/FontDescriptor '.($this->n+1).' 0 R');
	if($font['CMap']=='ETen-B5-H')
		$W='13648 13742 500';
	elseif($font['CMap']=='GBK-EUC-H')
		$W='814 907 500 7716 [500]';
	else
		$W='1 ['.implode(' ',$font['cw']).']';
	$this->_out('/W ['.$W.']>>');
	$this->_out('endobj');
	//Font descriptor
	$this->_newobj();
	$this->_out('<</Type /FontDescriptor');
	$this->_out('/FontName /'.$font['name']);
	$this->_out('/Flags 6');
	$this->_out('/FontBBox [0 -200 1000 900]');
	$this->_out('/ItalicAngle 0');
	$this->_out('/Ascent 800');
	$this->_out('/Descent -200');
	$this->_out('/CapHeight 800');
	$this->_out('/StemV 50');
	$this->_out('>>');
	$this->_out('endobj');
}
}
?>
