<?php
require_once('HTML/QuickForm/advmultiselect.php');

// FORM CONFIG

// --- Listing

$groupForm = [];
$kpiFactoryList = tldUtils::optionsByKeyValue(
    tldLocation::byConstraints(['disable' => 0, 'factory' => 'Y', 'hidden' => 0]),
    'id',
    'location'
);
$factoryList = ['' => 'Select Factory', 'ALL' => 'ALL'] + $kpiFactoryList;
$graphList = ['Delivery', 'Green Tag - by product', 'Green Tag - by type', 'Manufacturing - by product', 'Manufacturing - by type', 'Production', 'Product Support', 'Quality', 'Engineering']; // @todo -> migrate 'Inventory','Production','Vendors','Engineering'
$graphList = ['' => 'Select Graph'] + array_combine($graphList, $graphList);

$kpiToolForm = new HTML_QuickForm('kpiToolfrm');
$kpiToolForm->addElement('hidden', 'm[0]', 'kpi2');
$kpiToolForm->addElement('header', 'title', 'Graph configuration');
$groupForm[] = $kpiToolForm->createElement('select', 'bu_id', 'Factory', $factoryList, ['id' => 'bu_id']);
$groupForm[] = $kpiToolForm->createElement(
    'text',
    'dt_from',
    'Start',
    ['class' => 'datepicker', 'data-dateformat' => 'yy-mm', 'size' => 7, 'id' => 'start_date']
);
$groupForm[] = $kpiToolForm->createElement(
    'text',
    'dt_to',
    'End',
    ['id' => 'state', 'class' => 'datepicker', 'data-dateformat' => 'yy-mm', 'size' => 7]
);
$groupForm[] = $kpiToolForm->createElement('select', 'graph', 'Graph', $graphList, ['id' => 'graph_type', 'onchange' => 'checkstate()']);

//$groupForm[] = $kpiToolForm->createElement('select', 'cur', 'Select Currency', ["" => "", "USD" => "USD", "EUR" => "EUR"],['id'=>'currency']);
$queryModels = "select service.model, locations.id 
from service 
  join locations on locations.location=service.man_location 
  join models on service.model=models.model 
where service.model != '' 
      and service.model not like ' %' 
      and service.model not like '_' 
      and man_location != '' 
  and hide != 1 
GROUP BY man_location, service.model";
$models = [];
foreach (tldUtils::getSqlToAssocArray($queryModels) as $mod) {
    $models[$mod['id']][$mod['model']] = $mod['model'];
    $models['ALL'][$mod['model']] = $mod['model'];
}

ksort($models['ALL']);

$queryTypes = "select service.type, locations.id
from service 
  join locations on locations.location=service.man_location
where  man_location != ''
  and service.type != ''
  and service.model != ''
  and service.type != ' '
  and service.type != ' '
  and service.sales_org != ''
  and service.state = 'ACTIVE'
GROUP BY man_location, service.type";
$types = [];
foreach (tldUtils::getSqlToAssocArray($queryTypes) as $type) {
    $types[$type['id']][$type['type']] = $type['type'];
    $types['ALL'][$mod['type']] = $mod['type'];
}
ksort($models['ALL']);
$js = <<<JS

function addDMSLink() {
  if (!$('.js-link-container').length) {
    $('form').prepend($.parseHTML('<div class="js-link-container"><a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=3133">Help page</a></div>'));
  }
} 
   
function removeDMSLInk() {
  $('.js-link-container').remove();
}

