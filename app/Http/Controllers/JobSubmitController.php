<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;

class JobSubmitController extends Controller
{
    public function store(Request $request) {
        $data = $this->validate($request, [
            'firstname' => 'required|string|min:2',
            'lastname'  => 'required|string|min:2',
            'company' => 'required|string',
            'site' => 'required|url',
            "email" => 'required:email',
            'phone' => 'required|numeric',
        ]);

        $nutshell_status = FALSE;
        try {
            $apiKey   = '539b545ac31e5e2eb5d5d6f5a6fab8049dec1805';
            $username = 'fadib@eventuresworldwide.com';
// End of configuration

            require_once(base_path().'/nutshell/NutshellApi.php');
            $api = new \NutshellApi($username, $apiKey);

            $data = $request->except(['_token']);

            // Create a new contact and save its ID to $newContactId
            $params = array(
                'contact' => array(
                    'name' => $data['firstname'].' '.$data['lastname'],
                    'phone' => $data['phone'],
                    'email' =>$data['email']
                ),
            );
            $newContact = $api->call('newContact', $params);
            $newContactId = $newContact->id;

 // Create a new account that includes the contact we just added
            $params = array(
                'account' => array(
                    'name' => $data['firstname'].' '.$data['lastname'],
                    'url' => $data['site'],
                    'phone' => $data['phone'],
                    'contacts' => array(
                        array(
                            'id' => $newContactId,
                            'company' => $data['company']
                        ),
                    )
                ),
            );
            $newAccount = $api->newAccount($params);
            $newAccountId = $newAccount->id;

// Finally, create a lead that includes the account we just added
            $params = array(
                'lead' => array(
                    'primaryAccount' => array('id' => $newAccountId),
                    'confidence' => 70,
                    'market'   => array('id' => 1),
                    'contacts' => array(
                        array(
                            'relationship' => 'First Contact',
                            'id'           => $newContactId,
                        ),
                    ),
                    'products' => array(
                        array(
                            'relationship' => '',
                            'quantity'     => 15,
                            'price'        => array(
                                'currency_shortname' => 'USD',
                                'amount'   => 1000,
                            ),
                            'id'           => 4,
                        ),
                    ),
                    'sources' => array(
                        array('id' => 2),
                    ),
                    'assignee' => array(
                        'entityType' => 'Teams',
                        'id' => 1000,
                    )
                ),
            );
            $result = $api->newLead($params);
            $nutshell_status = true;
        } catch(\Exception $exception) {
            $nutshell_status = false;
        }

        $return_data = [];
        if($nutshell_status === false) {
            Mail::to('jtrika@gmail.com')->send(new SendMail($data));
            $return_data['success'] = true;
        }
        else {
            $return_data['fail'] = true;
        }

        return response()->json($return_data);
    }
}
