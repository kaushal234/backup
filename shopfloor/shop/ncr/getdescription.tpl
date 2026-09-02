<style>.nob {
        border: none;
        width: 100%;
        background-color: #FFF !important
    }</style>
<script type="text/javascript" src="/shared/javascript/jquery/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="/shared/javascript/jquery/jquery-ui-1.10.1.custom.min.js"></script>
<script type="text/javascript" language="javascript">
    function msgAjax() {
        var itemno = $('#itemno').val();
        var bu = $('#buid').val();
        if (itemno === '') {
            $('#itemdesc').val('');
            return false;
        }
        if (bu === '') {
            alert('no selected bussiness unit');
            return false;
        }
        var url = '/shop/autoselect.php?m[0]=ncr&m[1]=forms&m[2]=parts.getinfo';
        url = url + "&itemno=" + itemno;
        url = url + "&buid=" + bu;
        $.post(
            url,
                {itemno:itemno,buid:bu},
            function (json) {
                if (json.status === "1") {
                    $('#itemdesc').val(json.t_dsca);
                    $('#button').removeAttr("disabled");
                }
                else {
                    alert("Wrong item number!");
                    $('#itemdesc').val('');
                    $('#button').attr("disabled", "false");
                }
            },
            "json"
        );
    }
</script>