$(document).ready(function() {
    function changeSelectGraph() {
        $("select[id^='models_']").closest( "tbody" ).closest("tr").hide();
        $("select[id^='types_']").closest( "tbody" ).closest("tr").hide();
        
        switch($("#graph_type").val()) {
          case 'Manufacturing - by product':
          case 'Quality':
            if ($('#bu_id').val() !== 'ALL') {
              $("select[id='models_" + $("#bu_id").val() + "']").closest( "tbody" ).closest("tr").show();
            }
            break;
          case 'Manufacturing - by type':
            $("select[id='types_" + $("#bu_id").val() + "']").closest( "tbody" ).closest("tr").show();
            break;
          case 'Green Tag - by type':
            addDMSLink();
            if ($('input[name="unit_type"]:checked').val() === 'T') {
              $("select[id='types_" + $("#bu_id").val() + "']").closest( "tbody" ).closest("tr").show();
            }
            break;
          case 'Green Tag - by product':
            addDMSLink();
            if ($('input[name="unit_type"]:checked').val() === 'T') {
              $("select[id='models_" + $("#bu_id").val() + "']").closest( "tbody" ).closest("tr").show();
            }
            break;
        }
    }
    $("#graph_type").change(changeSelectGraph);
    changeSelectGraph();
   
    function changeSelectBu() {
        switch ($("#graph_type").val()) {
          case 'Green Tag - by product':
            $("select[id^='models_']").closest( "tbody" ).closest("tr").hide();

            addDMSLink();
            if ($('input[name="unit_type"]:checked').val() === 'T') {
              $("select[id='models_" + $(this).val() + "']").closest( "tbody" ).closest("tr").show();
            }
            
            var nowc=$('#bu_id').val();
            var nr=$('#models_'+nowc+'-f').html();
            if(nowc!='ALL'&& nr!=''){
              $('#models_'+nowc+'-t').append($('#models_'+nowc+'-f').html());
              $('#models_'+nowc+'-f').html($('#models_'+nowc+'-t').html());
              $('#models_'+nowc+'-t').html('');
              $('#models_'+nowc+'-f').removeAttr('disabled');
            }
            break;
          case 'Manufacturing - by product':
          case 'Quality':
          case 'Engineering':
            $("select[id^='models_']").closest( "tbody" ).closest("tr").hide();
            
            removeDMSLInk();
             var nowc=$('#bu_id').val()
            if (nowc !== 'ALL' || 'Engineering' === $("#graph_type").val()) {
                $("select[id='models_" + nowc + "']").closest( "tbody" ).closest("tr").show();
                $('#models_'+nowc+'-t').append($('#models_'+nowc+'-f').html());
                $('#models_'+nowc+'-f').html($('#models_'+nowc+'-t').html());
                $('#models_'+nowc+'-t').html('');
                $('#models_'+nowc+'-f').removeAttr('disabled');
            }
            break;
          case 'Green Tag - by type':
          case 'Manufacturing - by type':
            $("select[id^='types_']").closest( "tbody" ).closest("tr").hide();
            
            removeDMSLInk();
            $("select[id='types_" + $(this).val() + "']").closest( "tbody" ).closest("tr").show();
            $('#types_'+nowc+'-t').append($('#types_'+nowc+'-f').html());
            $('#types_'+nowc+'-f').html($('#types_'+nowc+'-t').html());
            $('#types_'+nowc+'-t').html('');
            $('#types_'+nowc+'-f').removeAttr('disabled');
            break;
        }
    }
    
    if ($("#graph_type").val() === 'Green Tag'){
          addDMSLink();
           
            $('#state').hide();
            $('input[name="unit_type"]').each(function (ind, el) {
              $(el).parent('td').show()
            })
    } else {
      removeDMSLInk();
       $('input[name="unit_type"]').each(function (ind, el) {
          $(el).parent('td').hide()
        })
    }
    $("#bu_id").change(changeSelectBu);
});
 function checkstate(keepChosen){
  
        if ($("#graph_type").val()== 'Green Tag'){
            addDMSLink();
            $('#state').hide();
            $('input[name="unit_type"]').each(function (ind, el) {
              $(el).parent('td').show()
            })
            // $('input[name="unit_type"]').change(checkstate);
            var begin = new Date();
            var year = begin.getFullYear();
            var year2 = year;
            var month = begin.getMonth();
            var month2 = parseInt(month)+1;
            if(month2 === 12) {
              year = begin.getFullYear()+1;
              month2 = '01';
            }

            var month2 = String(month2);
            if (month2.length < 2) {
              month2 = "0" + month2;
            }
            document.getElementById('start_date').value = year2 + '-' + month2;

            if($('input[name="unit_type"]:checked').val() !== 'T') {
              $("select[id^='models_']").closest( "tbody" ).closest("tr").hide();
              return;
            } 
           
            $("select[id='models_" + $("#bu_id").val() + "']").closest( "tbody" ).closest("tr").show();
            // avoid to reset the model multi-select when updating radio buttons
              if(true === keepChosen) {
                return;
              }
            var nowc=$('#bu_id').val();
            var nr=$('#models_'+nowc+'-f').html();
            $('#kpimodeltitle'+nowc).html('<label id="kpit">chosen (The more machines there are, the longer it will take)</label>');
            if(nowc!='ALL'&& nr!=''){
              $('#models_'+nowc+'-t').append($('#models_'+nowc+'-f').html());
              $('#models_'+nowc+'-f').html($('#models_'+nowc+'-t').html());
              $('#models_'+nowc+'-t').html('');
              $('#models_'+nowc+'-f').removeAttr('disabled');console.log($('#models_'+nowc+'-f'));
            }
            
        }else if ($("#graph_type").val()== 'Manufacturing - by type'){
            removeDMSLInk();
            $('#state').show();
            $('input[name="unit_type"]').each(function (ind, el) {
              $(el).parent('td').hide()
            })
            var begin = new Date();
            var year = parseInt(begin.getFullYear()) - 1;
            var month = parseInt(begin.getMonth()) + 1; // Months in JS are indexed 0-11
            var month2 = parseInt(month)+1;
            if(month === 12) {
              year = begin.getFullYear();
              month2 = '01';
            }

            var month2 = String(month2);
            if (month2.length < 2) {
              month2 = "0" + month2;
            }
           
            document.getElementById('start_date').value = year + '-' + month2;
            var nowc=$('#bu_id').val();
            $('#kpitypetitle'+nowc).html('<label id="kpit">chosen (2 max - The more types there are, the longer it will take)</label>');
            $('#types_'+nowc+'-t').append($('#types_'+nowc+'-f').html());
            $('#types_'+nowc+'-f').html($('#types_'+nowc+'-t').html());
            $('#types_'+nowc+'-t').html('');
            $('#types_'+nowc+'-f').removeAttr('disabled');
        }
        else{
            removeDMSLInk();
            $('#state').show();
            $('input[name="unit_type"]').each(function (ind, el) {
              $(el).parent('td').hide()
            })
            var begin = new Date();
            var year = parseInt(begin.getFullYear()) - 1;
            var month = parseInt(begin.getMonth()) + 1; // Months in JS are indexed 0-11
            var month2 = parseInt(month)+1;
            if(month === 12) {
              year = begin.getFullYear();
              month2 = '01';
            }

            var month2 = String(month2);
            if (month2.length < 2) {
              month2 = "0" + month2;
            }
           
            document.getElementById('start_date').value = year + '-' + month2;
            var nowc=$('#bu_id').val();
            $('#kpimodeltitle'+nowc).html('<label id="kpit">chosen (10 max - The more machines there are, the longer it will take)</label>');
            if ('ALL' !== nowc || 'Engineering' === $("#graph_type").val()) {
               $('#models_'+nowc+'-t').append($('#models_'+nowc+'-f').html());
                $('#models_'+nowc+'-f').html($('#models_'+nowc+'-t').html());
                $('#models_'+nowc+'-t').html('');
                $('#models_'+nowc+'-f').removeAttr('disabled');
            }
        }
    }
