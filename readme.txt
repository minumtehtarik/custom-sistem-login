
1. kita akan coba bikin controllernya
secara default controller pertama adalah controller welcome
yaitu ada di config - routes.php - 
$route['default_controller'] = 'welcome';
ganti defaultnya dengan auth
$route['default_controller'] = 'auth';

2. bikin controller baru
Auth.php
isinya nyontek sedikit dari controller welcome
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{ 
public function index()
    {
        echo 'auth/index';
    }
}

