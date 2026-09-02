{*
!!!!!!!!!!!!!!!!!!!!!!!!!!
	EMAIL TEMPLATE FORM
	USED BY DB ADMIN 2 & 3
	DO NOT DELETE
!!!!!!!!!!!!!!!!!!!!!!!!!!
*}

{literal}
<SCRIPT LANGUAGE="JavaScript">
// Compare two options within a list by VALUES
function compareOptionValues(a, b) 
{ 
  // Radix 10: for numeric values
  // Radix 36: for alphanumeric values
  var sA = parseInt( a.value, 36 );  
  var sB = parseInt( b.value, 36 );  
  return sA - sB;
}
// Compare two options within a list by TEXT
function compareOptionText(a, b) 
{ 
  // Radix 10: for numeric values
  // Radix 36: for alphanumeric values
  var sA = parseInt( a.text, 36 );  
  var sB = parseInt( b.text, 36 );  
  return sA - sB;
}
// Dual list move function
function moveDualList( srcList, destList, moveAll ) 
{
  // Do nothing if nothing is selected
  if (  ( srcList.selectedIndex == -1 ) && ( moveAll == false )   )
  {
    return;
  }
  newDestList = new Array( destList.options.length );
  var len = 0;
  for( len = 0; len < destList.options.length; len++ ) 
  {
    if ( destList.options[ len ] != null )
    {
      newDestList[ len ] = new Option( destList.options[ len ].text, destList.options[ len ].value, destList.options[ len ].defaultSelected, destList.options[ len ].selected );
    }
  }
  for( var i = 0; i < srcList.options.length; i++ ) 
  { 
    if ( srcList.options[i] != null && ( srcList.options[i].selected == true || moveAll ) )
    {
       // Statements to perform if option is selected
       // Incorporate into new list
       newDestList[ len ] = new Option( srcList.options[i].text, srcList.options[i].value, srcList.options[i].defaultSelected, srcList.options[i].selected );
       len++;
    }
  }
  // Sort out the new destination list
  //newDestList.sort( compareOptionValues );   // BY VALUES
  //newDestList.sort( compareOptionText );   // BY TEXT
  // Populate the destination with the items from the new array
  for ( var j = 0; j < newDestList.length; j++ ) 
  {
    if ( newDestList[ j ] != null )
    {
      destList.options[ j ] = newDestList[ j ];
    }
  }
  // Erase source list selected elements
  for( var i = srcList.options.length - 1; i >= 0; i-- ) 
  { 
    if ( srcList.options[i] != null && ( srcList.options[i].selected == true || moveAll ) )
    {
       // Erase Source
       //srcList.options[i].value = "";
       //srcList.options[i].text  = "";
       srcList.options[i]       = null;
    }
  }
} // End of moveDualList()
//  End -->
</script>
{/literal}

<div align="center">

<h2>Email Form</h2>

<p><a href="{$PHP_SELF}?>?mode=record_view&form_type={$form_type}&id={$id}">Cancel</a></p>

<form ACTION="{$PHP_SELF}" METHOD="POST" name="myForm">
    <input type="hidden" name="mode" value="email">
    <input type="hidden" name="id" value="{$id}">
    <input type="hidden" name="form_type" value="{$form_type}">
    <textarea name="message" wrap="VIRTUAL" cols="65" rows="10" align="left">Type your message here:</textarea>
    <br>N.B. You may select up to 10 people. A copy will be sent automatically to yourself.<br>
<table border="0">
<tr><td class="table_title">Address book</td><td>&nbsp;</td><td class="table_title">Recipients</td></tr>
<tr>
  <td>
    <select multiple size="10" style="width:220" name="addressbook">
	{html_options options=$recipients}
    </select>
  </td>
  <td>
    <input type="button" style="width:90" onclick="moveDualList(this.form.addressbook, this.form['emails[]'], false )" name="Add ->>"  value="Add ->>"><br>
    <input type="button" style="width:90" onclick="moveDualList( this.form['emails[]'], this.form.addressbook,  false )" name="<<- Remove"  value="<<- Remove">
    <br><br>
    <input type="submit" style="width:90" name="Submit" value="Email It">
  </td>
  <td>
    <select multiple size="10" style="width:220" name="emails[]">
    </select>
  </td>
</tr>
</table>
</form>
</div>