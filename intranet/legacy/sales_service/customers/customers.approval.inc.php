<?php

use ApiBundle\Client;
use AppBundle\Manager\FileManager;
use Symfony\Component\HttpClient\Exception\ClientException;

if(!$user->isInGroup(array("gg_SALES","gg_SALES_AGENTS","gg_SUPPORT","gg_ADMIN","role_ASM","role_SA","role_SAM","sales_cust_admin","superuser","role_SPM","role_EVP"))){
	$DEFAULT_ERROR[] = "ERROR: You do not have permission to access this function";
	return;
}

$asmList = tldCustomer::getAsmList();

switch($m[2]){
case 'process':
	if(empty($seqid)){
		$DEFAULT_ERROR[] = "ERROR: sequence id not set";
		break;
	}
	$seq = new tldSEQ($seqid);
	if($seq->isEmpty() OR !$seq->isSequence()){
		$DEFAULT_ERROR[] = "ERROR: sequence not valid";
		break;
	}
	if($seq->isClosed()){
		$DEFAULT_ERROR[] = "ERROR: sequence is closed";
		break;
	}
	$id = $seq->getParentID();
	$cust = new tldCustomer($id);
    $header = $cust->getHeader();
	if(empty($header)){
		$DEFAULT_ERROR[] = "ERROR: The customer ID#$id was not found or deleted. Please contact Sales Admin";
		$params = http_build_query(array(
			"m"=>array("0"=>"tasks","1"=>"task","2"=>"cancel"),
			"id"=>$seqid,
			"extras"=>base64_encode(serialize(array("cid"=>$id,"token"=>_createToken()))),
		));
		$body.=<<<EOF
		<ul>
			<li><a href="/en/private/calendar/calendar.php?$params">Cancel Sequence</a></li>
		</ul>
EOF;
		break;
	}

	switch($m[3]){
	case 'cancel':
		$form = new HTML_QuickForm('cancelCustomer', 'post');
		$form->addElement(	'hidden', 'm[0]', 'customers');
		$form->addElement(	'hidden', 'm[1]', 'approval');
		$form->addElement(	'hidden', 'm[2]', 'process');
		$form->addElement(	'hidden', 'm[3]', 'cancel');
		$form->addElement(	'hidden', 'seqid', $seqid);
		$companies = tldCustomer::getList("smartyOptions");
		$form->addElement(	'select', 'replace', "Replace Customer As",
			array(""=>"") + $companies);
		$form->addElement(	'submit', 'btnCancel', ' Continue ');
		$form->addRule('replace', 'This is required', 'required');

		if ($form->validate()){
		    global $kernel;
			$vars = tldUtils::cleanupFormInput($form->exportValues());
			$client = $kernel->getContainer()->get(Client::class);
			if (null === $customer = $client->findOneBy('sales/customers', ['legacyId' => $id])) {
                $DEFAULT_ERROR[] = 'Customer not found.';
                $body .= $form->toHTML();
                break;
            }

            if(!$kernel->getContainer()->get('security.authorization_checker.legacy')->isGranted('FEATURE_CUSTOMER_EDIT')) {
                $DEFAULT_ERROR[] = 'ERROR: You do not have the permission to access this page.';
                $body .= $form->toHTML();
                break;
            }

			$targetCustomer = $client->findOneBy('sales/customers', ['legacyId' => $vars['replace']]);
			$client->request('sales/customers', $customer->getIriId(), 'transfer', 'PUT',
                [
                    'json' => [
                        'target' => $targetCustomer['@id'],
                    ]
                ]);
			header("Location: http://{$_SERVER['HTTP_HOST']}/en/private/calendar/calendar.php?" . http_build_query(array(
				"m"=>array("0"=>"tasks","1"=>"task","2"=>"cancel"),
				"id"=>$seqid,
				"extras"=>base64_encode(serialize(array("cid"=>$id,"token"=>_createToken()))),
			)));
			exit;
		}else{
			$body .= $form->toHTML();
		}
	break;
	case 'disable':
        if (!$kernel->getContainer()->get('security.authorization_checker.legacy')->isGranted('FEATURE_CUSTOMER_EDIT')) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have the permission to access this page.';
            break;
        }
        try {
            $client = $kernel->getContainer()->get(Client::class);
        } catch(Exception $e) {
            return "ERROR: Could not get API client. Reason: " . $e->getMessage();
        }

        try {
            $customer = $client->findOneBy('sales/customers', ['legacyId' => $id]);
            $client->remove('sales/customers', $customer['id']);
        } catch(ClientException $e) {
            $DEFAULT_ERROR[] = sprintf('ERROR: Could not get or remove the customer. Reason: %s', $e->getMessage());
            break;
        }

		header("Location: http://{$_SERVER['HTTP_HOST']}/en/private/calendar/calendar.php?" . http_build_query(array(
			"m"=>array("0"=>"tasks","1"=>"task","2"=>"cancel"),
			"id"=>$seqid,
			"extras"=>base64_encode(serialize(array("cid"=>$id,"token"=>_createToken()))),
		)));
		exit;
	break;
	default:
		$form = new HTML_QuickForm('editCustomer', 'post');
		$form->addElement(	'hidden', 'm[0]', 'customers');
		$form->addElement(	'hidden', 'm[1]', 'approval');
		$form->addElement(	'hidden', 'm[2]', 'process');
		$form->addElement(	'hidden', 'seqid', $seqid);
		$form->addElement(	'text', 'customer_name', 'Company Name, LONG (SHORT)', array("size"=>"63"));
		$form->addElement(	'textarea', 'customer_address', 'Company Address',
			array("wrap"=>"VIRTUAL", "cols"=>"50", "rows"=>"5", "disabled" => "disabled"));
        $form->addElement(	'select', 'ctry_id', 'Country', array(""=>"")+tldCountry::optionsAsIDName());
        $form->addElement(	'text', 'customer_tel', 'Company Tel', array("size"=>"63"));
		$form->addElement(	'text', 'customer_fax', 'Company Fax', array("size"=>"63"));
		$form->addElement(	'text', 'url', 'Website', array("size"=>"63"));
		$form->addElement(	'select', 'asm_id', 'TLD Rep', array(""=>"")+$asmList);
		$form->addElement(	'static', null, 'Logo', $header['logo_file'] ? $header['logo_file'] : 'not selected');
		$form->addElement(	'file', 'file');
		$form->addGroup(
			array(
				$form->createElement(	'submit', 'btnAccept', ' Accept '),
				$form->createElement(	'submit', 'btnReject', ' Reject '),
				$form->createElement(	'submit', 'btnCancel', ' Cancel and Replace '),
				$form->createElement(	'submit', 'btnDisable', ' Cancel and Disable '),
			)
		);
		if(!$form->isSubmitted() OR $btnAccept){
			$form->addRule('customer_name', 'This is required', 'required');
			$form->addRule('asm_id', 'This is required', 'required');
			$form->addRule('ctry_id', 'This is required', 'required');
		}
		$form->applyFilter('customer_name', 'strtoupper');
		$form->applyFilter(array('customer_name','customer_address'), 'trim');
		$defaults = $header;
		if(!$header['asm_id']){
			$defaults['asm_id'] = $user->getId();
		}
		$form->setDefaults($defaults);
		if ($form->validate()){
		    global $kernel;
			$vars = tldUtils::cleanupFormInput($form->exportValues());
            if(!$kernel->getContainer()->get('security.authorization_checker.legacy')->isGranted('FEATURE_CUSTOMER_EDIT')) {
                $DEFAULT_ERROR[] = 'ERROR: You do not have the permission to access this page.';
                $body .= $form->toHTML();
                break;
            }

            try {
                /** @var Client $client */
                $client = $kernel->getContainer()->get(Client::class);
            } catch (Exception $e) {
                $DEFAULT_ERROR[] = 'ERROR: Could not get API client.';
                $body .= $form->toHTML();
                break;
            }
            $customerPayload = [];

            try {
                $customer = $client->findOneBy('sales/customers', ['legacyId' => $cust->getID()]);
            } catch (Exception $e) {
                $DEFAULT_ERROR[] = 'ERROR: Customer not found. Reason : '.$e->getMessage();
                $body .= $form->toHTML();
                break;
            }

            $resources = [
                'asm' => [
                    'people',
                    ['legacyId' => $vars['asm_id']]
                ],
                'country' => [
                    'countries',
                    ['legacyId' => $vars['ctry_id']]
                ]
            ];

            try {
                $country = $client->findOneBy('countries', ['legacyId' => $vars['ctry_id']]);
                $customerPayload['country'] = $country['@id'];
            } catch (Exception $e) {
                $DEFAULT_ERROR[] = 'ERROR: country not found. Reason : '.$e->getMessage();
                $body = $form->toHTML();
                break;
            }

            try {
                $asm = $client->findOneBy('people', ['legacyId' => $vars['asm_id']]);
                $businessUnit = $client->get($asm['businessUnit']['@id']);
                $region = $client->get($businessUnit['region']['@id']);
                $customerPayload['mainSalesRepresentative'] = ['asm' => $asm['@id'], 'subDivision' => $region['subDivision']['@id']];
            } catch (Exception $e) {
                $DEFAULT_ERROR[] = 'ERROR: country not found. Reason : '.$e->getMessage();
                $body = $form->toHTML();
                break;
            }

            $customerPayload['@id'] = $customer['@id'];
            $customerPayload['name'] = $customer['name'];
            $customerPayload['url'] = $vars['url'];
            $customerPayload['phone'] = $vars['customer_tel'];
            $customerPayload['fax'] = $vars['customer_fax'];

            try {
                $client->save('sales/customers', $customerPayload);
            } catch (ClientException $e) {
                $errors = json_decode($e->getResponse()->getContent(), true);
                $DEFAULT_ERROR[] = 'Could not update customer. Reason : '.$errors['hydra:description'];
                $body .= $form->toHTML();
                break;
            }

			$file = $form->getElement("file");
			$logo = $file->getValue();

            if(!empty($logo["tmp_name"])){
                $logo['file'] = new \Symfony\Component\HttpFoundation\File\UploadedFile($logo["tmp_name"], $logo["name"]);
                $fileId = isset($customer['logo']['id']) ? $customer['logo']['id'] : null;
            	$kernel->getContainer()->get(FileManager::class)->updateImage($customer, 'sales/customers', $logo, 'logo', $fileId);

        	}
			if($btnCancel){
				header("Location: http://{$_SERVER['HTTP_HOST']}$php_self?" . http_build_query(array(
					"m"=>array("0"=>"customers","1"=>"approval","2"=>"process","3"=>"cancel"),
					"seqid"=>$seqid,
				)));
			}elseif($btnDisable){
				header("Location: http://{$_SERVER['HTTP_HOST']}$php_self?" . http_build_query(array(
					"m"=>array("0"=>"customers","1"=>"approval","2"=>"process","3"=>"disable"),
					"seqid"=>$seqid,
				)));
			}elseif($btnReject){
				header("Location: http://{$_SERVER['HTTP_HOST']}/en/private/calendar/calendar.php?" . http_build_query(array(
					"m"=>array("0"=>"tasks","1"=>"task","2"=>"reject"),
					"id"=>$seqid,
					"extras"=>base64_encode(serialize(array("cid"=>$id,"token"=>_createToken()))),
				)));
			}else{
				header("Location: http://{$_SERVER['HTTP_HOST']}/en/private/calendar/calendar.php?" . http_build_query(array(
					"m"=>array("0"=>"tasks","1"=>"task","2"=>"accept"),
					"id"=>$seqid,
					"extras"=>base64_encode(serialize(array("cid"=>$id,"token"=>_createToken()))),
				)));
			}
			exit;
		}else{
			$body .= <<<EOF
			<h3>New Customer Approval Help:</h3>
			<p>Here is a description of how to use the approval buttons below:</p>
			<ul>
				<li><strong>Accept</strong> -
				Accept the new customer and proceed with the sequence process</li>
				<li><strong>Reject</strong> -
				Reject the new sequence and transfer the sequence back to the last step approver</li>
				<li><strong>Cancel and Replace</strong> -
				Use this option to replace this new customer with another existing customer already
				in the database and close the sequence</li>
				<li><strong>Cancel and Disable</strong> -
				Use this option to disable this new customer and close the sequence</li>
			</ul><br/>
EOF;

			$editUrl = $kernel->getContainer()->get('router')->generate('legacy_sales', [
			    'm' => ['customers', 'view', 'edit'],
                'id' => $header['id'],
            ], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_PATH);

			$body .= <<<EOF
<p>If you need to update the customer address, <strong style="color: red">please use the new form</strong> (<a href="$editUrl">click here</a>).</p>
EOF;
			$body .= $form->toHTML();

		}
	break;
	}
break;
case 'new':
    $body .= "This page has been migrated and should not be displayed anymore.";
break;
case 'seq':
    $body .= "This page has been migrated and should not be displayed anymore.";
break;
default:
	$body .= <<<EOF
	<h3>New Customer Approval Processing</h3><br/>
	<a href="$php_self?m[0]=customers&m[1]=approval&m[2]=new">Create New Customer Sequence</a>
EOF;
break;
}


function _createToken(){
	global $sess;
	$token = uniqid("SEQ");
	$sess["new_cust_approval_token"]=$token;
	return $token;
}