JS;

$smarty->assign('html_head', '<script type="text/javascript">'.$js.'</script>');
$groupForm[] = $kpiToolForm->createElement('submit', 'btnSubmit', 'Submit');
$kpiToolForm->addGroup($groupForm);
$defaults = [
    'dt_from' => (new DateTime())->modify('-11 months')->format('Y-m'),
    'dt_to' => date('Y-m'),
    'bu_id' => $user->getBUID(),

];
foreach ($kpiFactoryList + ['ALL' => 'ALL'] as $key => $val) {
    $ams =& $kpiToolForm->addElement('advmultiselect', "models_$key", null,
        $models[$key],
        ['size' => 15, 'class' => 'pool', 'style' => 'width:382px;', 'id' => "models_$key"]
    );
    $ams->setLabel(['Models', 'available', '<label id="kpimodeltitle'.$key.'">chosen (Usually 10 max - The more machines there are, the longer it will take)</label>', 'id' => 'kpimodeltitle']);

    $ams =& $kpiToolForm->addElement('advmultiselect', "types_$key", null,
        $types[$key],
        ['size' => 15, 'class' => 'pool', 'style' => 'width:382px;', 'id' => "types_$key"]
    );
    $ams->setLabel(['Types', 'available', '<label id="kpitypetitle'.$key.'">chosen (Usually 5 max - The more types there are, the longer it will take)</label>', 'id' => 'kpitypetitle']);

    $defaults["models_$key"] = $models[$key];
    $defaults["types_$key"] = $types[$key];
    $ams->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
    $ams->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);

}
$kpiToolForm->addElement('radio', 'unit_type', '', 'All Units [T + P]', 'ALL', ['onchange' => 'checkstate(true)']);
$kpiToolForm->addElement('radio', 'unit_type', '', 'T Units only', 'T', ['onchange' => 'checkstate(true)']);
$kpiToolForm->addElement('radio', 'unit_type', '', 'P Units only', 'P', ['onchange' => 'checkstate(true)']);
$defaults['unit_type'] = 'T';

