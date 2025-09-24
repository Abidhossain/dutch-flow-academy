<?php
/*****************************************
 * Simple Moneybird API interface
 *
 * Version: 1.15.1
 *****************************************/

class Moneybird2Api {

    private $access_token;
    private $admin_id;
    public $debug_log = '';                 // Set a filename to enable debug logging
    private $errors = array();              // If an error occurs, a description is appended to this array
    public $request_limit_reached = false;  // Set to true if the latest API request hit the request throttling limit

    function __construct($access_token, $admin_id='') {
        // $access_token: a valid oauth2 access token
        // $admin_id: optional administration id. If none is specified, the default admin id will be fetched

        // This is the only method that can throw an exception.
        // All other methods will return false upon failure, and will append an error description to $this->errors
        // $this->debug_log = dirname( __FILE__ ) . '/debug_log.txt';

        $this->access_token = $access_token;
        if ($admin_id != '') {
            $this->admin_id = $admin_id;
        } else {
            $default_admin_id = $this->getDefaultAdministrationId();
            if ($default_admin_id === false) {
                throw new Exception("Cannot fetch default administration id", 1);
            }
            $this->admin_id = $default_admin_id;
        }
    }

    private function log($message) {
        // Write a message to the debug log file
        if (empty($this->debug_log)) { return; }

        $fp = fopen($this->debug_log, "a");
        if (flock($fp, LOCK_EX)) {
            fwrite($fp, date("Y-m-d H:i:s") . "\n" . $message . "\n\n");
            fflush($fp);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }

    /*
     * Diagnostics
     */

    public function isConnectionWorking() {
        // Check if the communication with the MoneyBird API is working.
        // This function fetches the list of available administrations, and checks if our admin_id is in that list.
        // Returns: (bool)
        $administrations = $this->request('administrations');
        if ($administrations === false) { return false; }
        foreach ($administrations as $administration) {
            if ($this->admin_id == $administration->id) {
                return true;
            }
        }
        return false;
    }

    public function getAdminId() {
        return $this->admin_id;
    }

    public function getErrors() {
        // Returns: (array) error descriptions (oldest first)
        return $this->errors;
    }

    public function getLastError() {
        // Returns: (string) the last error description or false in case of no errors
        if (count($this->errors) < 1) {
            return false; // No errors
        } else {
            return end($this->errors);
        }
    }

    public function getLastErrorString() {
        // Returns: (string) the last error description or empty string in case of no errors
        if (empty($this->errors)) {
            return ''; // No errors
        } else {
            return end($this->errors);
        }
    }

    /*
     * Administrations
     */

    public function getAdministrations() {
        return $this->request('administrations');
    }


    /*
     * Contacts
     */

    public function getContact($contact_id) {
        return $this->request('contacts/' . $contact_id);
    }

    public function getContactByCustomerId($customer_id) {
        return $this->request('contacts/customer_id/' . $customer_id);
    }

    public function getContactsSynchronizationList() {
        return $this->request('contacts/synchronization');
    }

    public function getContacts($contact_ids) {
        // $contact_ids: array of integers
        // Returns at most 100 contacts, even if count($contact_ids) > 100
        return $this->request('contacts/synchronization', 'POST', array('ids' => $contact_ids));
    }

    public function getContactsByQuery($query, $page=0) {
        // $query: contacts search query
        // Returns all contacts that match the query
        if ($page == 0) {
            $all = array();
            for ($p=1; $p<10; $p++) {
                $batch = $this->request('contacts', 'GET', array('query' => $query, 'per_page' => 100, 'page' => $p));
                if (!empty($batch) && is_array($batch)) {
                    $all = array_merge($all, $batch);
                    if (count($batch) < 100) {
                        break; // Last page
                    }
                } else {
                    break; // Empty page
                }
            }
            return $all;
        } else {
            return $this->request('contacts', 'GET', array('query' => $query, 'per_page' => 100, 'page' => $page));
        }
    }

    public function createContact($contact) {
        // Create a new contact
        // Required fields in $contact: 'company_name' OR 'firstname' OR 'lastname'
        if ((!isset($contact['company_name']) || !$contact['company_name'])
            && (!isset($contact['firstname']) || !$contact['firstname'])
            && (!isset($contact['lastname']) || !$contact['lastname'])) {

            $this->errors[] = 'createContact(): specify at least company_name, firstname or lastname';
            return false;
        }

        $response = $this->request('contacts', 'POST', array('contact' => $contact));
        if ($response !== false) {
            return $response;
        }

        // Maybe try to assign fixed customer_id if automatically generated id is not accepted
        if (empty($contact['customer_id'])) {
            $customer_id = 100;
            while ((stripos($this->getLastErrorString(), 'customer_id') !== false) && ($customer_id <= 100*2^10)) {
                $contact['customer_id'] = $customer_id;
                $response = $this->request('contacts', 'POST', array('contact' => $contact));
                if ($response !== false) {
                    return $response;
                }
                $customer_id *= 2;
            }
        }
        return false;
    }

    public function updateContact($contact_id, $data) {
        // Update an existing contact
        if (isset($data['country']) && empty($data['country'])) {
            unset($data['country']);
        }
        return $this->request('contacts/'.$contact_id, 'PATCH', array('contact' => $data));
    }

    public function createContactPerson($contact_id, $contact_person) {
        // Create a new contact person
        // Required fields in $contact_person: 'firstname' OR 'lastname'
        if ((!isset($contact_person['firstname']) || !$contact_person['firstname']) &&
            (!isset($contact_person['lastname']) || !$contact_person['lastname'])) {

            $this->errors[] = 'createContactPerson(): specify at firstname or lastname';
            return false;
        }

        return $this->request('contacts/'.$contact_id.'/contact_people', 'POST', array('contact_person' => $contact_person));
    }

    /*
     * Custom fields
     */

    public function getCustomFields() {
        return $this->request('custom_fields');
    }


    /*
     * Document styles
     */

    public function getDocumentStyles() {
        return $this->request('document_styles');
    }


    /*
     * Identities
     */

    public function getIdentities() {
        return $this->request('identities');
    }

    public function getDefaultIdentity() {
        return $this->request('identities/default');
    }

    public function getIdentity($identity_id) {
        return $this->request('identities/' . $identity_id);
    }


    /*
     * Ledger accounts
     */

    public function getLedgerAccounts() {
        return $this->request('ledger_accounts');
    }


    /*
     * Projects
     */

    public function getProjects() {
        // Get all active projects, limited to 10 pages (10*100=1000 projects)
        $projects = array();
        for ($page=1; $page < 10; $page++) {
            $batch = $this->request('projects', 'GET', array('per_page' => 100, 'page' => $page, 'filter' => 'state:active'));
            if (!empty($batch) && is_array($batch)) {
                $projects = array_merge($projects, $batch);
                if (count($batch) < 100) {
                    break;
                }
            } else {
                break;
            }
        }
        return $projects;
    }


    /*
     * Sales invoices
     */

    public function getSalesInvoice($id) {
        return $this->request('sales_invoices/' . $id);
    }

    public function getSalesInvoiceByInvoiceId($invoice_id) {
        // $invoice_id is the invoice number, such as 2015-0001
        return $this->request('sales_invoices/find_by_invoice_id/' . trim($invoice_id));
    }

    public function getSalesInvoicePdf($id, $packing_slip=false) {
        // Return url of sales invoice pdf or false
        if ($packing_slip) {
            return $this->request('sales_invoices/' . $id . '/download_packing_slip_pdf');
        } else {
            return $this->request('sales_invoices/' . $id . '/download_pdf');
        }
    }

    public function createSalesInvoice($invoice) {
        // Create a new sales invoice
        // Required fields in $invoice: 'contact_id'.
        if (!isset($invoice['contact_id']) || !$invoice['contact_id']) {
            $this->errors[] = 'createSalesInvoice(): specify the required fields: contact_id.';
            return false;
        }

        return $this->request('sales_invoices', 'POST', array('sales_invoice' => $invoice));
    }

    public function createRecurringSalesInvoice($invoice) {
        // Create a new recurring sales invoice
        // Required fields in $invoice: 'contact_id'.
        if (!isset($invoice['contact_id']) || !$invoice['contact_id']) {
            $this->errors[] = 'createRecurringSalesInvoice(): specify the required fields: contact_id.';
            return false;
        }

        return $this->request('recurring_sales_invoices', 'POST', array('recurring_sales_invoice' => $invoice));
    }

    public function createRecurringSalesInvoiceNote($invoice_id, $note, $is_todo=false) {
        // Create a note on a recurring sales invoice.
        $params = array('note' => array('note' => $note, 'todo' => $is_todo));
        return $this->request('recurring_sales_invoices/'.$invoice_id.'/notes', 'POST', $params);
    }

    public function deleteSalesInvoice($invoice_id) {
        // Delete a sales invoice.

        return $this->request('sales_invoices/'.$invoice_id, 'DELETE');
    }

    public function sendSalesInvoice($invoice_id, $sending = array()) {
        // Send an invoice.
        if (count($sending) > 0) {
            return $this->request('sales_invoices/'.$invoice_id.'/send_invoice', 'PATCH', array('sales_invoice_sending' => $sending));
        } else {
            return $this->request('sales_invoices/'.$invoice_id.'/send_invoice', 'PATCH');
        }

    }

    public function updateSalesInvoice($invoice_id, $invoice) {
        // Update invoice_id. $invoice only has to contain the fields to update.

        return $this->request('sales_invoices/'.$invoice_id, 'PATCH', array('sales_invoice' => $invoice));
    }

    public function registerPaymentSalesInvoice($invoice_id, $payment) {
        // [DEPRICATED] Register a payment
        // Use createSalesInvoicePayment instead.
        // $payment is an array and should contain at least keys 'payment_date' and 'price'.
        if (!isset($payment['payment_date']) || !$payment['payment_date']
            || !isset($payment['price']) || !$payment['price']) {
            $this->errors[] = 'registerPaymentSalesInvoice(): specify the required fields: payment_date, price.';
            return false;
        }

        return $this->request('sales_invoices/'.$invoice_id.'/register_payment', 'PATCH', array('payment' => $payment));
    }

    public function createSalesInvoicePayment($invoice_id, $payment) {
        // Create a payment for a sales invoice.
        // $payment is an array and should contain at least keys 'payment_date' and 'price'.
        if (!isset($payment['payment_date']) || !$payment['payment_date']
            || !isset($payment['price']) || !$payment['price']) {
            $this->errors[] = 'createSalesInvoicePayment(): specify the required fields: payment_date, price.';
            return false;
        }

        return $this->request('sales_invoices/'.$invoice_id.'/payments', 'POST', array('payment' => $payment));
    }

    public function deleteSalesInvoicePayment($invoice_id, $payment_id) {
        // Delete payment of a sales invoice.

        return $this->request('sales_invoices/'.$invoice_id.'/payments/'.$payment_id, 'DELETE');
    }

    public function createSalesInvoiceNote($invoice_id, $note, $is_todo=false) {
        // Create a note on a sales invoice.
        $params = array('note' => array('note' => $note, 'todo' => $is_todo));
        return $this->request('sales_invoices/'.$invoice_id.'/notes', 'POST', $params);
    }

    public function createSalesInvoiceAttachment($invoice_id, $filename) {
        // Add attachment to a sales invoice.
        if (function_exists('curl_file_create')) {
            $file = curl_file_create($filename);
        } else { 
            $file = '@' . realpath($filename);
        }
        $params = array('file' => $file);
        return $this->request('sales_invoices/'.$invoice_id.'/attachments', 'POST', $params);
    }

    /*
     * Estimates
     */

    public function getEstimate($id) {
        return $this->request('estimates/' . $id);
    }

    public function getEstimateByEstimateId($estimate_id) {
        // $estimate_id is the estimate number, such as 2016-0001
        return $this->request('estimates/find_by_estimate_id/' . trim($estimate_id));
    }

    public function getEstimatePdf($id) {
        return $this->request('estimates/' . $id . '/download_pdf');
    }

    public function createEstimate($estimate) {
        // Create a new estimate
        // Required fields in $estimate: 'contact_id'.
        if (!isset($estimate['contact_id']) || !$estimate['contact_id']) {
            $this->errors[] = 'createEstimate(): specify the required fields: contact_id.';
            return false;
        }

        return $this->request('estimates', 'POST', array('estimate' => $estimate));
    }

    public function sendEstimate($estimate_id, $sending = array()) {
        // Send an estimate.

        return $this->request('estimates/'.$estimate_id.'/send_estimate', 'PATCH', array('estimate_sending' => $sending));
    }

    public function updateEstimate($estimate_id, $estimate) {
        // Update estimate_id. $estimate only has to contain the fields to update.

        return $this->request('estimates/'.$estimate_id, 'PATCH', array('estimate' => $estimate));
    }

    public function createEstimateNote($estimate_id, $note, $is_todo=false) {
        // Create a note on an estimate.
        $params = array('note' => array('note' => $note, 'todo' => $is_todo));
        return $this->request('estimates/'.$estimate_id.'/notes', 'POST', $params);
    }

    public function createEstimateAttachment($estimate_id, $filename) {
        // Add attachment to an estimate.
        if (function_exists('curl_file_create')) {
            $file = curl_file_create($filename);
        } else { 
            $file = '@' . realpath($filename);
        }
        $params = array('file' => $file);
        return $this->request('estimates/'.$estimate_id.'/attachments', 'POST', $params);
    }

    /*
     * Products
     */

    public function getProducts($page=0) {
        // Products are loaded in pages of 10.
        // Use $page to specify the page; 0 corresponds to all pages.
        if ($page == 0) {
            $all = array();
            for ($p=1; $p<100; $p++) {
                $batch = $this->request('products', 'GET', array('page' => $p));
                if (!empty($batch) && is_array($batch)) {
                    $all = array_merge($all, $batch);
                } else {
                    break;
                }
            }
            return $all;
        } else {
            return $this->request('products', 'GET', array('page' => $page));
        }
    }


    /*
     * Purchase invoices
     */


    public function getPurchaseInvoice($id) {
        return $this->request('documents/purchase_invoices/' . $id);
    }

    public function createPurchaseInvoice($invoice) {
        // Create a new purchase invoice
        // Required fields in $invoice: 'contact_id', 'reference', 'date'.
        $required_fields = array('contact_id', 'reference', 'date');
        foreach ($required_fields as $field) {
            if (!isset($invoice[$field]) || !$invoice[$field]) {
                $this->errors[] = 'createPurchaseInvoice(): specify the required fields: contact_id, reference, date.';
                return false;
            }
        }

        return $this->request('documents/purchase_invoices', 'POST', array('purchase_invoice' => $invoice));
    }


    /*
     * Tax rates
     */

    public function getTaxRates() {
        // Get all active tax rates
        return $this->request('tax_rates', 'GET', array('filter' => 'active:true'));
    }


    /*
     * Workflows
     */

    public function getWorkflows() {
        return $this->request('workflows');
    }

    /*
     * Import mappings
     */

    private function isValidImportMappingType($type) {
        // Check whether $type is a valid import mapping type
        $supported_types =  array(
                                'financial_account',
                                'bank_mutation',
                                'contact',
                                'document_attachment',
                                'general_journal',
                                'identity',
                                'incoming_invoice',
                                'attachment',
                                'payment',
                                'history',
                                'invoice_attachment',
                                'transaction',
                                'ledger_account',
                                'tax_rate',
                                'product',
                                'print_invoice',
                                'recurring_template',
                                'invoice',
                                'workflow',
                                'document_style'
                            );
        return in_array($type, $supported_types);
    }

    public function getImportMappings($type) {
        // Get all import mappings for a specific type.
        // See isValidImportMappingType($type) for the list of valid types.
        if (!$this->isValidImportMappingType($type)) {
            $this->errors[] = 'getImportMappings(): the specified type is not supported.';
            return false;
        }

        return $this->request('import_mappings/'.$type);
    }

    public function getImportMapping($type, $id) {
        // Get import mapping for a specific type and (old or new) object id.
        // See isValidImportMappingType($type) for the list of valid types.
        if (!$this->isValidImportMappingType($type)) {
            $this->errors[] = 'getImportMapping(): the specified type is not supported.';
            return false;
        }

        return $this->request('import_mappings/'.$type.'/'.trim($id));
    }

    /*
     * Internal functions
     */

    public function request($resource_path, $request_type = 'GET', $parameters = array(), $num_retries=2) {
        /*
        Internal function to perform a request to the MoneyBird API.

        $resource_path: the path of the resource to request
        $request_type: any of {"DELETE", GET", "PATCH", "POST"}
        $parameters: associative array of request parameters
        $num_retries: number of retries before the request fails permanently

        Returns: (array/object) decoded result of request if successful, false otherwise
        */

        // Sanity checks
        if (!$this->access_token) {
            $this->errors[] = 'request(): access token not defined';
            return false;
        }
        if (!in_array($request_type, array('DELETE','GET','PATCH','POST'))) {
            $this->errors[] = 'request(): invalid request type specified ('.$request_type.')';
            return false;
        }

        // Build url
        $url = 'https://moneybird.com/api/v2/';
        if ($resource_path == 'administrations') {
            $url .= 'administrations.json';
        } else {
            if (!$this->admin_id) {
                $this->errors[] = 'request(): administration id is required but not defined';
                return false;
            }
            $url .= $this->admin_id.'/'.$resource_path.'.json';
        }
        if (($request_type=='GET') && $parameters) {
            $get_parameters = array();
            foreach($parameters as $k => $v) {
                $get_parameters[] = urlencode($k) . '=' . urlencode($v);
            }
            $url .= '?' . implode('&', $get_parameters);
        }

        // Set up cURL
        $ch = curl_init();
        $headers = [];
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $file_upload = isset($parameters['file']);
        if ($file_upload) {
            $content_type = 'multipart/mixed';
        } else {
            $content_type = 'application/json';
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: ' . $content_type,
            'Authorization: Bearer ' . $this->access_token
        ));
        curl_setopt($ch, CURLOPT_HEADERFUNCTION,
            function($curl, $header) use (&$headers) {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) // ignore invalid headers
                    return $len;

                $headers[strtolower(trim($header[0]))][] = trim($header[1]);

                return $len;
            }
        );
        if ($request_type == 'PATCH') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
        } elseif ($request_type == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            if ($file_upload) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
            }
        } elseif ($request_type == 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
        }

        // Execute request
        $result = curl_exec($ch);
        while (curl_errno($ch) && $num_retries > 0) {
            $this->errors[] = 'request('.$resource_path.'): cURL request failed. Error: ' . curl_error($ch) . ' Retrying...';
            $this->log($request_type . " " . $url . "\ncURL error " . curl_error($ch) . '\nRetrying...');
            sleep(1.0);
            $result = curl_exec($ch);
            $num_retries = $num_retries - 1;
        }

        if (curl_errno($ch)) {
            $this->errors[] = 'request('.$resource_path.'): cURL request failed. Error: ' . curl_error($ch);
            $this->log($request_type . " " . $url . "\ncURL error " . curl_error($ch));
            if (($request_type=='POST') || ($request_type=='PATCH')) {
                if (!$file_upload) {
                    $this->log('Payload: ' . json_encode($parameters));
                }
            }
            return false;
        } else {
            $http_response_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $this->log($request_type . " " . $url . "\nHTTP response code " . $http_response_code);
            if (($request_type=='POST') || ($request_type=='PATCH')) {
                if (!$file_upload) {
                    $this->log('Payload: ' . json_encode($parameters));
                }
            }
            curl_close($ch);
            if (($result !== false) && ($http_response_code < 300)) {
                // Standard response
                $this->request_limit_reached = false;
                return json_decode($result);
            } elseif (($http_response_code == 301) || ($http_response_code == 302)) {
                // Redirect
                $this->request_limit_reached = false;
                if (isset($headers['location']) && is_array($headers['location'])) {
                    return $headers['location'][0];
                } else {
                    return false;
                }
            } elseif ($http_response_code == 429) {
                // API request limit reached
                $this->request_limit_reached = true;
                $this->errors[] = 'request('.$resource_path.'): API request limit reached!';
                return false;
            } else {
                // Unknown response
                $error = 'request('.$resource_path.'): unexpected http response code: ' . $http_response_code;
                if ($result) {
                    $this->log('Response: ' . $result);
                    $body = json_decode($result);
                    if ($body && property_exists($body, 'error')) {
                        $error .= ' (' . print_r($body->error, true) . ')';
                    }
                }
                $this->errors[] = $error;
                return false;
            }
        }
    }

    private function getDefaultAdministrationId() {
        // Returns: id of the first administration that the user has access to

        $administrations = $this->request('administrations');
        if ($administrations === false) {
            return false;
        }
        if (count($administrations) < 1) {
            $this->errors[] = 'getDefaultAdministrationId(): no administrations available';
            return false;
        }

        try {
            return $administrations[0]->id;
        } catch (Exception $e) {
            $this->errors[] = 'getDefaultAdministrationId(): exception: ' . $e->getMessage();
            return false;
        }
    }
}