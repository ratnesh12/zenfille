<?php
	$template = array (
                    'table_open'          => '<table border="0" cellpadding="4" cellspacing="0" class="variable_list" width="700px">',
              );
	$this -> table -> set_template($template);
	$this -> table -> add_row('client reference number', '%CLIENT_REFERENCE_NUMBER%');
	$this -> table -> add_row('client firstname', '%CLIENT_FIRSTNAME%');
	$this -> table -> add_row('client lastname', '%CLIENT_LASTNAME%');
	$this -> table -> add_row('client email', '%CLIENT_EMAIL%');
	$this -> table -> add_row('client phone', '%CLIENT_PHONE%');
	$this -> table -> add_row('client address', '%CLIENT_ADDRESS%');
    $this -> table -> add_row('client address2', '%CLIENT_ADDRESS2%');
	$this -> table -> add_row('client company name', '%CLIENT_COMPANY_NAME%');
	$this -> table -> add_row('client city', '%CLIENT_CITY%');
	$this -> table -> add_row('client state', '%CLIENT_STATE%');
	$this -> table -> add_row('client zip code', '%CLIENT_ZIP_CODE%');
    $this -> table -> add_row('client country', '%CLIENT_COUNTRY%');
	$this -> table -> add_row('client fax', '%CLIENT_FAX%');
	$this -> table -> add_row('&nbsp;', '&nbsp;');
	$this -> table -> add_row('case link (ex. http://zenfile.com/client/cases/view/777', '%CASE_LINK%');
	$this -> table -> add_row('case countries', '%CASE_COUNTRIES%');
	$this -> table -> add_row('case number', '%CASE_NUMBER%');
	$this -> table -> add_row('PARKIP case number', '%PARKIP_CASE_NUMBER%');
	$this -> table -> add_row('case type (pct intake, direct filing, ep validation)', '%CASE_TYPE%');
	$this -> table -> add_row('case application number', '%CASE_APPLICATION_NUMBER%');
	$this -> table -> add_row('case applicant', '%CASE_APPLICANT%');
	$this -> table -> add_row('case application title', '%CASE_APPLICATION_TITLE%');
	$this -> table -> add_row('case filing deadline', '%CASE_FILING_DEADLINE%');
	$this -> table -> add_row('case files (links for tracking)', '%CASE_FILES%');
	$this -> table -> add_row('case CC emails', '%CASE_CC%');
	$this -> table -> add_row('&nbsp;', '&nbsp;');
	$this -> table -> add_row('fa fee', '%FA_FEE%');
	$this -> table -> add_row('fa contact name', '%FA_NAME%');
	$this -> table -> add_row('fa country', '%FA_COUNTRY%');
	$this -> table -> add_row('fa filing deadline type (30 months, 31 months, ep validation)', '%FA_FILING_DEADLINE_TYPE%');
	$this -> table -> add_row('&nbsp;', '&nbsp;');
	$this -> table -> add_row('BDV Name', '%BDV_NAME%');
    $this -> table -> add_row('Case Manager', '%CASE_MANAGER%');
    $this -> table -> add_row('Case User Full Name', '%CASE_USER%');
    $this -> table -> add_row('New Password', '%NEW_PASSWORD%');
    $this -> table -> add_row('Portal email', '%PORTAL_EMAIL%');
    $this -> table -> add_row('Login Link', '%LOGIN_LINK%');
    $this -> table -> add_row('Case Countries Translation Associates Email', '%case_countries_translation-associates_email%');
	echo $this -> table -> generate();
?>