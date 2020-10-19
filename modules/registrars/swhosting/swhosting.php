<?php
/**
 * WHMCS SDK SW Hosting Registrar Module
 *
 * Registrar Modules allow you to create modules that allow for domain
 * registration, management, transfers, and other functionality within
 * WHMCS.
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Domain\Domain;
use WHMCS\Domains\DomainLookup\ResultsList;
use WHMCS\Domains\DomainLookup\SearchResult;
use WHMCS\Module\Registrar\SWHosting\RestApiClient;

/**
 * Define module related metadata
 *
 * Provide some module information including the display name and API Version to
 * determine the method of decoding the input values.
 *
 * @return array
 */
function swhosting_MetaData()
{
    return array(
        'DisplayName' => 'SW Hosting',
        'APIVersion' => '0.1',
    );
}
/**
 * Define registrar configuration options.
 *
 * The values you return here define what configuration options
 * we store for the module. These values are made available to
 * each module function.
 *
 * @return array
 */
function swhosting_getConfigArray()
{
    return array(
        // Friendly display name for the module
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'SW Hosting',
        ),
        // a text field type allows for single line text input
        'bearerToken' => array(
            'FriendlyName' => 'Bearer Token',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Get your token inside SW Panel <a href="https://www.swpanel.com?utm_source=whmcs" target="_blank">https://www.swpanel.com</a>',
        ),
        // the yesno field type displays a single checkbox option
        'isTest' => array(
            'FriendlyName' => 'Test mode?',
            'Type' => 'yesno',
            'Description' => 'Tick to enable test mode',
        )
    );
}

