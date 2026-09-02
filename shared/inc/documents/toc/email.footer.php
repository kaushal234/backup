<?php

/**
 * FOOTER email used by tldTOC::constructEmailFooter()
 */

ob_start();
?>
                </div>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td>
          <table width="670" border="0" cellspacing="0" cellpadding="20" align="center"  style="page-break-inside: avoid">
            <tr>
              <td align="center" valign="top">
                <small class="smallwhite">Copyright 2012, <?=strtoupper($sso['name'])?>. All rights reserved | <a href="<?= $sso['website'] ?>"><?= $sso['website'] ?></a><br></small></span>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>
<?php
return ob_get_clean();
?>