// --- Form config
if (!empty($_SESSION['kpi'])) {
    $defaults = array_merge($defaults, $_SESSION['kpi']['form']);
}

$kpiToolForm->setDefaults($defaults);
$body = $kpiToolForm->toHtml();

// FORM PROCESSING
if ($kpiToolForm->validate() && $kpiToolForm->isSubmitted()) {
    $vars = $kpiToolForm->exportValues();
    // Check Data
    // -- factory
    if (!isset($vars['bu_id']) || empty($vars['bu_id']) || !in_array($vars['bu_id'], array_keys($factoryList))) {
        $DEFAULT_ERROR[] = 'ERROR: Factory field is empty or invalid';
    }
    $bu = null;
    if (is_numeric($vars['bu_id'])) {
        $bu = new tldLocation($vars['bu_id']);
        if ($bu->isEmpty()) {
            $DEFAULT_ERROR[] = "ERROR: Factory#{$vars['bu_id']} not found";
        }
    }
    // -- Graph
    if (!isset($vars['graph']) || empty($vars['graph']) || !in_array($vars['graph'], $graphList)) {
        $DEFAULT_ERROR[] = 'ERROR: Graph field is empty or invalid';
    }
    // -- dates
    try {
        $start = new \DateTime($vars['dt_from']);
    } catch (Exception $e) {
        $DEFAULT_ERROR[] = "ERROR: Start period invalid. Reason: $e";
    }
    try {
        $end = new \DateTime($vars['dt_to']);
        $end->modify(
            sprintf('+%d days', $end->format('t') - $end->format('j'))
        );
    } catch (Exception $e) {
        $DEFAULT_ERROR[] = "ERROR: End period invalid. Reason: $e";
    }
    // Check start & end date
    if ($end < $start && $vars['graph'] !== 'Green Tag') {

        $DEFAULT_ERROR[] = 'ERROR: Start period can not be greater than End period';
    }
    // Check period <= 1 year
    $interval = $start->diff($end);
    $nbDays = $interval->format('%a');
    if ($nbDays > 365 && $vars['graph'] !== 'Green Tag') {
        $DEFAULT_ERROR[] = "ERROR: KPI period should not be bigger than a year (Period selected is {$interval->format('%R%a')} days)";
    }

    // if there is any error stop the script

    if (count($DEFAULT_ERROR ?? [])) {
        return;
    }

    // Save in session
    $_SESSION['kpi'] = [
        'form' => $vars,
        'bu' => $bu,
        'dt_from' => $start,
        'dt_to' => $end,
        'graph' => $vars['graph'],
    ];
    $display_graph = true;

}