/**
 * Register a domain.
 *
 * Attempt to register a domain with the domain registrar.
 *
 * This is triggered when the following events occur:
 * * Payment received for a domain registration order
 * * When a pending domain registration order is accepted
 * * Upon manual request by an admin user
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function swhosting_RegisterDomain($params)
{
    try {
        // Prepare data
        $action = 'registerDomain';
        $paramsAPI = [
            'endpoint' => 'domains/'.$params['domainname'].'/register',
            'method' => 'POST',
            'data' => [
                "debug" => $params,
                "years" => $params['regperiod'],
                "contactRegistrant" => [
                    "nombre" => $params["firstname"],
                    "apellidos" => $params["lastname"],
                    "empresa" => $params["companyname"],
                    "email" => $params["email"],
                    "direccion" => $params["address1"].' '.$params["address2"],
                    "poblacion" => $params["city"],
                    "provincia" => $params["state"],
                    "cpostal" => $params["postcode"],
                    "pais" => $params["countryname"],
                    "telefono" => $params["fullphonenumber"],
                    "fax" => "",
                    "es_forma_juridica" => (isset($params['additionalfields']['Legal Form'])) ? $params['additionalfields']['Legal Form'] : 1,
                    "nif" => (isset($params['additionalfields']['ID Form Number'])) ? $params['additionalfields']['ID Form Number'] : $params['tax_id'],
                ],
                "contactAdmin" => [
                    "nombre" => $params["adminfirstname"],
                    "apellidos" => $params["adminlastname"],
                    "empresa" => $params["admincompanyname"],
                    "email" => $params["adminemail"],
                    "direccion" => $params["adminaddress1"].' '.$params["adminaddress2"],
                    "poblacion" => $params["admincity"],
                    "provincia" => $params["adminstate"],
                    "cpostal" => $params["adminpostcode"],
                    "pais" => ($params["admincountry"] == 'ES') ? 'Spain' : $params["admincountry"],
                    "telefono" => $params["adminfullphonenumber"],
                    "fax" => "",
                    "es_forma_juridica" => (isset($params['additionalfields']['Legal Form'])) ? $params['additionalfields']['Legal Form'] : 1,
                    "nif" => (isset($params['additionalfields']['ID Form Number'])) ? $params['additionalfields']['ID Form Number'] : $params['tax_id'],
                ],
                "nameServers" => [
                    $params['ns1'], $params['ns2'], $params['ns3'], $params['ns4']
                ],
            ]
        ];

        // Call API
        $api = new RestApiClient($params['bearerToken'], ($params['isTest']=='on'));
        $response = $api->call($paramsAPI['endpoint'], $paramsAPI['method'], $paramsAPI['data']);
        logModuleCall('SWHostingRegistrar',$action,$paramsAPI,$response,$response,[]);

        return array(
            'success' => true,
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Initiate domain transfer.
 *
 * Attempt to create a domain transfer request for a given domain.
 *
 * This is triggered when the following events occur:
 * * Payment received for a domain transfer order
 * * When a pending domain transfer order is accepted
 * * Upon manual request by an admin user
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function swhosting_TransferDomain($params)
{
    try {
        // Prepare data
        $action = 'transferDomain';
        $paramsAPI = [
            'endpoint' => 'domains/'.$params['domainname'].'/transfer',
            'method' => 'POST',
            'data' => [
                "years" => $params['regperiod'],
                "authcode" => $params['eppcode'],
                "contactRegistrant" => [
                    "nombre" => $params["firstname"],
                    "apellidos" => $params["lastname"],
                    "empresa" => $params["companyname"],
                    "email" => $params["email"],
                    "direccion" => $params["address1"].' '.$params["address2"],
                    "poblacion" => $params["city"],
                    "provincia" => $params["state"],
                    "cpostal" => $params["postcode"],
                    "pais" => $params["countryname"],
                    "telefono" => $params["fullphonenumber"],
                    "fax" => "",
                    "nif" => $params['tax_id'],
                ],
                "contactAdmin" => [
                    "nombre" => $params["adminfirstname"],
                    "apellidos" => $params["adminlastname"],
                    "empresa" => $params["admincompanyname"],
                    "email" => $params["adminemail"],
                    "direccion" => $params["adminaddress1"].' '.$params["adminaddress2"],
                    "poblacion" => $params["admincity"],
                    "provincia" => $params["adminstate"],
                    "cpostal" => $params["adminpostcode"],
                    "pais" => $params["admincountry"],
                    "telefono" => $params["adminfullphonenumber"],
                    "fax" => "",
                    "nif" => $params['tax_id'],
                ],
                "nameServers" => [
                    $params['ns1'], $params['ns2'], $params['ns3'], $params['ns4']
                ],
            ]
        ];

        // Call API
        $api = new RestApiClient($params['bearerToken'], ($params['isTest']=='on'));
        $response = $api->call($paramsAPI['endpoint'], $paramsAPI['method'], $paramsAPI['data']);
        logModuleCall('SWHostingRegistrar',$action,$paramsAPI,$response,$response,[]);

        return array(
            'success' => true,
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Renew a domain.
 *
 * Attempt to renew/extend a domain for a given number of years.
 *
 * This is triggered when the following events occur:
 * * Payment received for a domain renewal order
 * * When a pending domain renewal order is accepted
 * * Upon manual request by an admin user
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function swhosting_RenewDomain($params)
{
    try {
        // Prepare data
        $domainId = $params['domainid'];
        $domainInfo = Domain::findOrFail($params['domainid']);

        $action = 'renewDomain';
        $paramsAPI = [
            'endpoint' => 'domains/'.$params['domainname'].'/renew',
            'method' => 'POST',
            'data' => [
                "current_expiration_date" => $domainInfo->expirydate->toDateString(),
                "years" => $params['regperiod']
            ]
        ];

        // Call API
        $api = new RestApiClient($params['bearerToken'], ($params['isTest']=='on'));
        $response = $api->call($paramsAPI['endpoint'], $paramsAPI['method'], $paramsAPI['data']);
        logModuleCall('SWHostingRegistrar',$action,$paramsAPI,$response,$response,[]);

        return array(
            'success' => true,
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Fetch current nameservers.
 *
 * This function should return an array of nameservers for a given domain.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function swhosting_GetNameservers($params)
{
    try {
        // Prepare data
        $action = 'getNameservers';
        $paramsAPI = [
            'endpoint' => 'domains/'.$params['domainname'],
            'method' => 'GET',
        ];

        // Call API
        $api = new RestApiClient($params['bearerToken'], ($params['isTest']=='on'));
        $response = $api->call($paramsAPI['endpoint'], $paramsAPI['method'], $paramsAPI['data']);
        logModuleCall('SWHostingRegistrar',$action,$paramsAPI,$response,$response,[]);

        // Output
        $results = [];
        $i = 1;
        foreach ($response->nameServers as $nameserver) {
            $results['ns'.$i] = $nameserver;
            $i++;
        }
        logModuleCall('SWHostingRegistrar',$action,$paramsAPI,$results,$results,[]);

        return $results;

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Save nameserver changes.
 *
 * This function should submit a change of nameservers request to the
 * domain registrar.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function swhosting_SaveNameservers($params)
{
    try {
        // Prepare data
        $action = 'saveNameservers';
        $paramsAPI = [
            'endpoint' => 'domains/'.$params['domainname'],
            'method' => 'PATCH',
            'data' => [
                'nameServers' => [
                    $params['ns1'], $params['ns2'], $params['ns3'], $params['ns4']
                ]
            ],
            'params' => $params
        ];

        // Call API
        $api = new RestApiClient($params['bearerToken'], ($params['isTest']=='on'));
        $response = $api->call($paramsAPI['endpoint'], $paramsAPI['method'], $paramsAPI['data']);
        logModuleCall('SWHostingRegistrar',$action,$paramsAPI,$response,$response,[]);

        return array(
            'success' => true,
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Get the current WHOIS Contact Information.
 *
 * Should return a multi-level array of the contacts and name/address
 * fields that be modified.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function swhosting_GetContactDetails($params)
{
    try {
        // Prepare data
        $action = 'GetContactDetails';
        $paramsAPI = [
            'endpoint' => 'domains/'.$params['domainname'],
            'method' => 'GET'
        ];

        // Call API
        $api = new RestApiClient($params['bearerToken'], ($params['isTest']=='on'));
        $response = $api->call($paramsAPI['endpoint'], $paramsAPI['method'], $paramsAPI['data']);
        logModuleCall('SWHostingRegistrar',$action,$paramsAPI,$response,$response,[]);

        $contactRegistrantId = $response->contactRegistrantId;
        $contactAdminId = $response->contactAdminId;
        $contactTechId = $response->contactTechId;

        $contacts = array_unique([
            $contactRegistrantId,
            $contactAdminId,
            $contactTechId
        ]);

        // Fetch contacts details
        $dataContacts = [];
        foreach ($contacts as $contact) {
            $paramsAPI = [
                'endpoint' => 'domains/contacts/'.$contact,
                'method' => 'GET'
            ];
            $api = new RestApiClient($params['bearerToken'], ($params['isTest']=='on'));
            $response = $api->call($paramsAPI['endpoint'], $paramsAPI['method'], $paramsAPI['data']);
            // logModuleCall('SWHostingRegistrar',$action.'/contact',$paramsAPI,$response,$response,[]);
            $dataContacts[$contact] = $response;
        }

        $contactRegistrant = $dataContacts[$contactRegistrantId];
        $contactAdmin = $dataContacts[$contactAdminId];
        $contactTech = $dataContacts[$contactTechId];
        logModuleCall('SWHostingRegistrar',$action.'/contacts',$paramsAPI,$dataContacts,$dataContacts,[]);

       return array(
            'Registrant' => array(
                'First Name' => $contactRegistrant->nombre,
                'Last Name' => $contactRegistrant->apellidos,
                'Company Name' => $contactRegistrant->empresa,
                'Email Address' => $contactRegistrant->email,
                'Address 1' => $contactRegistrant->direccion,
                'Address 2' => '',
                'City' => $contactRegistrant->poblacion,
                'State' => $contactRegistrant->provincia,
                'Postcode' => $contactRegistrant->cpostal,
                'Country' => $contactRegistrant->pais,
                'Phone Number' => $contactRegistrant->telefono,
                'Fax Number' => $contactRegistrant->fax,
            ),
            'Admin' => array(
                 'First Name' => $contactAdmin->nombre,
                'Last Name' => $contactAdmin->apellidos,
                'Company Name' => $contactAdmin->empresa,
                'Email Address' => $contactAdmin->email,
                'Address 1' => $contactAdmin->direccion,
                'Address 2' => '',
                'City' => $contactAdmin->poblacion,
                'State' => $contactAdmin->provincia,
                'Postcode' => $contactAdmin->cpostal,
                'Country' => $contactAdmin->pais,
                'Phone Number' => $contactAdmin->telefono,
                'Fax Number' => $contactAdmin->fax,
            ),
            'Technical' => array(
                'First Name' => $contactTech->nombre,
                'Last Name' => $contactTech->apellidos,
                'Company Name' => $contactTech->empresa,
                'Email Address' => $contactTech->email,
                'Address 1' => $contactTech->direccion,
                'Address 2' => '',
                'City' => $contactTech->poblacion,
                'State' => $contactTech->provincia,
                'Postcode' => $contactTech->cpostal,
                'Country' => $contactTech->pais,
                'Phone Number' => $contactTech->telefono,
                'Fax Number' => $contactTech->fax,
            ),
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Update the WHOIS Contact Information for a given domain.
 *
 * Called when a change of WHOIS Information is requested within WHMCS.
 * Receives an array matching the format provided via the `GetContactDetails`
 * method with the values from the users input.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function swhosting_SaveContactDetails($params)
{
    try {
        $contactDetails = $params['contactdetails'];

        // Prepare data
        $action = 'saveContactDetails';
        $paramsAPI = [
            'endpoint' => 'domains/'.$params['domainname'],
            'method' => 'PATCH',
            'data' => [
                "contactRegistrant" => [
                    "nombre" => $contactDetails['Registrant']['First Name'],
                    "apellidos" => $contactDetails['Registrant']['Last Name'],
                    "empresa" => $contactDetails['Registrant']['Company Name'],
                    "email" => $contactDetails['Registrant']['Email Address'],
                    "direccion" => $contactDetails['Registrant']['Address 1'].' '.$contactDetails['Registrant']['Address 2'],
                    "poblacion" => $contactDetails['Registrant']["City"],
                    "provincia" => $contactDetails['Registrant']["State"],
                    "cpostal" => $contactDetails['Registrant']["Postcode"],
                    "pais" => $contactDetails['Registrant']["Country"],
                    "telefono" => $contactDetails['Registrant']["Phone Number"],
                    "fax" => "",
                    "nif" => "",
                ],
                "contactAdmin" => [
                    "nombre" => $contactDetails['Admin']['First Name'],
                    "apellidos" => $contactDetails['Admin']['Last Name'],
                    "empresa" => $contactDetails['Admin']['Company Name'],
                    "email" => $contactDetails['Admin']['Email Address'],
                    "direccion" => $contactDetails['Admin']['Address 1'].' '.$contactDetails['Admin']['Address 2'],
                    "poblacion" => $contactDetails['Admin']["City"],
                    "provincia" => $contactDetails['Admin']["State"],
                    "cpostal" => $contactDetails['Admin']["Postcode"],
                    "pais" => $contactDetails['Admin']["Country"],
                    "telefono" => $contactDetails['Admin']["Phone Number"],
                    "fax" => "",
                    "nif" => "",
                ],
                "contactTech" => [
                    "nombre" => $contactDetails['Technical']['First Name'],
                    "apellidos" => $contactDetails['Technical']['Last Name'],
                    "empresa" => $contactDetails['Technical']['Company Name'],
                    "email" => $contactDetails['Technical']['Email Address'],
                    "direccion" => $contactDetails['Technical']['Address 1'].' '.$contactDetails['Technical']['Address 2'],
                    "poblacion" => $contactDetails['Technical']["City"],
                    "provincia" => $contactDetails['Technical']["State"],
                    "cpostal" => $contactDetails['Technical']["Postcode"],
                    "pais" => $contactDetails['Technical']["Country"],
                    "telefono" => $contactDetails['Technical']["Phone Number"],
                    "fax" => "",
                    "nif" => "",
                ]
            ]
        ];

        // Call API
        $api = new RestApiClient($params['bearerToken'], ($params['isTest']=='on'));
        $response = $api->call($paramsAPI['endpoint'], $paramsAPI['method'], $paramsAPI['data']);
        logModuleCall('SWHostingRegistrar',$action,$paramsAPI,$response,$response,[]);

        return array(
            'success' => true,
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}


/**
 * Check Domain Availability.
 *
 * Determine if a domain or group of domains are available for
 * registration or transfer.
 *
 * @param array $params common module parameters
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @see \WHMCS\Domains\DomainLookup\SearchResult
 * @see \WHMCS\Domains\DomainLookup\ResultsList
 *
 * @throws Exception Upon domain availability check failure.
 *
 * @return \WHMCS\Domains\DomainLookup\ResultsList An ArrayObject based collection of \WHMCS\Domains\DomainLookup\SearchResult results
 */
