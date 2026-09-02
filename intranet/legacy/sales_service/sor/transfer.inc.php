<?php

switch ($m[2]) {
    case 'test':
        // Get XML test file
        $xml = file_get_contents('sor/equote_example.xml');
        // Display form
        $form = new HTML_QuickForm('frmTransfer', 'post');
        $form->addElement('hidden', 'm[0]', 'sor');
        $form->addElement('hidden', 'm[1]', 'transfer');
        $form->addElement('header', 'title', 'Test equote transfer to SOR');
        $form->addElement('hidden', 'equote', base64_encode($xml));
        $form->addElement('submit', 'btnSubmit', 'Execute transfer');
        $body .= $form->toHTML();
        break;
    default:
        $equote_data_session = &$sess['sor']['equote'];
        // Get posted xml
        $xml = iconv('windows-1252', 'iso-8859-1', base64_decode($_POST['equote']));
        if (empty($xml)) {
            $DEFAULT_ERROR[] = 'ERROR: no xml data received from equotes';
            break;
        }
        // Convert to array
        $equoteArray = tldUtils::convXMLToArray(
            $xml,
            [
                'complexType' => 'array',
                'forceEnum' => ['line', 'breakdown'],
                'targetEncoding' => 'iso-8859-1',
            ]
        );
        if (is_string($equoteArray)) {
            $DEFAULT_ERROR[] = "ERROR: Could not transfer the equote, returned error was $equoteArray";
            break;
        }
        // Save data in session
        $equote_data_session = $equoteArray;
        // save array in log
        error_log(print_r($equoteArray, true), 3, "$HOME_DIR/cache/equotes/equote.log");
        // save xml in cache equotes files
        file_put_contents("$HOME_DIR/cache/equotes/" . time() . '.xml', $xml);
        // save xml in session
        $equote_data_session['xml'] = $xml;

        global $kernel;
        $container = $kernel->getContainer();
        $session = $container->get('request_stack')->getSession();
        $session->set('_legacy_equote_data', $equoteArray['header']);
        // confirmation message
        $route = $container->get('router')->generate('sales_orders_transfer');

        $body .= <<<EOF
<br><br><br><br><br><br>
<a href="$route">Continue transfer...</a>
EOF;
        break;
}
