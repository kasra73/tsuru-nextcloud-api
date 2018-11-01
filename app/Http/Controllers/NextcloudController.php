<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Instance;

class NextcloudController extends Controller
{
  private $base_uri;
  private $plans = [
      [
        "name" => "free",
        'quota' => '500MB',
        "description" => " free for fun!",
        'price' => '0T'
      ],
      [
        "name" => "basic",
        'quota' => '1GB',
        "description" => "",
        'price' => '1000T'
      ],
      [
        "name" => "development",
        'quota' => '5GB',
        "description" => "",
        'price' => '4500T'
      ],
      [
        "name" => "advanced",
        'quota' => '20GB',
        "description" => "",
        'price' => '15000T'
      ],
      [
        "name" => "business",
        'quota' => '100GB',
        "description" => "",
        'price' => '50000T'
      ]
    ];
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
      $this->middleware('auth:api');
      $this->plans = collect($this->plans);
      $this->base_uri = config('nextcloud.base_uri');
      $this->username = config('nextcloud.admin_user');
      $this->password = config('nextcloud.admin_pass');
    }

    public function plans(Request $request)
    {
      $plans = [];
      foreach($this->plans as $plan) {
        $plans[] = [
          'name' => $plan['name'],
          'description' => $plan['quota'] . $plan['description'] . ' -> ' . $plan['price'],
        ];
      }
      return response()->json($plans);
    }

    public function update(Request $request, string $name)
    {
      abort(404);
    }
    
    public function bind(Request $request, string $name)
    {
      $instance = Instance::where('name', $name)->firstOrFail();
      return response()->json([
        'STORAGE_HOST' => $this->base_uri,
        'STORAGE_USERNAME' => $instance->username,
        'STORAGE_PASSWORD' => $instance->password,
      ]);
    }

    public function show(Request $request, string $name)
    {
      $instance = Instance::where('name', $name)->firstOrFail();
      return response()->json($instance, 500);
    }

    public function restrict(Request $request, string $name)
    {
      abort(404);
    }

    public function status(Request $request, string $name)
    {
      $instance = Instance::where('name', $name)->firstOrFail();
      return response("", 204);
    }

    public function unbind(Request $request, string $name)
    {
      return response("", 200);
    }

    public function delete(Request $request, string $name)
    {
      $instance = Instance::where('name', $name)->firstOrFail();

      $client = $this->getClient();
      $response = $client->request('DELETE', '/ocs/v2.php/cloud/users/' . $instance->username);
      $instance->delete();
      return response("", $response->getStatusCode());
    }

    public function bindApp(Request $request, string $name)
    {
      $instance = Instance::where('name', $name)->firstOrFail();
      return response()->json([
        'STORAGE_HOST' => $this->base_uri,
        'STORAGE_USERNAME' => $instance->username,
        'STORAGE_PASSWORD' => $instance->password,
      ]);
    }

    public function create(Request $request)
    {
      $this->validate($request, [
        'name' => 'required|unique:instances',
        'plan' => [
          'required',
          Rule::in($this->plans->pluck('name')->toArray())
        ],
        'team' => 'required'
      ]);
      $name = $request->input('name');
      $plan = $this->plans->firstWhere('name', $request->input('plan'));
      $user = $request->input('user', 'admin');
      $username = $user . '_' .  $name;
      $password = str_random(20);

      $client = $this->getClient();
      $response = $client->request('POST', '/ocs/v2.php/cloud/users', [
        'form_params' => [
          'userid' => $username,
          'password' => $password
        ]
      ]);
      $response = $client->request(
        'PUT',
        '/ocs/v2.php/cloud/users/' . $username,[
          'form_params' => [
            'key' => 'quota',
            'value' => $plan['quota']
          ]
        ]
      );
      Instance::create([
        'name' => $name,
        'username' => $username,
        'password' => $password,
        'quota' => $plan['quota'],
      ]);
      return response()->json([], 201);
    }

    private function getClient() {
      $client = new GuzzleClient([
        // Base URI is used with relative requests
        'base_uri' => $this->base_uri,
        // You can set any number of default request options.
        'timeout'  => 15.0,
        'auth' => [
          'admin',
          'kasrafakhari01',
        ],
        'headers' => [
            'OCS-APIRequest' => 'true',
            'Accept'     => 'application/json',
        ],
        'http_errors' => false
      ]);
      return $client;
    }
}
