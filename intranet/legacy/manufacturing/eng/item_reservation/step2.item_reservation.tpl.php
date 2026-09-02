<?php
ob_start();
?>

<table border=0>
    <tr>
        <td>
            STEP 1: Search if PN already exists
        </td>
        <td>
            &nbsp;&nbsp;&nbsp;---------->&nbsp;&nbsp;&nbsp;
        </td>
        <td>
            <b>STEP 2: Reserve new PN</b>
        </td>
        <td>
            &nbsp;&nbsp;&nbsp;---------->&nbsp;&nbsp;&nbsp;
        </td>
        <td>
            STEP 3: List reserved PN
        </td>
    </tr>
</table>
<br>

<form name="frmreserveItem" action="./dev.php?m[0]=item_reservation&m[1]=listing&m[2]=doReservation&submitted=Y" method="post">
    <table border=0>
        <tr>
            <td colspan=2 bgcolor=grey><b>Fill new item description:</b></td>
        </tr>
        <tr>
            <td><font color=red>*</font><b>Description</b></td>
            <td>
                <input type=text name='DSCA' maxlength=30 size=40>
                <span id="errorMessage" style="color: red;"></span>
            </td>
        </tr>
        <tr>
            <td></td>
            <td><input type=submit Value=submit>

            </td>
        </tr>
        <tr>
            <td></td>
            <td><font color=red>*</font>&nbsp;denotes required field&nbsp; </td>
        </tr>
    </table>
</form>

{literal}
    <script type="application/javascript">
      document.addEventListener("DOMContentLoaded", function () {
        var input = document.querySelector('input[name="DSCA"]');
        var btn = document.querySelector('input[type="submit"]');
        var error = document.querySelector('#errorMessage');

        var validateASCIIOnly = function (input) {
          error.innerHTML = '';
          btn.disabled=false;
          // Check ASCII character only except '
          if (!/^[\x20-\x26\x28-\x7E]*$/.test(input.value)) {
            error.innerHTML = "Forbidden character";
            btn.disabled=true;
          }
        }

        validateASCIIOnly(input);
        input.addEventListener('input', function (e) {
          validateASCIIOnly(e.target);
        })
      });
    </script>
{/literal}

<?php
return ob_get_clean();
