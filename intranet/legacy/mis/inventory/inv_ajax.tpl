<style>.nob {
        border: none;
        width: 100%;
        background-color: #FFF !important
    }</style>
<script type="text/javascript" language="javascript">
    function handleSerialNumberGeneratorForLaptop(){
        var $manufacturerSn = $('input[name="manufacturer_sn"]');

        $manufacturerSn.on('input', function () {

            var snValue = $($manufacturerSn).val().trim();

            var year = new Date().getFullYear().toString().slice(-2);

            var serial = 'LAP' + year + '-';

            if (snValue) {
                serial += snValue.toUpperCase();
            }

            $('#tldsn').val(serial);
        });
    }
    function msgAjax() {
        var typeid = $('#typeid').val();
        var func = $('#func').val();
        var buid = $('#buid').val();
        if (typeid == '') {
            return false;
        }
        if(typeid == '1'){handleSerialNumberGeneratorForLaptop()}

        if (typeid == '19'){
          document.getElementById("func").style.display="";
        }
        var url = '/en/private/mis/mis.php?m[0]=inventory&m[1]=invadd.getinfo';
        url = url + "&typeid=" + typeid;
        url = url + "&func=" + func;
        url = url + "&buid=" + buid;

        $.post(
                url,
                {typeid:typeid},
                function (json) {
                    if (json.status == 1) {
                        $('#tldsn').val(json.tldsn);
                    }
                    else {
                        $('#tldsn').val('');
                        alert("Please implement item type prefix name!");
                    }
                },
                "json"
        );
    }
    function checkdate(){
        var warranty_date = $('#warranty_date').val();
        if (warranty_date !=''){
            document.getElementById("warranty_year1").style.visibility="hidden";
            document.getElementById("warranty_year2").style.visibility="hidden";
            document.getElementById("warranty_year3").style.visibility="hidden";
            document.getElementById("warranty_year4").style.visibility="hidden";
        }
    }
    function checkyear(){
      var warranty_year1 = $('#warranty_year1').val();
      var warranty_year2 = $('#warranty_year2').val();
      var warranty_year3 = $('#warranty_year3').val();
      var warranty_year4 = $('#warranty_year4').val();
      if (warranty_year1 !='' || warranty_year2 !='' || warranty_year3 !='' || warranty_year4 !=''){
        document.getElementById("warranty_date").style.display="none";
      }
    }
    function checkqty(){
      var quantity = $('#quantity').val();
      if(quantity>1){
        alert("Please confirm the Quantity you set!");
      }
    }
    function checkstate(){
      var state = $('#state').val();
      if (state == 'UNLIMITED'){
        document.getElementById("warranty_date").style.display="none";
        document.getElementById("warranty_year1").style.display="none";
        document.getElementById("warranty_year2").style.display="none";
        document.getElementById("warranty_year3").style.display="none";
        document.getElementById("warranty_year4").style.display="none";
      }else{
        document.getElementById("warranty_date").style.display="";
        document.getElementById("warranty_year1").style.display="";
        document.getElementById("warranty_year2").style.display="";
        document.getElementById("warranty_year3").style.display="";
        document.getElementById("warranty_year4").style.display="";
      }
    }
</script>