function swhosting_CheckAvailability($params)
{
    // Prepare data
    $action = 'checkAvailability';
    $name = $params['sld'];
    $tlds = $params['tldsToInclude'];
    foreach ($tlds as $key => $tld) {
        $tlds[$key] = ($tld[0] == '.') ? substr($tld,1) : $tld;
    }
    $endpoint = 'domains/available?name='.$name.'&tlds='.implode('&tlds=',$tlds);

    $paramsAPI = [
            'name' => $name,
            'tlds' => $tlds,
            'endpoint' => $endpoint
        ];


    // API call
    $results = new ResultsList();
    try {
        $api = new RestApiClient($params['bearerToken'], ($params['isTest']=='on'));
        $response = $api->call($paramsAPI['endpoint']);

        // Prepare output
        foreach ($response->domains as $domain) {
            $sld = explode(".",$domain->domain,2)[0];
            $tld = explode(".",$domain->domain,2)[1];
            $status = ($domain->available == 1) ? SearchResult::STATUS_NOT_REGISTERED : SearchResult::STATUS_REGISTERED;

            $searchResult = new SearchResult($sld, $tld);
            $searchResult->setStatus($status);
            $results->append($searchResult);
        }

    } catch (\Exception $e) {
        logModuleCall('SWHostingRegistrar',$action,$paramsAPI,$e->getMessage(),'',[]);

        return array(
            'error' => $e->getMessage(),
        );
    }

    logModuleCall('SWHostingRegistrar',$action,$paramsAPI,$results,'',[]);
    return $results;
}


