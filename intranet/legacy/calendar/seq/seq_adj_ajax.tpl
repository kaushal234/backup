<style>.nob{    border:none;width:100%;background-color:#FFF !important}</style>
		    <script type="text/javascript" language="javascript">
	function msgAjax(a)
	{
		var itemno = $('#itemno'+a).val();
		    var bu=$('#buid').val();
		    if(itemno==''){
		      $('#itemdesc'+a).val('');
					 $('#itemstdcost'+a).val('');
		    return false;
		    }
		    if(bu==''){
		    alert('no selected bussiness unit');
		    return false;
		    }
		var url = '/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=acct.positiveadj.getinfo';
		url = url + "&itemno=" + itemno;
		$.post(
			url,
			{itemno:itemno,bu_id:bu},
			function(json){
				if(json.status == 1){
					$('#itemdesc'+a).val(json.t_dsca);
					$('#itemstdcost'+a).val(json.t_copr);
				}
				else{
		    $('#itemdesc'+a).val('');
					 $('#itemstdcost'+a).val('');
					alert("No data");
				}
			},
			"json"
		);
	}
</script>
