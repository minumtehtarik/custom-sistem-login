<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct(); // -> ini untuk memanggil method construct yang ada di CI_Controller

        // form validation gk bisa di simpan di autoload, jadi form validation harus disimpan ke dalam method atau controller
        $this->load->library('form_validation');
    }
    public function index()
    {
        $this->goToDefaultPage();

        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');

        if ($this->form_validation->run() == false) {
            $data['title'] = 'TrayTech Login Page';
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/login');
            $this->load->view('templates/auth_footer');
        } else {
            // jika validasinya sukses
            // biasanya _ kita tandakan sebagai private
            $this->_login();
        }
    }


    private function _login()
    {
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        // ambil data user.. SELECT * FROM tabel user where emailnya = email-> cara bacanya
        $user = $this->db->get_where('user', ['email' => $email])->row_array();
        // kita ingin mendapatkan 1 baris <- row_array();
        // klo kita var_dump maka akan mengembalikan data usernya
        // var_dumpt($user);

        // jika usernya ada atau = true
        if ($user) {
            // jika usernya aktif
            // cara bacanya: jika usernya ada dan didalamnya ada field is_active == <i class="fas fa-signal-alt-1    "></i>
            if ($user['is_active'] == 1) {

                // kalo belum di aktivasi
                // cek passwordnya
                // cara bacanya: password_verify ini adalah fungsi yg ada di php
                // untuk menyamakan antara password yang diketika login form 
                // dengan password yang sudah di hash, nanti di cocokan dengan fungsi tersebut
                // parameter pertama password yang di ketikan dari kolom input
                // di cocokan dengan password yang ada di data $user
                if (password_verify($password, $user['password'])) {
                    // kalo benar, kita siapkan data di dalam session supaya bisa di pakai di halaman user nantinya
                    $data = [
                        'email' => $user['email'],
                        // role_id untuk menentukan menu nya nanti
                        'role_id' => $user['role_id']
                    ];
                    $this->session->set_userdata($data);
                    // lalu kita arahkan ke view yang kita mau
                    // kalo admin ke view admin ke kontroller admin
                    // kalo user ke view user ke kontroller user

                    // cek apakah login nya sebagai admin atau member
                    if ($user['role_id'] == 1) {
                        redirect('admin');
                    } else {
                        redirect('user');
                    }
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
                    Wrong password!</div>');
                    redirect('auth');
                }
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
                Email is not registered!</div>');
                redirect('auth');
            }
        }
    }



    public function registration()
    {
        $this->goToDefaultPage();
        // Rules Validation
        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        // CI sudah punya rules namanya is_unique[namatabel.namafieldnya]
        $this->form_validation->set_rules(
            'email',
            'Email',
            'required|trim|valid_email|is_unique[user.email]',
            [
                'is_unique' => 'This Email has already registered'
            ]
        );
        $this->form_validation->set_rules(
            'password1',
            'Password',
            'required|trim|min_length[3]|matches[password2]',
            [
                'matches' => 'password doesnt match!',
                'min_length' => 'Password too short'
            ]
        );
        $this->form_validation->set_rules('password2', 'Password', 'required|matches[password1]');


        if ($this->form_validation->run() == false) {
            $data['title'] = 'TrayTech User Registration';
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/registration');
            $this->load->view('templates/auth_footer');
        } else {
            $email = $this->input->post('email', true);
            $data =
                [
                    // id gausa diisi karena auto increment
                    // true untuk menghindari xss cross side scipting
                    'name' => htmlspecialchars($this->input->post('name', true)),
                    'email' => htmlspecialchars($email),
                    'image' => 'default.jpg',
                    'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
                    'role_id' => 2,
                    'is_active' => 0,
                    'date_created' => time()
                ];

            // siapkan token 
            // (base64_encode(random_bytes(32))fungsi php.net
            // random bytes akan memberikan bilangan random bentuknya bytes
            // base64_encode untuk terjemahkan agar mudah dibaca
            $token = base64_encode(random_bytes(32));
            $user_token = [
                'email' => $email,
                'token' => $token,
                'date_created' => time()
            ];


            $this->db->insert('user', $data); // insert ke database
            $this->db->insert('user_token', $user_token); // insert ke database


            $this->_sendEmail($token, 'verify');




            // bila sukses masuk tambahkan flashdata
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
            Congratulation! your account has been created. Please activate your account in email registered.</div>');
            redirect('auth');
        }
    }


    private function _sendEmail($token, $type)
    {

        $config = [
            'protocol' => 'smtp', // simple mail transfer protocol
            'smtp_host' => 'ssl://smtp.googlemail.com',
            'smtp_user' => 'treyebedey@gmail.com',
            'smtp_pass' => '6076Baday',
            'smtp_port' => 465, // port smtpnya google
            'mailtype' => 'html', //html karena kita mau nulis ada link nya
            'charset' => 'utf-8', // character set
            'newline' => "\r\n" // kalo ga pake ini ga mau ngirim nanti
        ];

        $this->load->library('email', $config); // library codeigniternya
        $this->email->initialize($config);
        // ekarang librarynya sudah bisa dipake karena sudah di load
        $this->email->from('treyebedey@gmail.com', 'Tray Tech'); // email ini dikirim dari siapa
        $this->email->to($this->input->post('email')); // mau dikirim kemana

        if ($type == 'verify') {

            $this->email->subject('Account Verification'); //subjeknya apa 
            $this->email->message('Click this link to verify your account : <a href="' . base_url() . 'auth/verify?email=' . $this->input->post('email') . '&token=' . urlencode($token) . '">Activate</a>');
        } // base64_encode ini mengandung +dan = yg tidak ramah dengan url
        // untuk itu sebelum linknya dikirim ke user tokennya kita bungkus dengan function yg namanya urlencode
        else if ($type == 'forgot') {
            $this->email->subject('Reset Password'); //subjeknya apa 
            $this->email->message('Click this link to reset your password : <a href="' . base_url() . 'auth/resetpassword?email=' . $this->input->post('email') . '&token=' . urlencode($token) . '">Reset Password</a>');
        }
        if ($this->email->send()) {
            return true;
        } else {
            echo $this->email->print_debugger();
            die;
        }
    }


    public function verify()
    {
        $email = $this->input->get('email');
        $token = $this->input->get('token');

        $user = $this->db->get_where('user', ['email' => $email])->row_array();

        if ($user) {
            $user_token = $this->db->get_where('user_token', ['token' => $token])->row_array();

            if ($user_token) {
                // waktu validasi
                if (time() - $user_token['date_created'] < (60 * 60 * 24)) {
                    $this->db->set('is_active', 1);
                    $this->db->where('email', $email);
                    $this->db->update('user');

                    $this->db->delete('user_token', ['email' => $email]);

                    $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
            ' . $email . ' has been activated!
            Please login.</div>');
                    redirect('auth');
                } else {
                    // waktu validasi expired
                    $this->db->delete('user', ['email' => $email]);
                    $this->db->delete('user_token', ['email' => $email]);

                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
            Account activation failed! Token Expired.</div>');
                    redirect('auth');
                }
            } else {
                // kalo ada yg edit token di url
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
            Account activation failed! Wrong token.</div>');
                redirect('auth');
            }
        } else {
            // kalo ada yg edit email di url
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
        Account activation failed! Wrong email.</div>');
            redirect('auth');
        }
    }




    public function logout()
    {
        $this->session->unset_userdata('email');
        $this->session->unset_userdata('role_id');

        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
        You have been logged out</div>');
        redirect('auth');
    }







    public function blocked()
    {
        // echo 'access blocked';
        $this->load->view('auth/blocked');
    }





    public function goToDefaultPage()
    {
        if ($this->session->userdata('role_id') == 1) {
            redirect('admin');
        } else if ($this->session->userdata('role_id') == 2) {
            redirect('user');
        }
    }


    public function forgotPassword()
    {

        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');

        if ($this->form_validation->run() == false) {
            $data['title'] = 'Forgot Password';
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/forgot-password');
            $this->load->view('templates/auth_footer');
        } else {
            $email = $this->input->post('email');
            // get_where ditabel user where email = $email kita butuh 1 baris jadi row_array
            // DAN is_active untuk cek apabila user baru daftar namun belum di aktivasi maka tidak bisa forgot password
            $user = $this->db->get_where('user', ['email' => $email, 'is_active' => 1])->row_array();

            if ($user) {
                $token = base64_encode(random_bytes(32));
                $user_token = [
                    'email' => $email,
                    'token' => $token,
                    'date_created' => time()
                ];
                // cara baca kalo pake query manual
                // this db insert in to tabel user _token where values $user_token
                $this->db->insert('user_token', $user_token);
                $this->_sendEmail($token, 'forgot');

                $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
        Please check your email to reset your password!</div>');
                redirect('auth/forgotpassword');
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
        Email is not registered or activated!</div>');
                redirect('auth/forgotpassword');
            }
        }
    }


    public function resetPassword()
    {
        $email = $this->input->get('email');
        $token = $this->input->get('token');

        $user = $this->db->get_where('user', ['email' => $email])->row_array();

        if ($user) {
            $user_token = $this->db->get_where('user_token', ['token' => $token])->row_array();

            if ($user_token) {
                $this->session->set_userdata('reset_email', $email);
                $this->changePassword();
            } else {
                // menghindari url token di edit
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
                Reset Password failed! Wrong Token</div>');
                redirect('auth/forgotpassword');
            }
        } else {
            // menghindari url di edit
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
            Reset Password failed! Wrong email</div>');
            redirect('auth/forgotpassword');
        }
    }

    public function changePassword()
    {
        // untuk menghindari user mengubah password tanpa lewat email
        // harus melewati session
        if (!$this->session->userdata('reset_email')) {
            redirect('auth');
        }


        $this->form_validation->set_rules('password1', 'Password', 'trim|required|min_length[3]|matches[password2]');
        $this->form_validation->set_rules('password2', 'Repeat Password', 'trim|required|min_length[3]|matches[password1]');
        if ($this->form_validation->run() == false) {

            $data['title'] = 'Change Password';
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/change-password');
            $this->load->view('templates/auth_footer');
        } else {
            $password = password_hash(
                $this->input->post('password1'),
                PASSWORD_DEFAULT
            );
            $email = $this->session->userdata('reset_email');

            $this->db->set('password', $password);
            $this->db->where('email', $email);
            $this->db->update('user');

            $this->session->unset_userdata('reset_email');

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
            Password has been changed! Please login</div>');
            redirect('auth');
        }
    }
}