// registrarmodule_DomainSuggestionOptions
// registrarmodule_GetDomainSuggestions


/**
 * Get registrar lock status.
 *
 * Also known as Domain Lock or Transfer Lock status.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return string|array Lock status or error message
 */
function swhosting_GetRegistrarLock($params)
{
    try {
        // Prepare data
        $action = 'getRegistrarLock';
        $paramsAPI = [
            'endpoint' => 'domains/'.$params['domainname'],
            'method' => 'GET',
        ];

        // Call API
        $api = new RestApiClient($params['bearerToken'], ($params['isTest']=='on'));
        $response = $api->call($paramsAPI['endpoint'], $paramsAPI['method'], $paramsAPI['data']);
        logModuleCall('SWHostingRegistrar',$action,$paramsAPI,$response,$response,[]);

        // Output
        return ($response->locked == 'S') ? 'locked' : 'unlocked';

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Set registrar lock status.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function swhosting_SaveRegistrarLock($params)
{
    try {
        // Prepare data
        $action = 'saveRegistrarLock';
        $paramsAPI = [
            'endpoint' => 'domains/'.$params['domainname'],
            'method' => 'PATCH',
            'data' => [
                'locked' => ($params['lockenabled'] == 'locked')
            ]
        ];

        // Call API
        $api = new RestApiClient($params['bearerToken'], ($params['isTest']=='on'));
        $response = $api->call($paramsAPI['endpoint'], $paramsAPI['method'], $paramsAPI['data']);
        logModuleCall('SWHostingRegistrar',$action,$paramsAPI,$response,$response,[]);

        // Output
        return array(
            'success' => 'success',
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Request EEP Code.
 *
 * Supports both displaying the EPP Code directly to a user or indicating
 * that the EPP Code will be emailed to the registrant.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 *
 */
function swhosting_GetEPPCode($params)
{
    try {
        // Prepare data
        $action = 'getEPPCode';
        $paramsAPI = [
            'endpoint' => 'domains/'.$params['domainname'],
            'method' => 'GET',
        ];

        // Call API
        $api = new RestApiClient($params['bearerToken'], ($params['isTest']=='on'));
        $response = $api->call($paramsAPI['endpoint'], $paramsAPI['method'], $paramsAPI['data']);
        logModuleCall('SWHostingRegistrar',$action,$paramsAPI,$response,$response,[]);

        // Output
        return array(
                'eppcode' => $response->authcode,
            );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Sync Domain Status & Expiration Date.
 *
 * Domain syncing is intended to ensure domain status and expiry date
 * changes made directly at the domain registrar are synced to WHMCS.
 * It is called periodically for a domain.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function swhosting_Sync($params)
{
    try {
        // Prepare data
        $action = 'sync';
        $paramsAPI = [
            'endpoint' => 'domains/'.$params['domainname'],
            'method' => 'GET',
            'params' => $params
        ];

        // Call API
        $api = new RestApiClient($params['bearerToken'], ($params['isTest']=='on'));
        $response = $api->call($paramsAPI['endpoint'], $paramsAPI['method'], $paramsAPI['data']);
        logModuleCall('SWHostingRegistrar',$action,$paramsAPI,$response,$response,[]);

        // Output
        return array(
                'expirydate' => $response->expiration, // Format: YYYY-MM-DD
                'active' => ($response->status == 'ACTIVE'), // Return true if the domain is active
                'expired' => false, // Return true if the domain has expired
                'transferredAway' => false, // Return true if the domain is transferred out
            );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Incoming Domain Transfer Sync.
 *
 * Check status of incoming domain transfers and notify end-user upon
 * completion. This function is called daily for incoming domains.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function swhosting_TransferSync($params)
{
    try {
        // Prepare data
        $action = 'transferSync';
        $paramsAPI = [
            'endpoint' => 'domains/'.$params['domainname'],
            'method' => 'GET',
            'params' => $params
        ];

        // Call API
        $api = new RestApiClient($params['bearerToken'], ($params['isTest']=='on'));
        $response = $api->call($paramsAPI['endpoint'], $paramsAPI['method'], $paramsAPI['data']);
        logModuleCall('SWHostingRegistrar',$action,$paramsAPI,$response,$response,[]);

        // Output
        $status = $response->status;
        if ($status == 'ACTIVE') {
            return array(
                'completed' => true,
                'expirydate' => $response->expiration, // Format: YYYY-MM-DD
            );
        } elseif ($status == 'FAILED') {
            return array(
                'failed' => true,
                'reason' => $response->message, // Reason for the transfer failure if available
            );
        } else {
            // No status change, return empty array
            return array();
        }

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}
