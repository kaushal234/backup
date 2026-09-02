<?php

include_once('common.inc.php');

session_start();
if (!isset($_SESSION['sess'])) {
    $_SESSION['sess'] = null;
}
$sess =& $_SESSION['sess'];

$user = new tldUser($GLOBALS['PHP_AUTH_USER']);session_start();
if (!isset($_SESSION['sess'])) {
    $_SESSION['sess'] = null;
}
$sess =& $_SESSION['sess'];

$user = new tldUser($GLOBALS['PHP_AUTH_USER']);

// List of ERP
$erps = tldLocation::getFactoryList('smartyOptions') + tldLocation::getSalesOrgList('smartyOptions') + tldLocation::getSPHList('smartyOptions');
$erps = array_intersect(tldLocation::getERPList('smartyOptions'), $erps);
$erp = $erp ?? 0;

if (isset($sess['parts']['default_erp'])) {
    $DEFAULT_ERP = $sess['parts']['default_erp'];
} else {
    $bu = new tldLocation($user->getBUID());
    $buErp = (int) $bu->getERP();
    // For LAC redirect to AME, default using user's BU ID
    $DEFAULT_ERP = $buErp === 310 ? 300 : $buErp;

    // Check ERP info from user
    if (empty($DEFAULT_ERP) || !array_key_exists($DEFAULT_ERP, $erps)) {
        $DEFAULT_ERP = 300; // Default to AME
    }
    $sess['parts']['default_erp'] = $DEFAULT_ERP;
}

$id = tldDatabase::escape($_GET['id']);

// Get Photo Vault Controller
        $photoController = new tldPhotoController();
        //var_dump($photoController); die;
        // Get single Photo Vault Controller
        $filePath = $photoController->fileExistsInVault($DEFAULT_ERP, "$id.JPG");

        $imgParams = [];
        if (!empty($filePath) && $filePath !== -1) {
            $imgParams[] = [
                'link' => [
                    'm' => [0 => 'getfile', 1 => 'photo'],
                    'erp' => $DEFAULT_ERP,
                    'item' => $id,
                ],
                'img' => [
                    'm' => [0 => 'getfile', 1 => 'photo'],
                    'erp' => $DEFAULT_ERP,
                    'item' => $id,
                    'new_width' => 256,
                ],
            ];
        }

        if ($_GET['isGranted']) {
            // If the PN folder exists, add multiple PN pictures
            $folderpath = $photoController->fileExistsInVault($DEFAULT_ERP, $id);
            if (!empty($folderpath) && $folderpath !== -1) {
                // Get list of the other files in vault
                $files = $photoController->getDirFilelist($DEFAULT_ERP, $folderpath);
                // retrieve all picture files to display it
                foreach ($files as $key => $file) {
                    if (strtolower($file) === 'thumbs.db') {
                        continue;
                    }
                    $imgParams[] = [
                        'link' => [
                            'm' => [0 => 'getfile', 1 => 'photo'],
                            'erp' => $DEFAULT_ERP,
                            'item' => $id,
                            'key' => $key,
                        ],
                        'img' => [
                            'm' => [0 => 'getfile', 1 => 'photo'],
                            'erp' => $DEFAULT_ERP,
                            'item' => $id,
                            'key' => $key,
                            'new_width' => 128,
                        ],
                    ];
                }
            }

        }

        $imgHtml = '';
        $linkHtml = '';

        foreach ($imgParams as $key => $img) {
            if ( $_GET['id'] === $img['img']['item']) {
                $imgHtml .= '<a href="/en/private/manufacturing/eng/dev.php?' . http_build_query($img['link']) . '" class="jqzoom"><img src="/en/private/manufacturing/eng/dev.php?' . http_build_query($img['img']) . '" alt="" align="right" /></a>' . "\n";
                $linkHtml .= '<br/><a href="/en/private/manufacturing/eng/dev.php?' . http_build_query($img['link']) . '" target="_blank">Open Image #' . ($key + 1) . ' In New Window</a>' . "\n";
            }
        }

        echo <<<EOF
<table width="100%">
  <tr>

  	<td>$imgHtml</td>
  </tr>
  <tr>
  	<td colspan="2">&nbsp;</td>
  	<td>$linkHtml</td>
  </tr>
</table>
EOF;