<?php

/**
 * FOOTER email used by extranetUser::constructEmailFooter()
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
        <td bgcolor="#669999">
          <table width="670" border="0" cellspacing="0" cellpadding="20" align="center"  style="page-break-inside: avoid">
            <?php  if(!empty($data['seqid']) && $data['seqid'] != 0) : ?>
            <tr>
              <td align="center" valign="top">
                <small class="smallwhite">#<?= $data['seqid'] ?></small>
              </td>
            </tr>
			<?php  endif; ?>
            <tr>
              <td align="center" valign="top">
                <small class="smallwhite">Copyright 2012, TLD. All rights reserved | <a href="https://www.tld-gse.com">https://www.tld-gse.com</a><br></small></span>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
    </div>
  </body>
</html>
<?php
return ob_get_clean();
?>
