<?php
$_output_before = ob_get_contents();
ob_start();
?>
    <script type="text/javascript">
        debut = (new Date()).getTime();
    </script>

    <script type="text/javascript" src="/shared/javascript/jquery/jquery-1.9.1.min.js"></script>
    <script type="text/javascript" src="/shared/javascript/jquery/jquery-ui-1.10.1.custom.min.js"></script>
    <link rel="stylesheet" href="/shared/javascript/jquery/css/smoothness/jquery-ui-1.10.1.custom.min.css">
    <link rel="stylesheet" type="text/css" href="/shared/javascript/sweetalert/sweetalert2.min.css">

<?php
$body = include("$PATH/header.pi.tpl.php");
$keyLast=-1;
?>


    <script type="text/javascript">
        $( ".TblInspect" ).css( "background-color" , "#EEEEEE" );
        $( ".TblInspect" ).css( "border" , "2px solid #000000" );
    </script>


    <style>
        img {
            border: none;
        }
        #large {
            display: none;
            position: absolute;
            background: #FFFFFF;
            padding: 5px;
            z-index: 10;
            min-height: 200px;
            min-width: 200px;
            color: #336699;
        }
        #background{
            display: none;
            position: fixed;
            width: 100%;
            top: 0;
            height:100%;
            left: 0;
            background: #000000;
            z-index: 1;
        }
        .swal1-input{
            -webkit-appearance: none;
            background-color: #fff;
            font-size: 14px;
            display: block;
            box-sizing: border-box;
            width: 100%;
            border: 1px solid rgba(0,0,0,.14);
            padding: 10px 13px;
            border-radius: 2px;
            transition: border-color .2s;
        }
        .swal-footer{
            text-align: center;
            background-color: rgb(245, 248, 250);
            margin-top: 32px;
            border-top: 1px solid #E9EEF1;
            overflow: hidden;
        }

        .swal-button{
            padding : 20px 45px;
        }
        .swal-button--no{
            background-color: #cc0033;
        }
        .swal-button--yes{
            background-color: #23dd50;
        }
    </style>

    <table id="mainTable"
        border=0
        width=100%
        data-derogationMsg = "<?= _('Answer out of tolerance + derogation'); ?>"
        data-outOfToleranceMsg = "<?= _('Answer out of tolerance'); ?>"
        data-operationNumber = "<?= $_SESSION['pi_opno']; ?>"
        data-userName ="<?= $_SESSION['pi_user']; ?>"
        data-workOrderNumber = "<?= $_SESSION['pi_pdno']; ?>"
        data-erId = "<?= $_SESSION['pi_snid']; ?>"
        data-brandLabel = "<?= _("Brand"); ?>"
        data-componentLabel = "<?= _("Component"); ?> : "
        data-modelLabel = "<?= _("Model"); ?>"
        data-serialLabel ="<?= _("Serial"); ?>"
    >
        <thead>
        <tr style="background:#2971a8; color:white;">
            <td width=15% style="text-align: center; vertical-align: middle; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= $translator->trans('shopfloor_columns.help', [], 'pio') ?></b></td>

			<td width=37% style="vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
			  <table width=100%>
			  	<tr style="background:#2971a8; color:white;">
			  		<td width=30% style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= $translator->trans('shopfloor_columns.description', [], 'pio') ?></b></td>
			  		<td width=70% align=right style="font-size: <?= $_SESSION['pi_font_size'] ?>;">
			  			<select name='QuestionsGroup'>
			  				<?= $_SESSION['QuestionsGroup'] ?>
			  			</select>
			  		</td>
			  	</tr>
			  </table>
			</td>


			<td width=33% style="text-align: center; vertical-align: middle; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
			  <table width=100%>
			  	<tr style="background:#2971a8; color:white;">
			  		<td width=80% align=center style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= $translator->trans('shopfloor_columns.value', [], 'pio') ?></b></td>
			  		<td width=20% align=center style="font-size: <?= $_SESSION['pi_font_size'] ?>;">
			  			<img id='QuestionsAnswered' src='//www.tld-gse.com/shared/bluesphere/16x16/actions/viewmag.png'>
			  		</td>
			  	</tr>
			  </table>
			</td>

			<td width=5%  style="text-align: center; vertical-align: middle; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= $translator->trans('shopfloor_columns.unit', [], 'pio') ?></b></td>
            <td width=5%  style="text-align: center; vertical-align: middle; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= $translator->trans('shopfloor_columns.name', [], 'pio') ?></b></td>
			<td width=5%  style="text-align: center; vertical-align: middle; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= $translator->trans('shopfloor_columns.crabs_open_all', [], 'pio') ?></b></td>
			<td width=5%  style="text-align: center; vertical-align: middle; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= $translator->trans('shopfloor_columns.help_dms', [], 'pio') ?></b></td>
			<td width=5%  style="text-align: center; vertical-align: middle; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= $translator->trans('shopfloor_columns.owner', [], 'pio') ?></b></td>
			<td width=5%  style="text-align: center; vertical-align: middle; vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;"><b><?= $translator->trans('shopfloor_columns.alert', [], 'pio') ?></b></td>
		</tr>
	</thead>
	<tbody>
	<?php
      	$lig=0;
    	foreach($questions as $key => $question):
        	// Pictures management
        	$PIfile = new tldFile($question['attachment']);

        	$PIFileName = $PIfile->getFilePath();
        	$PIImg = NULL;
        	$PIMainFile = new basicFile($PIfile->getFilePath());
        	if($PIMainFile->isFileExists()){
        	    $typeMime = $PIMainFile->getMimeTypeFromExtension();
        	    $typeMimeSplited = preg_split("#/#", $typeMime);
        	    if(strtolower($typeMimeSplited[0])=="image" || strtolower($typeMimeSplited[0])=="application"){
        	        $PIImg ="<img src='data:$typeMime;base64,{$PIMainFile->getBase64()}' width='800' />";
        	    }else{
        	        $PIImg = "<p><em>File: $PIFileName</em></p>";
        	    }
        	}else{
        	    $PIImg = "<p><em>File: none</em></p>";
        	}
        	$question['picture']=$PIImg;
        	// Groups
			$group=trim($group ?? '');
        	if ( ($group === '' && ($answered ?? null) !== 'no') ||
				($group !== '' && mb_convert_encoding($group,"UTF-8","HTML-ENTITIES") === mb_convert_encoding(trim($question['subject']),"UTF-8","HTML-ENTITIES")) ||
				($group === '' && ($answered ?? null) === 'no' && $question['answer'] === '')
			) {
	?>
                <?php if ($question['subject'] != ($questions[$keyLast]['subject'] ?? null)) {?>
                   <tr>
                     <td colspan=7 style="font-size: <?= $_SESSION['pi_font_size'] ?>;"><br><b><?= $question['subject'] ?></b></td>
                   </tr>
                    <?php
                } ?>
                    <tr id="<?= $question['idQst'];?>" class="TRLig" lig="<?= $key ?>" style="background:<?= $color ?? null ?>"
                    data-answerBrand = "<?= $question['answerBrand'];?>"
                    data-answerSerial = "<?= $question['answerSerial'];?>"
                    data-answerModel = "<?= $question['answerModel'];?>"
                    >
                        <td width=15% style="font-size: <?= $_SESSION['pi_font_size'] ?>;">
                            <div align="center">
                                <?php if ($question['attachment'] != '0') {?>
                                <div class="popPicture" lig="<?= $key ?>" pict="<?= $question['picture'] ?>">
                                    <a><img src='//www.tld-gse.com/shared/bluesphere/16x16/actions/viewmag.png'  title='<?= $question['attachment'] ?>' width='25'></a>
                                    <p id="large"></p>
                                </div>
                                <div id="large"></div>
                                <div id="background"></div>
                                <?php } ?>
                            </div>
                        </td>
		  			<?php $keyLast=$key; ?>

                        <td width=37% class ="desc" style="font-size: large;"><?= $question['desc'] ?></td>
                        <td width=33%
                    class="TDAnswer popup-button"
                    data-answer="<?= $question['answer_type']; ?>"
                    data-componentValue="<?= $question['component_sn']; ?>"
                    lig="<?= $key ?>"
                    style="vertical-align: middle; font-size: <?= $_SESSION['pi_font_size'] ?>;">
		  				<table width=100% height=100%>
		  				    <tr width=100% height=100%>
                                <div class="displayAnswers show" lig="<?= $key ?>" style="vertical-align: middle; font-size: <?php echo $_SESSION['pi_font_size'];?>;">
                                    <b>
                                        <span class="ToleranceValue" lig="<?= $key ?>">
                                            <?= $question['toleranceMsg'] ?? null ?>
                                        </span></b>
                                    <span class="AnswerDisplay" lig="<?= $key ?>"><?= $question['answer'] ?></span>
                            </div>
                        </tr>
                    </table>

                </td>
                <td width=5% style="font-size: <?= $_SESSION['pi_font_size'] ?>;" align = center><br><?= $question['answer_unit'] ?></td>
                <!-- column answer name -->
                <td
                    class="displayAnswerOperatorName"
                    width=5%
                    style="font-size: <?= $_SESSION['pi_font_size'] ?>; padding-top:5px;"
                    align = center>
                    <?= $question['answer_name'] ?>
                </td>

                <td width=5% style="font-size: <?= $_SESSION['pi_font_size'] ?>;" align=center><?= $question['openCRABS'] ?>&nbsp;/&nbsp;<?= $question['CRABS'] ?></td>
                <td width=5% style="font-size: <?= $_SESSION['pi_font_size'] ?>;">
                    <?php if ($question['help']!='') { ?>
                        <br><a class='openHelp' AttrHelp='<?= $question['help'] ?>'><img src='//www.tld-gse.com/shared/bluesphere/16x16/actions/viewmag.png'></a>
                    <?php } ?>
                </td>
                <td width=5% style="font-size: <?= $_SESSION['pi_font_size'] ?>;" align=center><?= $question['owner'] ?>&nbsp</td>
                <td width=5% style="font-size: <?= $_SESSION['pi_font_size'] ?>;">
                    <?php if ($question['alert'] != '') { ?>
                        <br><a class='openAlert'><img src='//www.tld-gse.com/shared/bluesphere/16x16/actions/stop_hand.png'  title='<?= $question['alert'] ?>' width='25'></a>
                    <?php } else { ?>
                        <br><a class='openAlert' href="/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=inspection&m[3]=alert&line=<?= $key ?>"><img src='//www.tld-gse.com/shared/bluesphere/16x16/actions/configure.png' width='25'></a>
                    <?php }  ?>
                </td>
                </tr>
            <?php }  ?>
        <?php endforeach; ?>
        </tbody>
    </table>
    <script src="/shared/javascript/sweetalert/sweetalert2.min.js"></script>
    <script src="/shop/dist/js/inspection.js"></script>



<?php
return ob_get_clean();
?>
